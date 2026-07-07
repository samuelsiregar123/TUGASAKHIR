<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Jobs\RunCurlScan;
use App\Jobs\RunNiktoScan;
use App\Jobs\RunNmapScan;
use App\Jobs\RunTestsslScan;
use App\Jobs\RunZapScan;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\BuktiButir;
use App\Models\PenilaianButir;
use App\Models\ScanResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ScanController extends Controller
{
    // Level 1 — daftar audit plan yang ditugaskan ke auditor ini
    public function index()
    {
        $userId = auth()->id();

        $plans = AuditPlan::with(['auditRequest', 'scanResults', 'auditors'])
            ->whereHas('auditors', fn ($q) => $q->where('user_id', $userId))
            ->latest()
            ->get()
            ->map(function ($plan) {
                $scans       = $plan->scanResults;
                $totalScans  = $scans->count();
                $doneScans   = $scans->whereIn('status', ['selesai', 'gagal'])->count();
                $hasRunning  = $scans->where('status', 'berjalan')->count() > 0;

                if ($totalScans === 0) {
                    $scanStatus = 'belum';
                } elseif ($hasRunning || $scans->where('status', 'menunggu')->count() > 0) {
                    $scanStatus = 'berjalan';
                } elseif ($doneScans === $totalScans) {
                    $scanStatus = 'selesai';
                } else {
                    $scanStatus = 'sebagian';
                }

                return [
                    'id'             => $plan->id,
                    'instansi'       => $plan->auditRequest->nama_instansi ?? '-',
                    'url_target'     => $plan->auditRequest->url_target ?? '-',
                    'waktu_mulai'    => optional($plan->waktu_mulai)->format('d M Y'),
                    'waktu_selesai'  => optional($plan->waktu_selesai)->format('d M Y'),
                    'scan_status'    => $scanStatus,
                    'status_pengisian' => $plan->status_pengisian,
                ];
            });

        return Inertia::render('Auditor/PemindaianIndex', ['plans' => $plans]);
    }

    // Level 2 — kontrol scan + hasil untuk satu audit plan
    public function show(int $planId)
    {
        $this->authorizePlan($planId);

        $plan = AuditPlan::with(['auditRequest', 'auditors.user'])->findOrFail($planId);

        $isKetua = AuditPlanAuditor::where('audit_plan_id', $planId)
            ->where('user_id', auth()->id())
            ->where('peran', 'ketua')
            ->exists() || auth()->user()->role === 'ketua_tim';

        $scans = ScanResult::where('audit_plan_id', $planId)
            ->orderBy('tool')
            ->get()
            ->map(fn ($s) => $this->formatScan($s));

        return Inertia::render('Auditor/Pemindaian', [
            'plan' => [
                'id'         => $plan->id,
                'instansi'   => $plan->auditRequest->nama_instansi ?? '-',
                'url_target' => $plan->auditRequest->url_target ?? '-',
                'is_ketua'   => $isKetua,
            ],
            'scans' => $scans,
        ]);
    }

    // Dispatch 1 atau 5 job
    public function start(Request $request, int $planId)
    {
        $this->authorizePlan($planId);

        $validated = $request->validate([
            'tool'       => 'required|in:curl,testssl,nmap,nikto,zap,semua',
            'target_url' => 'required|url',
        ]);

        $tools = $validated['tool'] === 'semua'
            ? ['curl', 'testssl', 'nmap', 'nikto', 'zap']
            : [$validated['tool']];

        $jobMap = [
            'curl'    => RunCurlScan::class,
            'testssl' => RunTestsslScan::class,
            'nmap'    => RunNmapScan::class,
            'nikto'   => RunNiktoScan::class,
            'zap'     => RunZapScan::class,
        ];

        foreach ($tools as $tool) {
            // Jangan dispatch jika tool ini sedang berjalan
            $existing = ScanResult::where('audit_plan_id', $planId)
                ->where('tool', $tool)
                ->whereIn('status', ['menunggu', 'berjalan'])
                ->exists();

            if ($existing) {
                continue;
            }

            // Reset atau buat baru
            $scan = ScanResult::updateOrCreate(
                ['audit_plan_id' => $planId, 'tool' => $tool],
                [
                    'target_url'    => $validated['target_url'],
                    'status'        => 'menunggu',
                    'hasil_json'    => null,
                    'started_at'    => null,
                    'finished_at'   => null,
                    'error_message' => null,
                ]
            );

            dispatch(new $jobMap[$tool]($scan));
        }

        return response()->json(['ok' => true]);
    }

    // Polling status semua scan untuk satu plan
    public function status(int $planId)
    {
        $this->authorizePlan($planId);

        $scans = ScanResult::where('audit_plan_id', $planId)
            ->orderBy('tool')
            ->get()
            ->map(fn ($s) => $this->formatScan($s));

        return response()->json(['scans' => $scans]);
    }

    // Detail findings satu scan result
    public function result(ScanResult $scan)
    {
        $this->authorizePlan($scan->audit_plan_id);

        $fkButir = \App\Models\ButirPenilaian::where('bagian', 'fk')
            ->orderBy('nomor')
            ->get(['id', 'kode', 'judul_butir']);

        return Inertia::render('Auditor/ScanResult', [
            'scan'    => $this->formatScan($scan, includeFindings: true),
            'fkButir' => $fkButir,
        ]);
    }

    // Tag satu finding sebagai bukti EFK
    public function tagBukti(Request $request, ScanResult $scan)
    {
        $this->authorizePlan($scan->audit_plan_id);

        $validated = $request->validate([
            'butir_id'      => 'required|exists:butir_penilaian,id',
            'finding_index' => 'required|integer|min:0',
        ]);

        $hasil    = $scan->hasil_json ?? [];
        $findings = $hasil['findings'] ?? [];
        $finding  = $findings[$validated['finding_index']] ?? null;

        if (! $finding) {
            return response()->json(['error' => 'Finding tidak ditemukan.'], 422);
        }

        // Cari lead penilaian_butir untuk butir ini di plan ini
        $penilaian = PenilaianButir::where('audit_plan_id', $scan->audit_plan_id)
            ->where('butir_id', $validated['butir_id'])
            ->orderBy('id')
            ->firstOrFail();

        $plan      = AuditPlan::with('auditRequest')->findOrFail($scan->audit_plan_id);
        $auditeeId = $plan->auditRequest->auditee_id;

        // Simpan evidence sebagai file .txt
        $safeName = preg_replace('/[^a-z0-9]+/', '-', strtolower($finding['title'] ?? 'finding'));
        $safeName = substr($safeName, 0, 60);
        $filePath = "bukti-scan/{$scan->audit_plan_id}/{$scan->id}-{$safeName}.txt";
        $content  = "[Scan Tool: {$scan->tool}]\n"
            . "[Target: {$scan->target_url}]\n"
            . "[Severity: {$finding['severity']}]\n\n"
            . "TEMUAN: {$finding['title']}\n\n"
            . "DESKRIPSI:\n{$finding['description']}\n\n"
            . "EVIDENCE:\n{$finding['evidence']}\n";

        Storage::disk('public')->put($filePath, $content);

        $butir = \App\Models\ButirPenilaian::find($validated['butir_id']);

        BuktiButir::create([
            'penilaian_id' => $penilaian->id,
            'jenis_acuan'  => 'efk',
            'auditee_id'   => $auditeeId,
            'path_file'    => $filePath,
            'nama_file'    => "Scan [{$scan->tool}]: {$finding['title']}",
        ]);

        return response()->json(['ok' => true, 'butir_kode' => $butir->kode ?? '']);
    }

    // Batalkan 1 scan yang sedang running/pending
    public function cancel(ScanResult $scan)
    {
        $this->authorizePlan($scan->audit_plan_id);

        if (! in_array($scan->status, ['menunggu', 'berjalan'])) {
            return response()->json(['error' => 'Scan tidak sedang berjalan.'], 422);
        }

        $scan->update([
            'status'        => 'gagal',
            'error_message' => 'Dibatalkan oleh auditor',
            'finished_at'   => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    // Batalkan semua scan running/pending untuk satu audit plan
    public function cancelAll(int $planId)
    {
        $this->authorizePlan($planId);

        ScanResult::where('audit_plan_id', $planId)
            ->whereIn('status', ['menunggu', 'berjalan'])
            ->update([
                'status'        => 'gagal',
                'error_message' => 'Dibatalkan oleh auditor',
                'finished_at'   => now(),
            ]);

        return response()->json(['ok' => true]);
    }

    // Jalankan ulang scan yang gagal
    public function rerun(ScanResult $scan)
    {
        $this->authorizePlan($scan->audit_plan_id);

        if (! in_array($scan->status, ['gagal', 'selesai'])) {
            return response()->json(['error' => 'Hanya scan yang gagal atau selesai yang bisa dijalankan ulang.'], 422);
        }

        $jobMap = [
            'curl'    => RunCurlScan::class,
            'testssl' => RunTestsslScan::class,
            'nmap'    => RunNmapScan::class,
            'nikto'   => RunNiktoScan::class,
            'zap'     => RunZapScan::class,
        ];

        $scan->update([
            'status'        => 'menunggu',
            'hasil_json'    => null,
            'started_at'    => null,
            'finished_at'   => null,
            'error_message' => null,
        ]);

        dispatch(new $jobMap[$scan->tool]($scan));

        return response()->json(['ok' => true]);
    }

    private function formatScan(ScanResult $scan, bool $includeFindings = false): array
    {
        $data = [
            'id'            => $scan->id,
            'tool'          => $scan->tool,
            'target_url'    => $scan->target_url,
            'status'        => $scan->status,
            'error_message' => $scan->error_message,
            'started_at'    => optional($scan->started_at)->format('d M Y H:i:s'),
            'finished_at'   => optional($scan->finished_at)->format('d M Y H:i:s'),
        ];

        if ($includeFindings && $scan->hasil_json) {
            $hasil        = $scan->hasil_json;
            $data['findings']   = $hasil['findings'] ?? [];
            $data['raw_output'] = $hasil['raw_output'] ?? '';
            $data['scanned_at'] = $hasil['scanned_at'] ?? null;
        } elseif ($scan->hasil_json) {
            // Summary only for list/status polling
            $findings = $scan->hasil_json['findings'] ?? [];
            $data['finding_count'] = count($findings);
            $data['severity_summary'] = array_count_values(array_column($findings, 'severity'));
        }

        return $data;
    }

    private function authorizePlan(int $planId): void
    {
        $userId = auth()->id();
        $role   = auth()->user()->role;

        if ($role === 'ketua_tim') {
            return; // Ketua tim bisa akses semua
        }

        $assigned = AuditPlanAuditor::where('audit_plan_id', $planId)
            ->where('user_id', $userId)
            ->exists();

        if (! $assigned) {
            abort(403, 'Anda tidak ditugaskan pada audit plan ini.');
        }
    }
}
