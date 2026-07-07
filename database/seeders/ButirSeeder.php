<?php

namespace Database\Seeders;

use App\Models\ButirPenilaian;
use Illuminate\Database\Seeder;

class ButirSeeder extends Seeder
{
    public function run(): void
    {
        $butirList = [];

        // TK: 25 butir (bagian=tk, nomor 1-25)
        for ($i = 1; $i <= 25; $i++) {
            $butirList[] = [
                'kode'        => "TK-{$i}",
                'bagian'      => 'tk',
                'nomor'       => $i,
                'domain'      => null,
                'pertanyaan'  => "Pertanyaan TK-{$i}",
                'rujukan_mk'  => null,
                'ada_scan'    => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // MK: 50 butir (bagian=mk, nomor 1-50)
        for ($i = 1; $i <= 50; $i++) {
            $butirList[] = [
                'kode'        => "MK-{$i}",
                'bagian'      => 'mk',
                'nomor'       => $i,
                'domain'      => null,
                'pertanyaan'  => "Pertanyaan MK-{$i}",
                'rujukan_mk'  => null,
                'ada_scan'    => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        // FK: 75 butir (bagian=fk, nomor 1-75)
        for ($i = 1; $i <= 75; $i++) {
            $butirList[] = [
                'kode'        => "FK-{$i}",
                'bagian'      => 'fk',
                'nomor'       => $i,
                'domain'      => null,
                'pertanyaan'  => "Pertanyaan FK-{$i}",
                'rujukan_mk'  => null,
                'ada_scan'    => false,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        foreach ($butirList as $butir) {
            ButirPenilaian::firstOrCreate(
                ['kode' => $butir['kode']],
                $butir
            );
        }
    }
}
