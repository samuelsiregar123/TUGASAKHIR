<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Traits\SinkronTemuanOtomatis;
use Inertia\Inertia;

class PenilaianController extends Controller
{
    use SinkronTemuanOtomatis;
    public function index()
    {
        $plans = AuditPlan::with(['auditRequest', 'auditors.user', 'penilaianButir'])
            ->latest()
            ->get()
            ->map(function ($p) {
                $total  = $p->penilaianButir->count();
                $dinilai = $p->penilaianButir->filter(fn ($pb) => ! empty($pb->edk))->count();

                return [
                    'id'           => $p->id,
                    'instansi'     => $p->auditRequest->nama_instansi ?? '-',
                    'url_target'   => $p->auditRequest->url_target ?? '-',
                    'waktu_mulai'  => optional($p->waktu_mulai)->format('d M Y'),
                    'total_butir'  => $total,
                    'dinilai'      => $dinilai,
                    'persen'       => $total > 0 ? round($dinilai / $total * 100) : 0,
                    'auditors'     => $p->auditors
                        ->where('peran', 'anggota')
                        ->map(fn ($a) => $a->user?->name ?? '-')
                        ->values(),
                ];
            });

        return Inertia::render('KetuaTim/PenilaianIndex', ['plans' => $plans]);
    }
}
