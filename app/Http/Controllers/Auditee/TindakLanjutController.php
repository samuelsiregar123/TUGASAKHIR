<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditRequest;
use App\Models\PesanTindakLanjut;
use App\Models\TemuanAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class TindakLanjutController extends Controller
{
    public function index()
    {
        $auditeeId = auth()->id();

        $plans = AuditPlan::with(['auditRequest', 'temuanAudit' => fn ($q) => $q->where('is_aktif', true)])
            ->whereHas('auditRequest', fn ($q) => $q->where('auditee_id', $auditeeId))
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'instansi'     => $p->auditRequest->nama_instansi ?? '-',
                'aplikasi'     => $p->auditRequest->url_target ?? '-',
                'tahun'        => optional($p->waktu_mulai)->format('Y'),
                'total_temuan' => $p->temuanAudit->count(),
                'proses'       => $p->temuanAudit->where('status_tindak_lanjut', 'proses')->count(),
                'selesai'      => $p->temuanAudit->where('status_tindak_lanjut', 'selesai')->count(),
            ]);

        return Inertia::render('Auditee/TindakLanjutIndex', [
            'plans' => $plans,
        ]);
    }

    public function show(int $planId)
    {
        // Pastikan auditee hanya bisa akses audit instansinya sendiri
        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);
        abort_if($plan->auditRequest->auditee_id !== auth()->id(), 403);

        $temuan = TemuanAudit::with(['butir', 'auditor', 'pesanTindakLanjut.user'])
            ->where('audit_plan_id', $planId)
            ->where('is_aktif', true)
            ->latest()
            ->get()
            ->map(fn ($t) => $this->formatTemuan($t));

        return Inertia::render('Shared/TindakLanjut', [
            'plan' => [
                'id'      => $plan->id,
                'instansi'=> $plan->auditRequest->nama_instansi ?? '-',
            ],
            'temuan'    => $temuan,
            'isKetuaTim'=> false,
        ]);
    }

    public function kirimPesan(Request $request, TemuanAudit $temuan)
    {
        $plan = AuditPlan::with('auditRequest')->findOrFail($temuan->audit_plan_id);
        abort_if($plan->auditRequest->auditee_id !== auth()->id(), 403);

        $request->validate([
            'pesan'      => 'nullable|string|max:2000',
            'lampiran'   => 'nullable|array|max:5',
            'lampiran.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        // Pesan wajib terisi — jika hanya kirim lampiran, gunakan teks default
        $pesanTeks = trim($request->pesan ?? '') ?: 'Bukti terlampir.';

        $lampiranData = [];
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $path = $file->store("tindak_lanjut/{$temuan->audit_plan_id}", 'public');
                $lampiranData[] = [
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ];
            }
        }

        PesanTindakLanjut::create([
            'temuan_id' => $temuan->id,
            'user_id'   => auth()->id(),
            'pesan'     => $pesanTeks,
            'lampiran'  => $lampiranData ? json_encode($lampiranData) : null,
        ]);

        return response()->json(['ok' => true, 'pesan' => $this->formatPesan(
            PesanTindakLanjut::with('user')->where('temuan_id', $temuan->id)->latest()->first()
        )]);
    }

    public function setDeadline(Request $request, TemuanAudit $temuan)
    {
        $plan = AuditPlan::with('auditRequest')->findOrFail($temuan->audit_plan_id);
        abort_if($plan->auditRequest->auditee_id !== auth()->id(), 403);

        $request->validate([
            'deadline' => 'required|date|after_or_equal:today',
        ]);

        $temuan->update(['deadline' => $request->deadline]);

        return response()->json(['ok' => true, 'deadline' => $request->deadline]);
    }

    public function pesanTerbaru(TemuanAudit $temuan)
    {
        $plan = AuditPlan::with('auditRequest')->findOrFail($temuan->audit_plan_id);
        abort_if($plan->auditRequest->auditee_id !== auth()->id(), 403);

        $pesan = PesanTindakLanjut::with('user')
            ->where('temuan_id', $temuan->id)
            ->latest()
            ->get()
            ->map(fn ($p) => $this->formatPesan($p));

        return response()->json(['pesan' => $pesan, 'status' => $temuan->status_tindak_lanjut]);
    }

    private function formatTemuan(TemuanAudit $t): array
    {
        return [
            'id'                   => $t->id,
            'judul'                => $t->judul ?? '-',
            'deskripsi'            => $t->deskripsi,
            'risiko'               => $t->risiko,
            'rekomendasi'          => $t->rekomendasi,
            'status_tindak_lanjut' => $t->status_tindak_lanjut,
            'butir_kode'           => $t->butir?->kode ?? '-',
            'butir_judul'          => $t->butir?->judul_butir,
            'butir_sumber'         => $t->butir?->sumber_acuan,
            'deadline'             => $t->deadline?->format('Y-m-d'),
            'pesan'                => $t->pesanTindakLanjut->map(fn ($p) => $this->formatPesan($p))->values(),
        ];
    }

    private function formatPesan(PesanTindakLanjut $p): array
    {
        $lampiran = [];

        if ($p->lampiran) {
            foreach (json_decode($p->lampiran, true) ?? [] as $item) {
                $lampiran[] = [
                    'url'  => Storage::disk('public')->url($item['path']),
                    'name' => $item['name'],
                    'size' => $item['size'] ?? null,
                ];
            }
        }

        // Backward-compat: data lama yang pakai kolom path_bukti tunggal
        if ($p->path_bukti && empty($lampiran)) {
            $lampiran[] = [
                'url'  => Storage::disk('public')->url($p->path_bukti),
                'name' => basename($p->path_bukti),
                'size' => null,
            ];
        }

        return [
            'id'         => $p->id,
            'pesan'      => $p->pesan,
            'user_name'  => $p->user?->name ?? '-',
            'user_role'  => $p->user?->role ?? '-',
            'lampiran'   => $lampiran,
            'created_at' => $p->created_at?->format('d M Y H:i'),
        ];
    }
}
