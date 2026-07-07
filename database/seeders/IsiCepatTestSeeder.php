<?php

/**
 * IsiCepatTestSeeder — HANYA UNTUK TESTING
 *
 * Mengisi data auditee (jawaban + bukti) dan auditor (EDK/EIK/EFK)
 * secara otomatis agar alur konklusi dan LHAK bisa langsung diuji.
 *
 * Jalankan semua plan:
 *   php artisan db:seed --class=IsiCepatTestSeeder
 *
 * Jalankan untuk satu plan (gunakan env var SEEDER_PLAN):
 *
 *   Linux / Mac:
 *     SEEDER_PLAN=2 php artisan db:seed --class=IsiCepatTestSeeder
 *
 *   Windows PowerShell:
 *     $env:SEEDER_PLAN=2; php artisan db:seed --class=IsiCepatTestSeeder
 *
 *   Windows CMD:
 *     set SEEDER_PLAN=2 && php artisan db:seed --class=IsiCepatTestSeeder
 */

namespace Database\Seeders;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IsiCepatTestSeeder extends Seeder
{
    // Distribusi nilai EDK per bagian (persentase, total harus 100)
    private const DIST = [
        'tk' => ['memadai' => 80, 'perlu_peningkatan' => 15, 'tidak_memadai' =>  5],
        'mk' => ['memadai' => 65, 'perlu_peningkatan' => 25, 'tidak_memadai' => 10],
        'fk' => ['memadai' => 50, 'perlu_peningkatan' => 30, 'tidak_memadai' => 20],
    ];

    public function run(): void
    {
        $planId = $this->parsePlanArg();

        // Validasi plan jika diberikan
        if ($planId !== null) {
            $plan = DB::table('audit_plans')->where('id', $planId)->first();
            if (! $plan) {
                $this->command->error(
                    "Audit plan dengan id {$planId} tidak ditemukan. Pastikan id sudah benar."
                );
                return;
            }
            $plans = collect([$plan]);
        } else {
            $plans = DB::table('audit_plans')->orderBy('id')->get();
            if ($plans->isEmpty()) {
                $this->command->warn('Tidak ada audit_plan di database.');
                return;
            }
        }

        $this->command->info("IsiCepatTestSeeder — memproses {$plans->count()} plan...");

        $dummyPath = $this->ensureDummyPdf();

        $grandTotal = ['jawaban' => 0, 'bukti' => 0, 'penilaian' => 0];

        foreach ($plans as $plan) {
            $stats = $this->fillPlan($plan, $dummyPath);
            foreach ($grandTotal as $k => &$v) $v += $stats[$k];
        }

        if ($plans->count() > 1) {
            $this->command->info('─────────────────────────────────────');
            $this->command->info(sprintf(
                'TOTAL (%d plan) — Jawaban: %d | Bukti: %d | Penilaian: %d',
                $plans->count(),
                $grandTotal['jawaban'],
                $grandTotal['bukti'],
                $grandTotal['penilaian']
            ));
        }

        $this->command->info('✓ Seeder selesai.');
    }

    // ──────────────────────────────────────────────
    // PARSE ARGUMENT --plan=X DARI ARGV
    // ──────────────────────────────────────────────

    private function parsePlanArg(): ?int
    {
        // Baca dari environment variable SEEDER_PLAN
        // Linux/Mac:     SEEDER_PLAN=2 php artisan db:seed --class=IsiCepatTestSeeder
        // Windows PS:    $env:SEEDER_PLAN=2; php artisan db:seed --class=IsiCepatTestSeeder
        $val = getenv('SEEDER_PLAN') ?: ($_ENV['SEEDER_PLAN'] ?? null);
        return ($val !== null && $val !== false && is_numeric($val)) ? (int) $val : null;
    }

    // ──────────────────────────────────────────────
    // GENERATE PDF DUMMY (jika belum ada)
    // ──────────────────────────────────────────────

