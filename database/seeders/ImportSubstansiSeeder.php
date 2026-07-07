<?php

namespace Database\Seeders;

use App\Models\ButirPenilaian;
use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSubstansiSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/substansi_butir.xlsx');

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");
            return;
        }

        $spreadsheet = IOFactory::load($path);

        $sheets       = ['tk', 'mk', 'fk'];
        $totalUpdated = 0;
        $totalSkipped = 0;

        foreach ($sheets as $bagian) {
            $sheet = $spreadsheet->getSheetByName($bagian);

            if (! $sheet) {
                $this->command->warn("Sheet '{$bagian}' tidak ditemukan, dilewati.");
                continue;
            }

            $highestRow = $sheet->getHighestRow();
            $updated    = 0;
            $skipped    = 0;

            // Baris 2 ke bawah (baris 1 = header)
            for ($row = 2; $row <= $highestRow; $row++) {
                $nomor       = (int)   $sheet->getCell("A{$row}")->getValue();
                $judulButir  = trim((string) $sheet->getCell("B{$row}")->getValue());
                $sumberAcuan = trim((string) $sheet->getCell("C{$row}")->getValue());
                $acuanEdk    = trim((string) $sheet->getCell("D{$row}")->getValue());
                $acuanEik    = trim((string) $sheet->getCell("E{$row}")->getValue());
                $acuanEfk    = trim((string) $sheet->getCell("F{$row}")->getValue());

                if ($nomor === 0) {
                    continue;
                }

                $butir = ButirPenilaian::where('bagian', $bagian)
                    ->where('nomor', $nomor)
                    ->first();

                if (! $butir) {
                    $this->command->warn("  Butir {$bagian}-{$nomor} tidak ditemukan.");
                    $skipped++;
                    continue;
                }

                $butir->update([
                    'judul_butir'  => $judulButir  ?: null,
                    'sumber_acuan' => $sumberAcuan ?: null,
                    'acuan_edk'    => $acuanEdk    ?: null,
                    'acuan_eik'    => $acuanEik    ?: null,
                    'acuan_efk'    => $acuanEfk    ?: null,
                ]);

                $updated++;
            }

            $totalUpdated += $updated;
            $totalSkipped += $skipped;
            $this->command->info("Sheet [{$bagian}]: {$updated} diupdate, {$skipped} dilewati.");
        }

        $this->command->newLine();
        $this->command->info("Selesai: {$totalUpdated} butir diupdate, {$totalSkipped} dilewati.");
    }
}
