<?php

namespace App\Http\Controllers\KetuaTim;

use App\Http\Controllers\Controller;
use App\Models\AuditPlan;
use App\Models\AuditResult;
use App\Models\LhakApproval;
use App\Models\PenilaianButir;
use App\Services\KonklusiService;
use App\Services\LhakPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class KonklusiLhakController extends Controller
{
    public function index()
    {
        $plans = AuditPlan::with(['auditRequest', 'auditResult'])
            ->latest()
            ->get()
            ->map(fn ($p) => [
                'id'                   => $p->id,
                'instansi'             => $p->auditRequest->nama_instansi ?? '-',
                'aplikasi'             => $p->auditRequest->url_target ?? '-',
                'status_pengisian'     => $p->status_pengisian ?? 'pengisian',
                'waktu_mulai'          => optional($p->waktu_mulai)->format('d M Y'),
                'tahun'                => optional($p->waktu_mulai)->format('Y'),
                'konklusi_keseluruhan' => $p->auditResult?->konklusi_keseluruhan,
                'has_lhak'             => (bool) $p->auditResult?->path_lhak,
            ]);

        return Inertia::render('KetuaTim/KonklusiLhakIndex', [
            'plans' => $plans,
        ]);
    }

    public function show(int $planId)
    {
        $plan   = AuditPlan::with(['auditRequest', 'auditResult'])->findOrFail($planId);
        $result = AuditResult::where('audit_plan_id', $planId)->first();

        $lhakUrl = null;
        if ($result?->path_lhak && Storage::disk('public')->exists($result->path_lhak)) {
            $lhakUrl = Storage::disk('public')->url($result->path_lhak);
        }

        $approval = LhakApproval::where('audit_plan_id', $planId)
            ->with('reviewer')
            ->latest()
            ->first();

        return Inertia::render('KetuaTim/KonklusiLhak', [
            'plan' => [
                'id'      => $plan->id,
                'instansi'=> $plan->auditRequest->nama_instansi ?? '-',
                'aplikasi'=> $plan->auditRequest->url_target ?? '-',
            ],
            'approval' => $approval ? [
                'status'      => $approval->status,
                'catatan'     => $approval->catatan,
                'reviewed_at' => $approval->reviewed_at?->format('d M Y H:i'),
                'reviewer'    => $approval->reviewer?->name,
            ] : null,
            'result'  => $result ? [
                'konklusi_tk'          => $result->konklusi_tk,
                'nilai_edk_tk'         => $result->nilai_edk_tk,
                'nilai_eik_tk'         => $result->nilai_eik_tk,
                'nilai_efk_tk'         => $result->nilai_efk_tk,
                'konklusi_mk'          => $result->konklusi_mk,
                'nilai_edk_mk'         => $result->nilai_edk_mk,
                'nilai_eik_mk'         => $result->nilai_eik_mk,
                'nilai_efk_mk'         => $result->nilai_efk_mk,
                'konklusi_fk'          => $result->konklusi_fk,
                'nilai_edk_fk'         => $result->nilai_edk_fk,
                'nilai_eik_fk'         => $result->nilai_eik_fk,
                'nilai_efk_fk'         => $result->nilai_efk_fk,
                'konklusi_keseluruhan' => $result->konklusi_keseluruhan,
                'lhak_url'             => $lhakUrl,
            ] : null,
        ]);
    }

    public function hitung(int $planId, KonklusiService $service)
    {
        try {
            $result = $service->hitung($planId);
        } catch (\RuntimeException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        $lhakUrl = null;
        if ($result->path_lhak && Storage::disk('public')->exists($result->path_lhak)) {
            $lhakUrl = Storage::disk('public')->url($result->path_lhak);
        }

        activity('konklusi')->causedBy(auth()->user())
            ->withProperties(['plan_id' => $planId, 'konklusi_keseluruhan' => $result->konklusi_keseluruhan])
            ->log("Hitung konklusi audit plan #{$planId}");

        return response()->json([
            'ok'     => true,
            'result' => [
                'konklusi_tk'          => $result->konklusi_tk,
                'nilai_edk_tk'         => $result->nilai_edk_tk,
                'nilai_eik_tk'         => $result->nilai_eik_tk,
                'nilai_efk_tk'         => $result->nilai_efk_tk,
                'konklusi_mk'          => $result->konklusi_mk,
                'nilai_edk_mk'         => $result->nilai_edk_mk,
                'nilai_eik_mk'         => $result->nilai_eik_mk,
                'nilai_efk_mk'         => $result->nilai_efk_mk,
                'konklusi_fk'          => $result->konklusi_fk,
                'nilai_edk_fk'         => $result->nilai_edk_fk,
                'nilai_eik_fk'         => $result->nilai_eik_fk,
                'nilai_efk_fk'         => $result->nilai_efk_fk,
                'konklusi_keseluruhan' => $result->konklusi_keseluruhan,
                'lhak_url'             => $lhakUrl,
            ],
        ]);
    }

    public function generate(int $planId, LhakPdfService $service)
    {
        // Konklusi harus sudah dihitung
        $result = AuditResult::where('audit_plan_id', $planId)->first();
        if (! $result || ! $result->konklusi_keseluruhan) {
            return response()->json(['ok' => false, 'message' => 'Hitung konklusi terlebih dahulu.'], 422);
        }

        try {
            $path    = $service->generate($planId);
            $lhakUrl = Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Gagal generate PDF: '.$e->getMessage()], 500);
        }

        activity('lhak')->causedBy(auth()->user())
            ->withProperties(['plan_id' => $planId, 'path' => $path])
            ->log("Generate LHAK audit plan #{$planId}");

        return response()->json(['ok' => true, 'lhak_url' => $lhakUrl]);
    }

    public function ajukan(AuditPlan $plan)
    {
        $result = AuditResult::where('audit_plan_id', $plan->id)->first();
        abort_if(! $result?->path_lhak, 422, 'LHAK belum digenerate.');

        $pending = LhakApproval::where('audit_plan_id', $plan->id)
            ->where('status', 'pending')
            ->exists();
        abort_if($pending, 422, 'LHAK sudah diajukan dan sedang menunggu persetujuan.');

        LhakApproval::create([
            'audit_plan_id' => $plan->id,
            'submitted_by'  => auth()->id(),
            'status'        => 'pending',
            'file_lhak'     => $result->path_lhak,
            'submitted_at'  => now(),
        ]);

        return back()->with('success', 'LHAK berhasil diajukan ke Supervisor.');
    }

    public function download(int $planId)
    {
        $result = AuditResult::where('audit_plan_id', $planId)->firstOrFail();
        abort_if(! $result->path_lhak, 404, 'LHAK belum digenerate.');

        return Storage::disk('public')->download($result->path_lhak);
    }
}
