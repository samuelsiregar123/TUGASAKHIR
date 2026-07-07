<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\User;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'admin'     => User::where('role', 'admin')->count(),
            'ketua_tim' => User::where('role', 'ketua_tim')->count(),
            'auditor'   => User::where('role', 'auditor')->count(),
            'auditee'   => User::where('role', 'auditee')->count(),
        ];
        $total = array_sum($stats);

        $auditAktif = AuditPlan::with(['auditRequest'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'instansi'     => $p->auditRequest->nama_instansi ?? '-',
                'waktu_mulai'  => $p->waktu_mulai,
                'waktu_selesai' => $p->waktu_selesai,
            ]);

        return Inertia::render('Admin/Dashboard', compact('stats', 'total', 'auditAktif'));
    }
}
