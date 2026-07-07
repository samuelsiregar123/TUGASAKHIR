<?php

namespace App\Http\Controllers\Auditee;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditResult;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LhakController extends Controller
{
    public function index()
    {
        $auditeeId = auth()->id();

        $plans = AuditPlan::with(['auditRequest', 'auditResult'])
            ->whereHas('auditRequest', fn ($q) => $q->where('auditee_id', $auditeeId))
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'                   => $p->id,
                'instansi'             => $p->auditRequest->nama_instansi ?? '-',
                'aplikasi'             => $p->auditRequest->url_target ?? '-',
                'waktu_mulai'          => optional($p->waktu_mulai)->format('d M Y'),
                'konklusi_keseluruhan' => $p->auditResult?->konklusi_keseluruhan,
                'has_lhak'             => (bool) $p->auditResult?->path_lhak,
            ]);

        return Inertia::render('Auditee/Lhak', [
            'plans' => $plans,
        ]);
    }

    public function download(int $planId)
    {
        $plan = AuditPlan::with('auditRequest')->findOrFail($planId);
        abort_if($plan->auditRequest->auditee_id !== auth()->id(), 403);

        $result = AuditResult::where('audit_plan_id', $planId)->firstOrFail();
        abort_if(! $result->path_lhak, 404, 'LHAK belum tersedia.');

        return Storage::disk('public')->download($result->path_lhak);
    }
}
