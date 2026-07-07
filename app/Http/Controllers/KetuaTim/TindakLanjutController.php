<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\PesanTindakLanjut;
use App\Models\TemuanAudit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TindakLanjutController extends Controller
{
    public function index()
    {
        $plans = AuditPlan::with(['auditRequest', 'temuanAudit' => fn ($q) => $q->where('is_aktif', true)])
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

        return Inertia::render('KetuaTim/TindakLanjutIndex', [
            'plans' => $plans,
        ]);
    }

    public function show(int $planId)
    {
        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);

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
            'isKetuaTim'=> true,
        ]);
    }

    public function tandaiSelesai(Request $request, TemuanAudit $temuan)
    {
        $temuan->update(['status_tindak_lanjut' => 'selesai']);

        activity('tindak_lanjut')->causedBy(auth()->user())->performedOn($temuan)
            ->log("Tandai selesai temuan: {$temuan->judul}");

        return response()->json(['ok' => true]);
    }

    public function kirimPesan(Request $request, TemuanAudit $temuan)
    {
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

    public function pesanTerbaru(TemuanAudit $temuan)
    {
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
