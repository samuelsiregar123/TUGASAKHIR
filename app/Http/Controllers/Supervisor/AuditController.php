<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\LhakApproval;
use App\Models\PenilaianButir;
use App\Models\TemuanAudit;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index()
    {
        $plans = AuditPlan::with(['auditRequest', 'auditResult', 'auditors.user'])
            ->latest()
            ->get()
            ->map(function ($p) {
                $total   = PenilaianButir::where('audit_plan_id', $p->id)->count();
                $selesai = PenilaianButir::where('audit_plan_id', $p->id)->whereNotNull('edk')->count();

                $auditorNames = $p->auditors
                    ->where('peran', '!=', 'ketua_tim')
                    ->map(fn ($a) => $a->user?->name)
                    ->filter()
                    ->values();

                return [
                    'id'                   => $p->id,
                    'instansi'             => $p->auditRequest->nama_instansi ?? '-',
                    'aplikasi'             => $p->auditRequest->url_target ?? '-',
                    'tahun'                => optional($p->waktu_mulai)->format('Y'),
                    'ketua_tim'            => optional($p->auditors->firstWhere('peran', 'ketua_tim'))->user?->name ?? '-',
                    'auditors'             => $auditorNames,
                    'total_butir'          => $total,
                    'selesai_butir'        => $selesai,
                    'status_pengisian'     => $p->status_pengisian ?? 'pengisian',
                    'konklusi_keseluruhan' => $p->auditResult?->konklusi_keseluruhan,
                ];
            });

        return Inertia::render('Supervisor/AuditIndex', [
            'plans' => $plans,
        ]);
    }

    public function show(int $planId)
    {
        $plan = AuditPlan::with([
            'auditRequest.auditee',
            'auditors.user',
            'auditResult',
        ])->findOrFail($planId);

        // Progress per bagian (join butir_penilaian untuk kolom bagian)
        $progressRaw = PenilaianButir::where('audit_plan_id', $planId)
            ->join('butir_penilaian', 'penilaian_butir.butir_id', '=', 'butir_penilaian.id')
            ->select(
                'butir_penilaian.bagian',
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN penilaian_butir.edk IS NOT NULL THEN 1 ELSE 0 END) as selesai')
            )
            ->groupBy('butir_penilaian.bagian')
            ->get()
            ->keyBy('bagian');

        $mkeyOf  = fn ($bag) => [
            'total'   => (int) ($progressRaw[$bag]?->total ?? 0),
            'selesai' => (int) ($progressRaw[$bag]?->selesai ?? 0),
        ];

        $totalTemuan = TemuanAudit::where('audit_plan_id', $planId)->where('is_aktif', true)->count();

        $tlSelesai = TemuanAudit::where('audit_plan_id', $planId)
            ->where('is_aktif', true)
            ->where('status_tindak_lanjut', 'selesai')
            ->count();

        $approval = LhakApproval::where('audit_plan_id', $planId)->latest()->first();

        $lhakStatus = 'belum_generate';
        if ($plan->auditResult?->path_lhak) {
            $lhakStatus = 'sudah_generate';
        }
        if ($approval) {
            $lhakStatus = $approval->status === 'pending' ? 'diajukan' : $approval->status;
        }

        $auditors = $plan->auditors->map(fn ($a) => [
            'name'  => $a->user?->name ?? '-',
            'peran' => $a->peran,
        ]);

        return Inertia::render('Supervisor/AuditShow', [
            'plan' => [
                'id'                   => $plan->id,
                'instansi'             => $plan->auditRequest->nama_instansi ?? '-',
                'aplikasi'             => $plan->auditRequest->url_target ?? '-',
                'auditee'              => $plan->auditRequest->auditee->name ?? '-',
                'waktu_mulai'          => optional($plan->waktu_mulai)->format('d M Y'),
                'waktu_selesai'        => optional($plan->waktu_selesai)->format('d M Y'),
                'status_pengisian'     => $plan->status_pengisian ?? 'pengisian',
                'konklusi_keseluruhan' => $plan->auditResult?->konklusi_keseluruhan,
            ],
            'auditors'    => $auditors,
            'progressTK'  => $mkeyOf('tk'),
            'progressMK'  => $mkeyOf('mk'),
            'progressFK'  => $mkeyOf('fk'),
            'totalTemuan' => $totalTemuan,
            'tlSelesai'   => $tlSelesai,
            'lhakStatus'  => $lhakStatus,
        ]);
    }
}
