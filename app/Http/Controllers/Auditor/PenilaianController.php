<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\PenilaianButir;
use App\Models\ScanResult;
use App\Traits\SinkronTemuanOtomatis;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PenilaianController extends Controller
{
    use SinkronTemuanOtomatis;
    /** Mapping nilai kolom bagian audit_plan_auditors → array bagian butir */
    private const BAGIAN_MAP = [
        'semua' => ['tk', 'mk', 'fk'],
        'tk_mk' => ['tk', 'mk'],
        'fk'    => ['fk'],
    ];

    public function index(Request $request)
    {
        $planId = (int) $request->query('plan');

        if (! $planId) {
            return redirect()->route('auditor.dashboard');
        }

        $isKetuaTim = auth()->user()->role === 'ketua_tim';

        // Ketua tim selalu melihat semua bagian; auditor dibatasi penugasannya
        if ($isKetuaTim) {
            $assignedTabs = ['tk', 'mk', 'fk'];
        } else {
            $assignment = AuditPlanAuditor::where('audit_plan_id', $planId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $assignedTabs = self::BAGIAN_MAP[$assignment->bagian] ?? ['tk', 'mk', 'fk'];
        }

        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);

        // Lead penilaian per butir_id (untuk membaca jawaban + bukti auditee)
        $allByButir = PenilaianButir::with('buktiButir')
            ->where('audit_plan_id', $planId)
            ->get()
            ->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first());

        if ($isKetuaTim) {
            // Ketua tim melihat lead record per butir (satu record representatif per butir)
            $myPenilaian = PenilaianButir::with('butir')
                ->where('audit_plan_id', $planId)
                ->whereIn('id', $allByButir->pluck('id'))
                ->whereHas('butir', fn ($q) => $q->whereIn('bagian', $assignedTabs))
                ->get();
        } else {
            // Auditor hanya melihat penilaian milik dirinya sesuai bagian yang ditugaskan
            $myPenilaian = PenilaianButir::with('butir')
                ->where('audit_plan_id', $planId)
                ->where('auditor_id', auth()->id())
                ->whereHas('butir', fn ($q) => $q->whereIn('bagian', $assignedTabs))
                ->get();
        }

        $butirByBagian = $myPenilaian
            ->groupBy(fn ($p) => $p->butir->bagian)
            ->map(fn ($group) => $group->sortBy('butir.nomor')->values()->map(function ($p) use ($allByButir) {
                $lead = $allByButir[$p->butir_id] ?? $p;

                return [
                    'penilaian_id'   => $p->id,
                    'butir_id'       => $p->butir_id,
                    'bagian'         => $p->butir->bagian,
                    'kode'           => $p->butir->kode,
                    'nomor'          => $p->butir->nomor,
                    'domain'         => $p->butir->domain,
                    'judul_butir'    => $p->butir->judul_butir,
                    'sumber_acuan'   => $p->butir->sumber_acuan,
                    'acuan_edk'      => $p->butir->acuan_edk,
                    'acuan_eik'      => $p->butir->acuan_eik,
                    'acuan_efk'      => $p->butir->acuan_efk,
                    'jawaban_auditee'=> $lead->jawaban_auditee,
                    'bukti'          => $lead->buktiButir->map(fn ($b) => [
                        'id'          => $b->id,
                        'jenis_acuan' => $b->jenis_acuan,
                        'nama_file'   => $b->nama_file,
                        'path_file'   => $b->path_file,
                    ])->values(),
                    'edk'        => $p->edk,
                    'catatan_edk'=> $p->catatan_edk,
                    'eik'        => $p->eik,
                    'catatan_eik'=> $p->catatan_eik,
                    'efk'        => $p->efk,
                    'catatan_efk'=> $p->catatan_efk,
                ];
            })->values());

        // Kumpulkan findings dari scan selesai, diindeks per butir_id (hanya FK)
        $scanFindings = $this->getScanFindings($planId);

        return Inertia::render('Auditor/Penilaian', [
            'plan' => [
                'id'            => $plan->id,
                'instansi'      => $plan->auditRequest->nama_instansi ?? '-',
                'url_target'    => $plan->auditRequest->url_target ?? '-',
                'waktu_mulai'   => optional($plan->waktu_mulai)->format('d M Y'),
                'waktu_selesai' => optional($plan->waktu_selesai)->format('d M Y'),
            ],
            'butirByBagian'  => $butirByBagian,
            'assignedBagian' => $assignedTabs,
            'scanFindings'   => $scanFindings,
        ]);
    }

    public function update(Request $request, PenilaianButir $penilaian)
    {
        $isKetuaTim = auth()->user()->role === 'ketua_tim';
        if (! $isKetuaTim && $penilaian->auditor_id !== auth()->id()) {
            abort(403, 'Bukan penilaian Anda.');
        }

        $validated = $request->validate([
            'edk'        => 'required|in:memadai,perlu_peningkatan,tidak_memadai',
            'catatan_edk'=> 'nullable|string|max:3000',
            'eik'        => 'nullable|in:sesuai,tidak_sesuai,skip',
            'catatan_eik'=> 'nullable|string|max:3000',
            'efk'        => 'nullable|in:efektif,perlu_peningkatan,belum_efektif',
            'catatan_efk'=> 'nullable|string|max:3000',
        ]);

        if ($validated['edk'] === 'tidak_memadai') {
            $validated['eik']        = 'skip';
            $validated['catatan_eik']= null;
        }

        $penilaian->update($validated);

        $this->sinkronTemuanOtomatis($penilaian->fresh());

        activity('penilaian')->causedBy(auth()->user())->performedOn($penilaian)
            ->withProperties(['butir_id' => $penilaian->butir_id, 'edk' => $validated['edk'], 'eik' => $validated['eik'] ?? null, 'efk' => $validated['efk'] ?? null])
            ->log("Update penilaian butir #{$penilaian->butir_id}");

        return response()->json(['ok' => true]);
    }

    /**
     * Kumpulkan findings dari semua scan selesai, dikelompokkan per butir_id.
     * Hanya butir FK yang memiliki ada_scan=true yang ditampilkan di penilaian.
     * Return: ['butir_id' => [['tool', 'severity', 'title', 'description', 'evidence', 'scan_id', 'finding_index'], ...]]
     */
    private function getScanFindings(int $planId): array
    {
        $scans = ScanResult::where('audit_plan_id', $planId)
            ->where('status', 'selesai')
            ->whereNotNull('hasil_json')
            ->get();

        $result = [];
        foreach ($scans as $scan) {
            $findings = $scan->hasil_json['findings'] ?? [];
            foreach ($findings as $idx => $finding) {
                $butirId = $finding['butir_id'] ?? null;
                if (! $butirId) continue;
                $result[$butirId][] = [
                    'tool'          => $scan->tool,
                    'severity'      => $finding['severity'] ?? 'Info',
                    'title'         => $finding['title'] ?? '',
                    'description'   => substr($finding['description'] ?? '', 0, 300),
                    'evidence'      => $finding['evidence'] ?? '',
                    'scan_id'       => $scan->id,
                    'finding_index' => $idx,
                ];
            }
        }

        return $result;
    }

    public function validasiKonklusi(int $planId)
    {
        $isKetuaTim = auth()->user()->role === 'ketua_tim';

        if ($isKetuaTim) {
            // Ketua tim memvalidasi semua lead record (satu per butir)
            $allByButir = PenilaianButir::where('audit_plan_id', $planId)
                ->get()
                ->groupBy('butir_id')
                ->map(fn ($g) => $g->sortBy('id')->first());

            $myPenilaian = PenilaianButir::where('audit_plan_id', $planId)
                ->whereIn('id', $allByButir->pluck('id'))
                ->get();
        } else {
            $assignment = AuditPlanAuditor::where('audit_plan_id', $planId)
                ->where('user_id', auth()->id())
                ->firstOrFail();
            $assignedTabs = self::BAGIAN_MAP[$assignment->bagian] ?? ['tk', 'mk', 'fk'];

            $myPenilaian = PenilaianButir::where('audit_plan_id', $planId)
                ->where('auditor_id', auth()->id())
                ->whereHas('butir', fn ($q) => $q->whereIn('bagian', $assignedTabs))
                ->get();
        }

        $belum = $myPenilaian->filter(function ($p) {
            $edkSet = ! empty($p->edk);
            $efkSet = ! empty($p->efk);
            $eikOk  = $p->edk === 'tidak_memadai' || ! empty($p->eik);

            return ! ($edkSet && $efkSet && $eikOk);
        })->count();

        if ($belum > 0) {
            return response()->json([
                'ok'      => false,
                'message' => "Penilaian belum lengkap. Masih ada {$belum} butir yang belum selesai dinilai.",
            ], 422);
        }

        return response()->json([
            'ok'      => true,
            'message' => 'Semua butir telah dinilai. Konklusi siap dihitung.',
        ]);
    }
}
