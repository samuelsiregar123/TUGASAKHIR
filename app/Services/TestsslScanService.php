<?php

namespace App\Services;

class TestsslScanService implements ScanServiceInterface
{
    public function getName(): string
    {
        return 'testssl';
    }

    public function getMappedButir(): array
    {
        return config('scan_mapping.testssl.butir_ids', []);
    }

    public function scan(string $url): array
    {
        $host    = parse_url($url, PHP_URL_HOST) ?: $url;
        $port    = parse_url($url, PHP_URL_PORT) ?: 443;
        $target  = "{$host}:{$port}";
        $tmpJson = sys_get_temp_dir() . '/testssl_' . md5($url) . '.json';

        $binary = trim(shell_exec('which testssl.sh 2>/dev/null') ?? '');
        if (empty($binary)) {
            $binary = trim(shell_exec('which testssl 2>/dev/null') ?? '');
        }

        if (empty($binary)) {
            return [
                'tool'       => 'testssl',
                'target_url' => $url,
                'scanned_at' => now()->toIso8601String(),
                'status'     => 'selesai',
                'findings'   => [[
                    'butir_id'    => $this->getMappedButir()[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'testssl tidak tersedia di server',
                    'description' => 'Binary testssl.sh dan testssl tidak ditemukan di PATH. Instal testssl untuk mengaktifkan pemindaian TLS.',
                    'evidence'    => '',
                    'raw'         => '',
                ]],
                'raw_output' => '',
            ];
        }

        $cmd = $binary . " --jsonfile " . escapeshellarg($tmpJson)
             . " --quiet --color 0 " . escapeshellarg($target) . " 2>&1";

        $rawOutput = shell_exec($cmd) ?? "{$binary} tidak dapat dijalankan.";
        $findings  = [];
        $butirIds  = $this->getMappedButir();
        $butirMap  = config('scan_mapping.testssl.butir_map', []);

        if (file_exists($tmpJson)) {
            $data = json_decode(file_get_contents($tmpJson), true) ?? [];
            @unlink($tmpJson);

            $skipIds      = ['FS_TLS12_sig_algs', 'FS_TLS13_sig_algs', 'TLS_session_ticket', 'overall_grade', 'grade_cap_reason'];
            $cipherGroups = ['tls1_0' => [], 'tls1_1' => [], 'tls1_2_weak' => []];
            $ocspEvidences = [];

            foreach ($data as $item) {
                $id          = $item['id'] ?? '';
                $finding     = $item['finding'] ?? '';
                $findingLow  = strtolower($finding);

                // ── Filter noise ────────────────────────────────────────────────
                if (in_array($id, $skipIds, true)) {
                    continue;
                }
                if (str_starts_with($id, 'cipherlist_') ||
                    str_starts_with($id, 'NPN')         ||
                    str_starts_with($id, 'FS')          ||
                    str_starts_with($id, 'ALPN')) {
                    continue;
                }
                // SSLv2/SSLv3 tidak aktif = bukan kerentanan
                if (in_array($id, ['SSLv2', 'SSLv3'], true) && str_contains($findingLow, 'not offered')) {
                    continue;
                }
                // TLS 1.2 / 1.3 aktif = aman, bukan temuan
                if (in_array($id, ['TLS1_2', 'TLS1_3'], true) &&
                    str_contains($findingLow, 'offered') &&
                    ! str_contains($findingLow, 'not offered')) {
                    continue;
                }
                // cert_trust + wildcard bukan kerentanan
                if (str_contains($id, 'cert_trust') && str_contains($findingLow, 'wildcard')) {
                    continue;
                }

                // ── Cipher grouping ─────────────────────────────────────────────
                if (str_starts_with($id, 'cipher-tls1_3_')) {
                    continue; // TLS 1.3 ciphers aman
                }
                if (str_starts_with($id, 'cipher-tls1_2_')) {
                    if (str_contains($finding, '3DES') || str_contains($finding, 'RC4') || str_contains($finding, 'DES')) {
                        $cipherGroups['tls1_2_weak'][] = $finding;
                    }
                    continue;
                }
                if (str_starts_with($id, 'cipher-tls1_1_')) {
                    $cipherGroups['tls1_1'][] = $finding;
                    continue;
                }
                if (str_starts_with($id, 'cipher-tls1_')) {
                    $cipherGroups['tls1_0'][] = $finding;
                    continue;
                }
                if (str_starts_with($id, 'cipher-')) {
                    continue;
                }

                // ── Severity check ──────────────────────────────────────────────
                $severity = $this->mapSeverity($item['severity'] ?? 'INFO');
                $isNotOk  = str_contains($findingLow, 'not ok')
                          || str_contains($findingLow, 'not offered')
                          || str_contains($findingLow, 'offered');

                if (! in_array($severity, ['Critical', 'High', 'Medium', 'Low']) && ! $isNotOk) {
                    continue;
                }
                if ($severity === 'Info' && ! $isNotOk) {
                    continue;
                }

                // ── OCSP_stapling: gabungkan setelah loop ───────────────────────
                if (str_starts_with($id, 'OCSP_stapling')) {
                    $ocspEvidences[] = "testssl id={$id}: {$finding}";
                    continue;
                }

                // ── Template per ID ─────────────────────────────────────────────
                [$title, $description, $butirIdOverride] = $this->resolveTemplate($id, $finding);

                $findings[] = [
                    'butir_id'    => $butirIdOverride ?? $this->resolveButirId($id, $butirMap, $butirIds),
                    'severity'    => $severity === 'Info' ? 'Low' : $severity,
                    'title'       => $title,
                    'description' => $description ?: $finding,
                    'evidence'    => "testssl id={$id}: {$finding}",
                    'raw'         => json_encode($item),
                ];
            }

            // ── Merge semua OCSP_stapling menjadi satu temuan ──────────────────
            if (! empty($ocspEvidences)) {
                $count = count($ocspEvidences);
                $findings[] = [
                    'butir_id'    => 123,
                    'severity'    => 'Low',
                    'title'       => 'OCSP Stapling tidak diaktifkan',
                    'description' => "Server tidak mengaktifkan OCSP Stapling pada {$count} sertifikat untuk mempercepat validasi sertifikat.",
                    'evidence'    => implode("\n", $ocspEvidences),
                    'raw'         => json_encode($ocspEvidences),
                ];
            }

            // ── Tambahkan cipher group findings ────────────────────────────────
            foreach ([
                'tls1_0' => [
                    'label'    => 'TLS 1.0',
                    'desc'     => 'protokol TLS 1.0 yang sudah deprecated. Seluruh cipher pada TLS 1.0 harus dinonaktifkan.',
                    'severity' => 'Medium',
                    'butir_id' => 121,
                ],
                'tls1_1' => [
                    'label'    => 'TLS 1.1',
                    'desc'     => 'protokol TLS 1.1 yang sudah deprecated. Seluruh cipher pada TLS 1.1 harus dinonaktifkan.',
                    'severity' => 'Medium',
                    'butir_id' => 121,
                ],
                'tls1_2_weak' => [
                    'label'    => 'TLS 1.2 (cipher lemah)',
                    'desc'     => 'TLS 1.2 namun termasuk cipher lemah (3DES, DES, RC4). Cipher ini sebaiknya dinonaktifkan.',
                    'severity' => 'Low',
                    'butir_id' => 122,
                ],
            ] as $group => $meta) {
                $ciphers = $cipherGroups[$group];
                if (empty($ciphers)) {
                    continue;
                }
                $count = count($ciphers);
                $label = $group === 'tls1_2_weak' ? 'TLS 1.2' : $meta['label'];
                $findings[] = [
                    'butir_id'    => $meta['butir_id'],
                    'severity'    => $meta['severity'],
                    'title'       => "{$label}: {$count} cipher masih aktif" . ($group !== 'tls1_2_weak' ? ' (deprecated)' : ' (lemah)'),
                    'description' => "Server masih mendukung {$count} cipher pada {$meta['desc']}",
                    'evidence'    => implode(', ', $ciphers),
                    'raw'         => json_encode($ciphers),
                ];
            }
        } else {
            $findings[] = [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'testssl tidak menghasilkan output JSON',
                'description' => 'Pemindaian TLS berjalan tetapi tidak menghasilkan file JSON. Periksa koneksi ke target atau log output di bawah.',
                'evidence'    => $rawOutput,
                'raw'         => $rawOutput,
            ];
        }

        if (empty($findings)) {
            $findings[] = [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'Tidak ditemukan masalah TLS signifikan',
                'description' => 'testssl tidak menemukan kerentanan TLS dengan tingkat Medium ke atas.',
                'evidence'    => substr($rawOutput, 0, 2000),
                'raw'         => $rawOutput,
            ];
        }

        return [
            'tool'       => 'testssl',
            'target_url' => $url,
            'scanned_at' => now()->toIso8601String(),
            'status'     => 'selesai',
            'findings'   => $findings,
            'raw_output' => $rawOutput,
        ];
    }

    private function resolveButirId(string $id, array $butirMap, array $butirIds): ?int
    {
        if (isset($butirMap[$id])) {
            return $butirMap[$id];
        }
        foreach ($butirMap as $key => $butirId) {
            if (str_starts_with($id, $key)) {
                return $butirId;
            }
        }
        return $butirIds[0] ?? null;
    }

    private function resolveTemplate(string $id, string $finding): array
    {
        $templates = [
            'SSLv2'         => ['SSLv2 diaktifkan (sangat tidak aman)', null, null],
            'SSLv3'         => ['SSLv3 diaktifkan (POODLE vulnerability)', null, null],
            'TLS1'          => ['TLSv1.0 diaktifkan (deprecated)', null, null],
            'TLS1_1'        => ['TLSv1.1 diaktifkan (deprecated)', null, null],
            'TLS1_2'        => ['TLSv1.2 didukung', null, null],
            'TLS1_3'        => ['TLSv1.3 didukung', null, null],
            'BEAST'         => ['Rentan terhadap BEAST attack', null, 122],
            'CRIME'         => ['Rentan terhadap CRIME attack', null, null],
            'HEARTBLEED'    => ['Rentan terhadap Heartbleed', null, null],
            'LOGJAM'        => ['Rentan terhadap Logjam attack', null, null],
            'POODLE_SSL'    => ['Rentan terhadap POODLE (SSL)', null, null],
            'RC4'           => ['Cipher RC4 diaktifkan (lemah)', null, 122],
            'cert_expiry'   => ['Sertifikat hampir kadaluarsa', null, null],
            'cert_chain'    => ['Rantai sertifikat tidak lengkap', null, null],
            'HSTS'          => [
                'Header HSTS tidak diterapkan',
                'Server tidak mengirimkan header Strict-Transport-Security. Browser tidak dipaksa menggunakan HTTPS.',
                150,
            ],
            'OCSP_stapling' => [
                'OCSP Stapling tidak diaktifkan',
                'Server tidak mengaktifkan OCSP Stapling untuk mempercepat validasi sertifikat.',
                123,
            ],
            'SWEET32'       => [
                'Rentan terhadap serangan SWEET32',
                'Server menggunakan cipher 64-bit block yang rentan terhadap serangan SWEET32.',
                122,
            ],
            'LUCKY13'       => [
                'Potensi rentan terhadap serangan LUCKY13',
                'Server menggunakan cipher CBC yang berpotensi rentan terhadap serangan timing LUCKY13.',
                122,
            ],
        ];

        if (isset($templates[$id])) {
            [$title, $desc, $butirId] = $templates[$id];
            return [$title, $desc ?? $finding, $butirId];
        }

        foreach ($templates as $key => [$title, $desc, $butirId]) {
            if (str_starts_with($id, $key)) {
                return [$title, $desc ?? $finding, $butirId];
            }
        }

        return ["Temuan TLS: {$id}", $finding, null];
    }

    private function mapSeverity(string $s): string
    {
        return match (strtoupper($s)) {
            'CRITICAL' => 'Critical',
            'HIGH'     => 'High',
            'MEDIUM'   => 'Medium',
            'LOW'      => 'Low',
            default    => 'Info',
        };
    }
}
