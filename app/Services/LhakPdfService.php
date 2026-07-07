<?php

namespace App\Services;

use App\Models\AuditPlan;
use App\Models\AuditResult;
use App\Models\PenilaianButir;
use App\Models\TemuanAudit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LhakPdfService
{
    public function generate(int $planId): string
    {
        $plan   = AuditPlan::with(['auditRequest.auditee', 'auditors.user', 'auditResult'])->findOrFail($planId);
        $result = AuditResult::where('audit_plan_id', $planId)->firstOrFail();

        // Per-bagian penilaian detail (lead per butir)
        $penilaianTk = $this->getPenilaianBagian($planId, 'tk');
        $penilaianMk = $this->getPenilaianBagian($planId, 'mk');
        $penilaianFk = $this->getPenilaianBagian($planId, 'fk');

        $temuan = TemuanAudit::with(['butir', 'auditor'])
            ->where('audit_plan_id', $planId)
            ->orderBy('risiko')
            ->get();

        $ketuaTim = $plan->auditors
            ->firstWhere('peran', 'ketua_tim') ?? $plan->auditors->first();

        $data = [
            'plan'          => $plan,
            'result'        => $result,
            'instansi'      => $plan->auditRequest->nama_instansi ?? '-',
            'aplikasi'      => $plan->auditRequest->url_target ?? '-',
            'auditee'       => $plan->auditRequest->auditee->name ?? '-',
            'waktu_mulai'   => optional($plan->waktu_mulai)->format('d F Y'),
            'waktu_selesai' => optional($plan->waktu_selesai)->format('d F Y'),
            'ketua_tim'     => $ketuaTim?->user->name ?? '-',
            'penilaian_tk'  => $penilaianTk,
            'penilaian_mk'  => $penilaianMk,
            'penilaian_fk'  => $penilaianFk,
            'temuan'        => $temuan,
            'generated_at'  => now()->format('d F Y'),
        ];

        $pdf = Pdf::loadView('pdf.lhak', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = "LHAK_{$plan->auditRequest->nama_instansi}_{$planId}.pdf";
        $safeName = preg_replace('/[^A-Za-z0-9_\-.]/', '_', $filename);
        $path     = "lhak/{$safeName}";

        Storage::disk('public')->put($path, $pdf->output());

        $result->update(['path_lhak' => $path]);

        return $path;
    }

    private function getPenilaianBagian(int $planId, string $bagian): \Illuminate\Support\Collection
    {
        return PenilaianButir::with('butir')
            ->where('audit_plan_id', $planId)
            ->whereHas('butir', fn ($q) => $q->where('bagian', $bagian))
            ->get()
            ->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first())
            ->sortBy('butir.nomor')
            ->values();
    }
}
