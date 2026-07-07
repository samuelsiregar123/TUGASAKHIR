<?php
/**
 * Script untuk generate file substansi_butir.xlsx
 * Jalankan sekali: php database/seeders/data/generate_template.php
 * Lalu isi kolom acuan_edk, acuan_eik, acuan_efk secara manual di Excel.
 */

require __DIR__ . '/../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0);

$sheets = [
    'tk' => ['prefix' => 'TK', 'count' => 25],
    'mk' => ['prefix' => 'MK', 'count' => 50],
    'fk' => ['prefix' => 'FK', 'count' => 75],
];

$headerStyle = [
    'font'    => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
];

foreach ($sheets as $sheetName => $config) {
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($sheetName);

    // Header
    $headers = ['nomor_butir', 'judul_butir', 'sumber_acuan', 'acuan_edk', 'acuan_eik', 'acuan_efk'];
    foreach ($headers as $col => $header) {
        $cell = chr(65 + $col) . '1';
        $sheet->setCellValue($cell, $header);
        $sheet->getStyle($cell)->applyFromArray($headerStyle);
        $sheet->getColumnDimension(chr(65 + $col))->setWidth(30);
    }

    // Data rows
    for ($i = 1; $i <= $config['count']; $i++) {
        $row = $i + 1;
        $kode = $config['prefix'] . '-' . $i;
        $sheet->setCellValue("A{$row}", $i);
        $sheet->setCellValue("B{$row}", "Pertanyaan {$kode}");
        $sheet->setCellValue("C{$row}", '');
        $sheet->setCellValue("D{$row}", '');
        $sheet->setCellValue("E{$row}", '');
        $sheet->setCellValue("F{$row}", '');
    }
}

$path = __DIR__ . '/substansi_butir.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($path);

echo "File berhasil dibuat: {$path}\n";
echo "Silakan isi kolom acuan_edk, acuan_eik, acuan_efk di Excel,\n";
echo "lalu jalankan: php artisan db:seed --class=ImportSubstansiSeeder\n";
