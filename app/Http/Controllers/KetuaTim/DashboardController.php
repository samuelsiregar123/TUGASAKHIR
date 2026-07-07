<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditRequest;
use App\Models\TemuanAudit;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $auditAktif    = AuditPlan::count();
        $temuanTerbuka = TemuanAudit::where('status_tindak_lanjut', 'terbuka')->count();
        $menungguReview = AuditRequest::where('status', 'menunggu')->count();

        $auditTerbaru = AuditPlan::with('auditRequest')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'id'            => $p->id,
                'instansi'      => $p->auditRequest->nama_instansi ?? '-',
                'waktu_mulai'   => $p->waktu_mulai,
                'waktu_selesai' => $p->waktu_selesai,
            ]);

        return Inertia::render('KetuaTim/Dashboard', compact(
            'auditAktif', 'temuanTerbuka', 'menungguReview', 'auditTerbaru'
        ));
    }
}