    private function ensureDummyPdf(): string
    {
        $relativePath = 'bukti_butir/dummy/bukti_dummy.pdf';
        $fullPath     = storage_path('app/public/' . $relativePath);

        if (file_exists($fullPath)) {
            $this->command->line("  [PDF] dummy sudah ada — skip generate.");
            return $relativePath;
        }

        $dir = dirname($fullPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $generated = date('d M Y H:i:s');
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  body  { font-family: Arial, sans-serif; padding: 48px; color: #333; }
  h1    { color: #1F4E79; border-bottom: 2px solid #1F4E79; padding-bottom: 8px; font-size: 18px; }
  p     { line-height: 1.7; font-size: 13px; }
  .foot { margin-top: 48px; border-top: 1px solid #ccc; padding-top: 10px;
          font-size: 11px; color: #999; }
</style>
</head>
<body>
<h1>BUKTI DUMMY UNTUK TESTING</h1>
<p>File ini dibuat otomatis oleh <strong>IsiCepatTestSeeder</strong>
   untuk keperluan pengujian sistem SPBE-SCAN.</p>
<p>Bukan dokumen audit nyata. Digunakan sebagai bukti dukung placeholder
   saat menguji alur konklusi dan pembuatan LHAK.</p>
<div class="foot">
  <p>Generated: {$generated}</p>
  <p>SPBE-SCAN &mdash; Sistem Audit Keamanan Aplikasi Berbasis SPBE</p>
</div>
</body>
</html>
HTML;

        $opts = new Options();
        $opts->set('defaultFont', 'Arial');
        $opts->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($opts);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($fullPath, $dompdf->output());

        $this->command->info("  [PDF] dummy dibuat → {$fullPath}");
        return $relativePath;
    }

    // ──────────────────────────────────────────────
    // ISI SATU AUDIT PLAN
    // ──────────────────────────────────────────────

    private function fillPlan(object $plan, string $dummyPath): array
    {
        $auditRequest = DB::table('audit_requests')->where('id', $plan->audit_request_id)->first();
        $auditeeId    = $auditRequest->auditee_id ?? null;
        $instansi     = $auditRequest->nama_instansi ?? "Plan #{$plan->id}";

        $this->command->newLine();
        $this->command->line("── Plan #{$plan->id}: {$instansi} ──");

        // Ambil semua penilaian_butir + info butir untuk plan ini
        $allPb = DB::table('penilaian_butir as pb')
            ->join('butir_penilaian as bp', 'pb.butir_id', '=', 'bp.id')
            ->where('pb.audit_plan_id', $plan->id)
            ->select('pb.id', 'pb.butir_id', 'pb.auditor_id', 'pb.jawaban_auditee', 'pb.edk',
                     'bp.bagian', 'bp.kode')
            ->orderBy('pb.id')
            ->get();

        if ($allPb->isEmpty()) {
            $this->command->warn("  Tidak ada penilaian_butir — plan ini mungkin belum dikonfigurasi.");
            return ['jawaban' => 0, 'bukti' => 0, 'penilaian' => 0];
        }

        // Lead record per butir (record dengan id terkecil)
        $leadByButir = $allPb->groupBy('butir_id')
            ->map(fn ($g) => $g->sortBy('id')->first());

        $stats    = ['jawaban' => 0, 'bukti' => 0, 'penilaian' => 0];
        $edkCount = ['memadai' => 0, 'perlu_peningkatan' => 0, 'tidak_memadai' => 0];

        // 1. Jawaban auditee (hanya lead record, hanya yang masih NULL/kosong)
        foreach ($leadByButir as $lead) {
            if (! empty($lead->jawaban_auditee)) continue;

            DB::table('penilaian_butir')->where('id', $lead->id)->update([
                'jawaban_auditee' => "Data isian testing untuk {$lead->kode}. "
                    . "Sistem telah menerapkan kontrol keamanan sesuai standar yang berlaku.",
                'updated_at' => now(),
            ]);
            $stats['jawaban']++;
        }

        // 2. Bukti dukung (3 per butir: edk, eik, efk — hanya lead record, skip jika sudah ada)
        if ($auditeeId) {
            foreach ($leadByButir as $lead) {
                foreach (['edk', 'eik', 'efk'] as $jenis) {
                    $exists = DB::table('bukti_butir')
                        ->where('penilaian_id', $lead->id)
                        ->where('jenis_acuan', $jenis)
                        ->exists();

                    if ($exists) continue;

                    DB::table('bukti_butir')->insert([
                        'penilaian_id' => $lead->id,
                        'jenis_acuan'  => $jenis,
                        'auditee_id'   => $auditeeId,
                        'path_file'    => $dummyPath,
                        'nama_file'    => "bukti_dummy_{$lead->kode}_{$jenis}.pdf",
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                    $stats['bukti']++;
                }
            }
        } else {
            $this->command->warn("  auditee_id tidak ditemukan — bukti dilewati.");
        }

        // 3. Nilai EDK/EIK/EFK — SEMUA record penilaian_butir (per auditor), skip jika sudah dinilai
        foreach ($allPb as $pb) {
            if (! empty($pb->edk)) continue;

            $dist = self::DIST[$pb->bagian] ?? self::DIST['mk'];
            $edk  = $this->pick($dist);

            $eik = ($edk === 'tidak_memadai')
                ? null
                : $this->pick(['sesuai' => 75, 'tidak_sesuai' => 25]);

            $efk = $this->pick(['efektif' => 60, 'perlu_peningkatan' => 30, 'belum_efektif' => 10]);

            DB::table('penilaian_butir')->where('id', $pb->id)->update([
                'edk'         => $edk,
                'catatan_edk' => "Penilaian testing {$pb->kode}",
                'eik'         => $eik,
                'catatan_eik' => $eik ? "EIK testing {$pb->kode}" : null,
                'efk'         => $efk,
                'catatan_efk' => "EFK testing {$pb->kode}",
                'updated_at'  => now(),
            ]);

            $edkCount[$edk]++;
            $stats['penilaian']++;
        }

        // Output ringkasan plan ini
        $this->command->info(sprintf(
            '  Jawaban diisi  : %d baris',    $stats['jawaban']
        ));
        $this->command->info(sprintf(
            '  Bukti dibuat   : %d baris',    $stats['bukti']
        ));
        $this->command->info(sprintf(
            '  Penilaian diisi: %d baris',    $stats['penilaian']
        ));

        if (array_sum($edkCount) > 0) {
            $this->command->line(sprintf(
                '  EDK distribusi : memadai=%d | perlu_peningkatan=%d | tidak_memadai=%d',
                $edkCount['memadai'], $edkCount['perlu_peningkatan'], $edkCount['tidak_memadai']
            ));
        }

        return $stats;
    }

    // ──────────────────────────────────────────────
    // WEIGHTED RANDOM PICK
    // ──────────────────────────────────────────────

    private function pick(array $weights): string
    {
        $roll = mt_rand(1, 100);
        $cumulative = 0;
        foreach ($weights as $value => $pct) {
            $cumulative += $pct;
            if ($roll <= $cumulative) return (string) $value;
        }
        return (string) array_key_last($weights);
    }
}
