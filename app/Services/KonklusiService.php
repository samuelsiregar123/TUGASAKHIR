<?php

namespace App\Services;

use App\Models\AuditPlan;
use App\Models\AuditResult;
use App\Models\PenilaianButir;

class KonklusiService
{
    /**
     * 15-condition matrix: "edk,eik,efk" => konklusi_bagian
     */
    private const MATRIX = [
        'memadai,sesuai,efektif'                           => 'memadai',
        'memadai,sesuai,perlu_peningkatan'                 => 'perlu_peningkatan',
        'memadai,sesuai,belum_efektif'                     => 'perlu_peningkatan',
        'memadai,tidak_sesuai,efektif'                     => 'perlu_peningkatan',
        'memadai,tidak_sesuai,perlu_peningkatan'           => 'perlu_peningkatan',
        'memadai,tidak_sesuai,belum_efektif'               => 'tidak_memadai',
        'perlu_peningkatan,sesuai,efektif'                 => 'perlu_peningkatan',
        'perlu_peningkatan,sesuai,perlu_peningkatan'       => 'perlu_peningkatan',
        'perlu_peningkatan,sesuai,belum_efektif'           => 'tidak_memadai',
        'perlu_peningkatan,tidak_sesuai,efektif'           => 'perlu_peningkatan',
        'perlu_peningkatan,tidak_sesuai,perlu_peningkatan' => 'tidak_memadai',
        'perlu_peningkatan,tidak_sesuai,belum_efektif'     => 'tidak_memadai',
        'tidak_memadai,skip,efektif'                       => 'perlu_peningkatan',
        'tidak_memadai,skip,perlu_peningkatan'             => 'tidak_memadai',
        'tidak_memadai,skip,belum_efektif'                 => 'tidak_memadai',
    ];

    /**
     * Hitung dan simpan konklusi untuk semua bagian.
     * Throws \RuntimeException jika penilaian belum lengkap.
     */
    public function hitung(int $planId): AuditResult
    {
        $plan = AuditPlan::findOrFail($planId);

        $tk = $this->hitungBagian($planId, 'tk');
        $mk = $this->hitungBagian($planId, 'mk');
        $fk = $this->hitungBagian($planId, 'fk');

        $keseluruhan = $this->hitungKeseluruhan($tk['konklusi'], $mk['konklusi'], $fk['konklusi']);

        $result = AuditResult::updateOrCreate(
            ['audit_plan_id' => $planId],
            [
                'nilai_edk_tk'         => $tk['skor_edk'],
                'nilai_eik_tk'         => $tk['skor_eik'],
                'nilai_efk_tk'         => $tk['skor_efk'],
                'konklusi_tk'          => $tk['konklusi'],
                'nilai_edk_mk'         => $mk['skor_edk'],
                'nilai_eik_mk'         => $mk['skor_eik'],
                'nilai_efk_mk'         => $mk['skor_efk'],
                'konklusi_mk'          => $mk['konklusi'],
                'nilai_edk_fk'         => $fk['skor_edk'],
                'nilai_eik_fk'         => $fk['skor_eik'],
                'nilai_efk_fk'         => $fk['skor_efk'],
                'konklusi_fk'          => $fk['konklusi'],
                'konklusi_keseluruhan' => $keseluruhan,
            ]
        );

        return $result;
    }

    /**
     * Hitung skor + konklusi untuk satu bagian (tk/mk/fk).
     */
    public function hitungBagian(int $planId, string $bagian): array
    {
        // Ambil semua penilaian lead (per butir_id, auditor pertama)
        $penilaian = PenilaianButir::with('butir')
            ->where('audit_plan_id', $planId)
            ->whereHas('butir', fn ($q) => $q->where('bagian', $bagian))
            ->get()
            ->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first());

        $total = $penilaian->count();
        if ($total === 0) {
            return ['skor_edk' => null, 'skor_eik' => null, 'skor_efk' => null, 'konklusi' => null];
        }

        // Validasi kelengkapan
        $belum = $penilaian->filter(fn ($p) => empty($p->edk) || empty($p->efk))->count();
        if ($belum > 0) {
            throw new \RuntimeException("Penilaian bagian {$bagian} belum lengkap ({$belum} butir belum dinilai).");
        }

        // EDK: ((memadai×2)+(perlu_peningkatan×1)) / (total×2)
        $edkM  = $penilaian->where('edk', 'memadai')->count();
        $edkPP = $penilaian->where('edk', 'perlu_peningkatan')->count();
        $skorEdk = round(($edkM * 2 + $edkPP) / ($total * 2), 4);
        $hasilEdk = match(true) {
            $skorEdk >= 0.80 => 'memadai',
            $skorEdk >= 0.50 => 'perlu_peningkatan',
            default          => 'tidak_memadai',
        };

        // EIK: hitung hanya butir yang EDK-nya bukan tidak_memadai
        $eikCandidates = $penilaian->filter(fn ($p) => $p->edk !== 'tidak_memadai');
        $eikTotal = $eikCandidates->count();
        if ($eikTotal > 0) {
            $eikS    = $eikCandidates->where('eik', 'sesuai')->count();
            $skorEik = round($eikS / $eikTotal, 4);
            $hasilEik = $skorEik >= 0.70 ? 'sesuai' : 'tidak_sesuai';
        } else {
            $skorEik  = null;
            $hasilEik = 'skip';
        }

        // EFK: ((efektif×2)+(perlu_peningkatan×1)) / (total×2)
        $efkE  = $penilaian->where('efk', 'efektif')->count();
        $efkPP = $penilaian->where('efk', 'perlu_peningkatan')->count();
        $skorEfk = round(($efkE * 2 + $efkPP) / ($total * 2), 4);
        $hasilEfk = match(true) {
            $skorEfk >= 0.80 => 'efektif',
            $skorEfk >= 0.50 => 'perlu_peningkatan',
            default          => 'belum_efektif',
        };

        $key = implode(',', [$hasilEdk, $hasilEik, $hasilEfk]);
        $konklusi = self::MATRIX[$key] ?? 'perlu_peningkatan';

        return [
            'skor_edk' => $skorEdk,
            'skor_eik' => $skorEik,
            'skor_efk' => $skorEfk,
            'konklusi' => $konklusi,
        ];
    }

    /**
     * Konklusi keseluruhan dari 3 bagian.
     */
    public function hitungKeseluruhan(?string $tk, ?string $mk, ?string $fk): string
    {
        $vals = array_filter([$tk, $mk, $fk]);
        if (empty($vals)) return 'perlu_peningkatan';

        $tidakMemadai = count(array_filter($vals, fn ($v) => $v === 'tidak_memadai'));
        $memadai      = count(array_filter($vals, fn ($v) => $v === 'memadai'));

        if ($tidakMemadai >= 2) return 'tidak_memadai';
        if ($tidakMemadai === 1) return 'perlu_peningkatan';
        if ($memadai === count($vals)) return 'memadai';
        return 'perlu_peningkatan';
    }
}
