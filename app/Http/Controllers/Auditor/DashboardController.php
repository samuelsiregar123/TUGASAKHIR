<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlanAuditor;
use App\Models\PenilaianButir;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $penugasan = AuditPlanAuditor::with(['auditPlan.auditRequest'])
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($a) {
                $plan    = $a->auditPlan;
                $request = $plan->auditRequest;

                $totalButir = PenilaianButir::where('audit_plan_id', $plan->id)
                    ->where('auditor_id', auth()->id())
                    ->count();

                $selesai = PenilaianButir::where('audit_plan_id', $plan->id)
                    ->where('auditor_id', auth()->id())
                    ->whereNotNull('edk')
                    ->count();

                return [
                    'plan_id'       => $plan->id,
                    'instansi'      => $request->nama_instansi ?? '-',
                    'url_target'    => $request->url_target ?? '-',
                    'peran'         => $a->peran,
                    'bagian'        => $a->bagian,
                    'waktu_mulai'   => $plan->waktu_mulai,
                    'waktu_selesai' => $plan->waktu_selesai,
                    'total_butir'   => $totalButir,
                    'selesai'       => $selesai,
                    'persen'        => $totalButir > 0 ? round($selesai / $totalButir * 100) : 0,
                ];
            });

        return Inertia::render('Auditor/Dashboard', compact('penugasan'));
    }
}
