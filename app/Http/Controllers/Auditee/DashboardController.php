<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditRequest;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $pengajuan = AuditRequest::with(['auditPlans'])
            ->where('auditee_id', auth()->id())
            ->latest()
            ->get()
            ->map(function ($r) {
                $plan = $r->auditPlans->first();
                return [
                    'id'           => $r->id,
                    'nama_instansi' => $r->nama_instansi,
                    'url_target'   => $r->url_target,
                    'status'       => $r->status,
                    'created_at'   => $r->created_at->format('d M Y'),
                    'plan'         => $plan ? [
                        'id'           => $plan->id,
                        'waktu_mulai'  => $plan->waktu_mulai,
                        'waktu_selesai' => $plan->waktu_selesai,
                    ] : null,
                ];
            });

        return Inertia::render('Auditee/Dashboard', [
            'pengajuan'   => $pengajuan,
            'namaInstansi' => auth()->user()->nama_instansi,
        ]);
    }
}
