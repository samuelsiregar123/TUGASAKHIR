<?php

namespace App\Traits;

use App\Models\PenilaianButir;
use App\Models\TemuanAudit;

trait SinkronTemuanOtomatis
{
    private function sinkronTemuanOtomatis(PenilaianButir $penilaian): void
    {
        $kelemahan = [];

        if ($penilaian->edk === 'tidak_memadai') {
            $kelemahan[] = 'edk_tidak_memadai';
        }
        if ($penilaian->eik === 'tidak_sesuai') {
            $kelemahan[] = 'eik_tidak_sesuai';
        }
        if ($penilaian->efk === 'belum_efektif') {
            $kelemahan[] = 'efk_belum_efektif';
        }

        $existing = TemuanAudit::where('audit_plan_id', $penilaian->audit_plan_id)
            ->where('butir_id', $penilaian->butir_id)
            ->where('sumber', 'otomatis')
            ->first();

        if (count($kelemahan) > 0) {
            if (! $existing) {
                TemuanAudit::create([
                    'audit_plan_id'        => $penilaian->audit_plan_id,
                    'butir_id'             => $penilaian->butir_id,
                    'auditor_id'           => auth()->id(),
                    'sumber'               => 'otomatis',
                    'jenis_kelemahan'      => $kelemahan,
                    'is_lengkap'           => false,
                    'is_aktif'             => true,
                    'status_tindak_lanjut' => 'proses',
                    'deskripsi'            => '',
                    'rekomendasi'          => '',
                    'risiko'               => 'sedang',
                ]);
            } else {
                $existing->update([
                    'jenis_kelemahan' => $kelemahan,
                    'is_aktif'        => true,
                ]);
            }
        } else {
            if ($existing) {
                $existing->update(['is_aktif' => false]);
            }
        }
    }
}
