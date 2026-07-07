<?php

namespace App\Services;

class NmapScanService implements ScanServiceInterface
{
    // Ports that should not be publicly exposed on a web application server
    private const SENSITIVE_PORTS = [21, 22, 23, 25, 110, 143, 1433, 1521, 3306, 3389, 5432, 5900, 6379, 8080, 8443, 27017];

    public function getName(): string
    {
        return 'nmap';
    }

    public function getMappedButir(): array
    {
        return config('scan_mapping.nmap.butir_ids', []);
    }

    public function scan(string $url): array
    {
        $host    = parse_url($url, PHP_URL_HOST) ?: $url;
        $tmpXml  = sys_get_temp_dir() . '/nmap_' . md5($url) . '.xml';
        $cmd     = "timeout 120 nmap -sV --open -oX " . escapeshellarg($tmpXml) . " " . escapeshellarg($host) . " 2>&1";

        exec($cmd, $outputLines, $exitCode);
        $rawOutput = $outputLines ? implode("\n", $outputLines) : 'nmap tidak dapat dijalankan atau tidak ditemukan di PATH.';
        $findings  = [];
        $butirIds  = $this->getMappedButir();
        $butirMap  = config('scan_mapping.nmap.butir_map', []);

        if (file_exists($tmpXml)) {
            $xml = simplexml_load_file($tmpXml);
            @unlink($tmpXml);

            if ($xml !== false) {
                foreach ($xml->host ?? [] as $host) {
                    foreach ($host->ports->port ?? [] as $port) {
                        $portNum  = (int) $port['portid'];
                        $protocol = (string) $port['protocol'];
                        $state    = (string) ($port->state['state'] ?? '');
                        $service  = (string) ($port->service['name'] ?? '');
                        $version  = (string) ($port->service['version'] ?? '');
                        $product  = (string) ($port->service['product'] ?? '');

                        if ($state !== 'open') {
                            continue;
                        }

                        $isSensitive = in_array($portNum, self::SENSITIVE_PORTS);
                        $severity    = $isSensitive ? 'High' : 'Medium';
                        $serviceStr  = trim("{$product} {$version}");

                        $findings[] = [
                            'butir_id'    => $butirMap[$portNum] ?? ($butirIds[0] ?? null),
                            'severity'    => $severity,
                            'title'       => "Port {$portNum}/{$protocol} terbuka ({$service})" . ($isSensitive ? ' — port sensitif' : ''),
                            'description' => $isSensitive
                                ? "Port {$portNum} adalah port layanan sensitif yang sebaiknya tidak terekspos secara publik pada server aplikasi web."
                                : "Port {$portNum}/{$protocol} ({$service}) ditemukan terbuka.",
                            'evidence'    => "{$portNum}/{$protocol}\topen\t{$service}\t{$serviceStr}",
                            'raw'         => "Port {$portNum}/{$protocol} state={$state} service={$service} product={$product} version={$version}",
                        ];
                    }
                }
            }
        } else {
            $findings[] = [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'nmap tidak tersedia di server',
                'description' => 'Binary nmap tidak ditemukan di PATH. Instal nmap untuk mengaktifkan pemindaian port.',
                'evidence'    => $rawOutput,
                'raw'         => $rawOutput,
            ];
        }

        if ($exitCode === 124) {
            array_unshift($findings, [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'Pemindaian Nmap timeout',
                'description' => 'Nmap tidak selesai dalam batas waktu 2 menit.',
                'evidence'    => '',
                'raw'         => '',
            ]);
        }

        if (empty($findings)) {
            $findings[] = [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'Tidak ditemukan port berbahaya yang terbuka',
                'description' => 'nmap tidak menemukan port sensitif yang terekspos secara publik.',
                'evidence'    => substr($rawOutput, 0, 2000),
                'raw'         => $rawOutput,
            ];
        }

        return [
            'tool'       => 'nmap',
            'target_url' => $url,
            'scanned_at' => now()->toIso8601String(),
            'status'     => 'selesai',
            'findings'   => $findings,
            'raw_output' => $rawOutput,
        ];
    }
}
