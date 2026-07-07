<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\LhakApproval;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAudit        = AuditPlan::count();
        $totalSelesai      = AuditPlan::whereHas('auditResult', fn ($q) => $q->whereNotNull('konklusi_keseluruhan'))->count();
        $totalBerjalan     = $totalAudit - $totalSelesai;
        $totalMenungguLhak = LhakApproval::where('status', 'pending')->count();

        return Inertia::render('Supervisor/Dashboard', [
            'totalAudit'        => $totalAudit,
            'totalBerjalan'     => $totalBerjalan,
            'totalSelesai'      => $totalSelesai,
            'totalMenungguLhak' => $totalMenungguLhak,
        ]);
    }
}
