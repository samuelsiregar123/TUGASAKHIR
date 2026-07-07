<?php

namespace App\Services;

class NiktoScanService implements ScanServiceInterface
{
    public function getName(): string
    {
        return 'nikto';
    }

    public function getMappedButir(): array
    {
        return config('scan_mapping.nikto.butir_ids', []);
    }

    public function scan(string $url): array
    {
        $cmd = "timeout 300 nikto -h " . escapeshellarg($url) . " -Format txt -nointeractive 2>&1";

        exec($cmd, $outputLines, $exitCode);
        $rawOutput = $outputLines ? implode("\n", $outputLines) : 'nikto tidak dapat dijalankan atau tidak ditemukan di PATH.';

        $findings = [];
        $butirIds = $this->getMappedButir();
        $butirMap = config('scan_mapping.nikto.butir_map', []);

        // Nikto v2.6+ tidak lagi menyertakan 'OSVDB' — cukup cek banner versi
        $niktoRan = str_contains($rawOutput, 'Nikto v') || str_contains($rawOutput, 'Nikto/');

        if ($niktoRan) {
            $lines = explode("\n", $rawOutput);
            foreach ($lines as $line) {
                $line = trim($line);
                if (! str_starts_with($line, '+')) {
                    continue;
                }
                if (str_contains($line, 'Target IP')   || str_contains($line, 'Target Hostname') ||
                    str_contains($line, 'Target Port')  || str_contains($line, 'Start Time') ||
                    str_contains($line, 'End Time')     || str_contains($line, 'requests:') ||
                    str_contains($line, 'Platform:')    || str_contains($line, 'Server:') ||
                    str_contains($line, 'No CGI')       || str_contains($line, 'host(s) tested') ||
                    str_contains($line, 'Scan terminated') || str_contains($line, 'x-recruiting') ||
                    str_contains($line, 'ERROR: Failed to check for updates') ||
                    str_contains($line, 'ERROR: *** Error limit') ||
                    str_contains($line, 'ERROR: *** Consider using') ||
                    str_contains($line, 'SSL Info:') ||
                    str_contains($line, 'alt-svc') ||
                    str_contains(strtolower($line), 'wildcard certificate')) {
                    continue;
                }

                $lower = strtolower($line);

                // Parse Nikto ID format [XXXXXX] (v2.6+)
                $niktoId = null;
                if (preg_match('/\[(\d{6})\]/', $line, $m)) {
                    $niktoId = $m[1];
                }

                // Klasifikasi kategori berdasarkan konten baris
                if (str_contains($lower, 'security header') ||
                    str_contains($lower, 'x-frame-options') ||
                    str_contains($lower, 'x-content-type') ||
                    str_contains($lower, 'strict-transport') ||
                    str_contains($lower, 'content-security-policy') ||
                    str_contains($lower, 'referrer-policy') ||
                    str_contains($lower, 'permissions-policy')) {
                    $category = 'missing_header';
                } elseif (str_contains($lower, '.htpasswd') ||
                          str_contains($lower, '.bash_history') ||
                          str_contains($lower, '.sh_history') ||
                          str_contains($lower, '.htaccess') ||
                          str_contains($lower, '.git/') ||
                          str_contains($lower, '.env') ||
                          str_contains($lower, '.svn') ||
                          str_contains($lower, 'backup') ||
                          str_contains($lower, 'password') ||
                          str_contains($lower, 'authorization information')) {
                    $category = 'sensitive_file';
                } elseif (str_contains($lower, 'directory') ||
                          str_contains($lower, 'indexing') ||
                          str_contains($lower, 'listing') ||
                          str_contains($lower, 'this might be interesting')) {
                    $category = 'directory_index';
                } elseif (str_contains($lower, 'access-control-allow-origin') ||
                          str_contains($lower, 'cors') ||
                          str_contains($lower, 'cross-origin')) {
                    $category = 'cors_issue';
                } elseif (str_contains($lower, 'robots.txt')) {
                    $category = 'robots';
                } elseif (str_contains($lower, 'breach')) {
                    $category = 'crypto_attack';
                } else {
                    $category = 'info';
                }

                // Severity berdasarkan kategori
                if ($category === 'sensitive_file') {
                    $severity = 'High';
                } elseif ($category === 'missing_header' && (
                    str_contains($lower, 'content-security-policy') ||
                    str_contains($lower, 'strict-transport')
                )) {
                    $severity = 'High';
                } elseif (in_array($category, ['missing_header', 'cors_issue', 'directory_index', 'robots', 'crypto_attack'])) {
                    $severity = 'Medium';
                } else {
                    $severity = 'Low';
                }

                // Title dan description berbasis kategori (dalam bahasa Indonesia)
                switch ($category) {
                    case 'missing_header':
                        if (str_contains($lower, 'content-security-policy')) {
                            $headerName = 'Content-Security-Policy';
                            $headerDesc = 'mencegah serangan Cross-Site Scripting (XSS) dengan membatasi sumber skrip yang diizinkan';
                        } elseif (str_contains($lower, 'strict-transport')) {
                            $headerName = 'Strict-Transport-Security';
                            $headerDesc = 'memaksa browser menggunakan koneksi HTTPS yang terenkripsi';
                        } elseif (str_contains($lower, 'referrer-policy')) {
                            $headerName = 'Referrer-Policy';
                            $headerDesc = 'mencegah kebocoran URL sensitif ke situs pihak ketiga';
                        } elseif (str_contains($lower, 'permissions-policy')) {
                            $headerName = 'Permissions-Policy';
                            $headerDesc = 'membatasi fitur browser (kamera, mikrofon, geolokasi) yang boleh digunakan';
                        } elseif (str_contains($lower, 'x-frame-options')) {
                            $headerName = 'X-Frame-Options';
                            $headerDesc = 'mencegah serangan clickjacking dengan melarang halaman dimuat dalam iframe';
                        } elseif (str_contains($lower, 'x-content-type')) {
                            $headerName = 'X-Content-Type-Options';
                            $headerDesc = 'mencegah browser melakukan MIME-sniffing yang dapat disalahgunakan penyerang';
                        } else {
                            $headerName = preg_match('/missing:\s*(\S+)/i', $line, $hm) ? $hm[1] : 'keamanan HTTP';
                            $headerDesc = 'melindungi aplikasi dari serangan umum';
                        }
                        $title       = "Header keamanan {$headerName} tidak ditemukan";
                        $description = "Server tidak mengirimkan header {$headerName} pada respons HTTP. Header ini penting untuk {$headerDesc}.";
                        break;

                    case 'sensitive_file':
                        $path = preg_match('/\]\s*(\/[^:\s]+)/', $line, $pm) ? $pm[1] : '';
                        $title       = $path ? "File sensitif {$path} dapat diakses publik" : 'File sensitif dapat diakses publik';
                        $description = ($path ? "File {$path}" : 'File sensitif') .
                            ' ditemukan dapat diakses tanpa autentikasi. File ini dapat mengandung informasi sensitif seperti kredensial, konfigurasi server, atau riwayat perintah yang dapat dimanfaatkan penyerang.';
                        break;

                    case 'directory_index':
                        $path = preg_match('/\]\s*(\/[^:\s]+)/', $line, $pm) ? $pm[1] : '';
                        $title       = $path ? "Direktori {$path} dapat diakses publik" : 'Direktori dapat diakses publik';
                        $description = ($path ? "Direktori {$path}" : 'Direktori') .
                            ' ditemukan terbuka dan dapat diakses tanpa autentikasi. Periksa apakah direktori ini berisi file sensitif yang seharusnya tidak dapat diakses oleh publik.';
                        break;

                    case 'cors_issue':
                        $title       = 'Konfigurasi CORS terlalu permisif';
                        $description = 'Server mengirimkan header Access-Control-Allow-Origin dengan nilai wildcard (*), yang mengizinkan seluruh domain mengakses sumber daya server. Sebaiknya dibatasi ke domain tepercaya saja.';
                        break;

                    case 'robots':
                        $title       = 'File robots.txt mengungkapkan struktur direktori';
                        $description = 'File robots.txt ditemukan dan berisi entri yang perlu diperiksa secara manual. File ini dapat mengungkapkan lokasi halaman atau direktori sensitif kepada penyerang.';
                        break;

                    case 'crypto_attack':
                        $title       = 'Potensi rentan terhadap serangan BREACH';
                        $description = 'Server menggunakan kompresi HTTP (deflate/gzip) yang berpotensi rentan terhadap serangan BREACH. Penyerang dapat mengeksploitasi kompresi untuk mengungkap data terenkripsi.';
                        break;

                    default: // 'info'
                        $raw         = ltrim($line, '+ ');
                        $title       = 'Nikto: ' . substr($raw, 0, 100);
                        $description = $raw;
                        break;
                }

                $findings[] = [
                    'butir_id'    => match ($category) {
                        'robots'       => 148,
                        'crypto_attack'=> 122,
                        default        => $butirMap[$category] ?? ($butirIds[0] ?? null),
                    },
                    'severity'    => $severity,
                    'title'       => $title,
                    'description' => $description,
                    'evidence'    => $line,
                    'raw'         => $line,
                ];
            }

            // Tambahkan peringatan timeout jika nikto dihentikan paksa
            if ($exitCode === 124) {
                array_unshift($findings, [
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'Pemindaian Nikto timeout',
                    'description' => 'Nikto tidak selesai dalam batas waktu 5 menit. Hasil yang sudah ditemukan tetap ditampilkan.',
                    'evidence'    => '',
                    'raw'         => '',
                ]);
            }

            if (empty($findings)) {
                $findings[] = [
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'Tidak ditemukan kerentanan konfigurasi web server',
                    'description' => 'Nikto tidak menemukan kerentanan konfigurasi yang signifikan.',
                    'evidence'    => substr($rawOutput, 0, 2000),
                    'raw'         => $rawOutput,
                ];
            }
        } else {
            $notInstalled = ! str_contains($rawOutput, 'Nikto') && ! str_contains($rawOutput, 'nikto');
            if ($notInstalled) {
                $findings[] = [
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'nikto tidak tersedia di server',
                    'description' => 'Binary nikto tidak ditemukan di PATH. Instal nikto untuk mengaktifkan pemindaian web server.',
                    'evidence'    => $rawOutput,
                    'raw'         => $rawOutput,
                ];
            } else {
                $findings[] = [
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'Tidak ditemukan kerentanan konfigurasi web server',
                    'description' => 'Nikto tidak menemukan kerentanan konfigurasi yang signifikan.',
                    'evidence'    => substr($rawOutput, 0, 2000),
                    'raw'         => $rawOutput,
                ];
            }
        }

        return [
            'tool'       => 'nikto',
            'target_url' => $url,
            'scanned_at' => now()->toIso8601String(),
            'status'     => 'selesai',
            'findings'   => $findings,
            'raw_output' => $rawOutput,
        ];
    }
}
