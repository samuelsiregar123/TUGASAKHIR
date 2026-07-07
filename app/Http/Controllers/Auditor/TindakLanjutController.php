<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditPlanAuditor;
use App\Models\PesanTindakLanjut;
use App\Models\TemuanAudit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TindakLanjutController extends Controller
{
    // Level 1 — daftar plan yang auditor ini ditugaskan
    public function index()
    {
        $plans = AuditPlanAuditor::with(['auditPlan.auditRequest', 'auditPlan.temuanAudit' => fn ($q) => $q->where('is_aktif', true)])
            ->where('user_id', auth()->id())
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->auditPlan->id,
                'instansi'     => $a->auditPlan->auditRequest->nama_instansi ?? '-',
                'aplikasi'     => $a->auditPlan->auditRequest->url_target ?? '-',
                'tahun'        => optional($a->auditPlan->waktu_mulai)->format('Y'),
                'total_temuan' => $a->auditPlan->temuanAudit->count(),
                'proses'       => $a->auditPlan->temuanAudit->where('status_tindak_lanjut', 'proses')->count(),
                'selesai'      => $a->auditPlan->temuanAudit->where('status_tindak_lanjut', 'selesai')->count(),
            ])
            ->unique('id')
            ->values();

        return Inertia::render('Auditor/TindakLanjutIndex', ['plans' => $plans]);
    }

    // Level 2 — thread per audit plan
    public function show(int $planId)
    {
        // Pastikan auditor ditugaskan pada plan ini
        AuditPlanAuditor::where('audit_plan_id', $planId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);

        $temuan = TemuanAudit::with(['butir', 'auditor', 'pesanTindakLanjut.user'])
            ->where('audit_plan_id', $planId)
            ->where('is_aktif', true)
            ->latest()
            ->get()
            ->map(fn ($t) => $this->formatTemuan($t));

        return Inertia::render('Shared/TindakLanjut', [
            'plan' => [
                'id'       => $plan->id,
                'instansi' => $plan->auditRequest->nama_instansi ?? '-',
            ],
            'temuan'     => $temuan,
            'isKetuaTim' => false,
            'baseUrlProp'=> '/auditor',
        ]);
    }

    // Kirim pesan balasan (teks saja, tanpa upload bukti — itu hak auditee)
    public function kirimPesan(Request $request, TemuanAudit $temuan)
    {
        AuditPlanAuditor::where('audit_plan_id', $temuan->audit_plan_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate(['pesan' => 'required|string|max:2000']);

        PesanTindakLanjut::create([
            'temuan_id' => $temuan->id,
            'user_id'   => auth()->id(),
            'pesan'     => $request->pesan,
        ]);

        return response()->json(['ok' => true, 'pesan' => $this->formatPesan(
            PesanTindakLanjut::with('user')->where('temuan_id', $temuan->id)->latest()->first()
        )]);
    }

    // Polling pesan terbaru
    public function pesanTerbaru(TemuanAudit $temuan)
    {
        AuditPlanAuditor::where('audit_plan_id', $temuan->audit_plan_id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $pesan = PesanTindakLanjut::with('user')
            ->where('temuan_id', $temuan->id)
            ->oldest()
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
            'pesan'                => $t->pesanTindakLanjut
                ->sortBy('created_at')
                ->map(fn ($p) => $this->formatPesan($p))
                ->values(),
        ];
    }

    private function formatPesan(PesanTindakLanjut $p): array
    {
        $lampiran = [];

        if ($p->lampiran) {
            foreach (json_decode($p->lampiran, true) ?? [] as $item) {
                $lampiran[] = [
                    'url'  => \Illuminate\Support\Facades\Storage::disk('public')->url($item['path']),
                    'name' => $item['name'],
                    'size' => $item['size'] ?? null,
                ];
            }
        }

        if ($p->path_bukti && empty($lampiran)) {
            $lampiran[] = [
                'url'  => \Illuminate\Support\Facades\Storage::disk('public')->url($p->path_bukti),
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
