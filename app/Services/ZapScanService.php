<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ZapScanService implements ScanServiceInterface
{
    private string $zapBase;
    private string $apiKey;

    public function __construct()
    {
        $this->zapBase = config('scan_mapping.zap.api_base', 'http://127.0.0.1:8080');
        $this->apiKey  = config('scan_mapping.zap.api_key', '');
    }

    public function getName(): string
    {
        return 'zap';
    }

    public function getMappedButir(): array
    {
        return config('scan_mapping.zap.butir_ids', []);
    }

    public function scan(string $url): array
    {
        // ZAP mungkin berjalan di Docker dan tidak bisa akses localhost langsung
        $zapTargetHost = env('ZAP_TARGET_HOST', '');
        if (! empty($zapTargetHost)) {
            $url = preg_replace('/localhost|127\.0\.0\.1/', $zapTargetHost, $url);
        }

        $client   = new Client(['base_uri' => $this->zapBase, 'timeout' => 30]);
        $butirIds = $this->getMappedButir();
        $butirMap = config('scan_mapping.zap.butir_map', []);
        $raw      = [];
        $timedOut = false;
        $deadline = time() + 300; // 5-menit total (recurse=false lebih cepat)

        // Kirim apikey hanya kalau dikonfigurasi (ZAP mungkin berjalan dengan api.disablekey=true)
        $apiParams = $this->apiKey !== '' ? ['apikey' => $this->apiKey] : [];

        try {
            // ── 1. Spider ────────────────────────────────────────────────────────────
            $spiderResp = $client->get('/JSON/spider/action/scan/', [
                'query' => array_merge($apiParams, ['url' => $url, 'recurse' => 'true']),
            ]);
            $spiderId = json_decode($spiderResp->getBody(), true)['scan'] ?? '0';
            $raw[]    = "Spider scan ID: {$spiderId}";

            // Polling spider sampai 100% atau deadline
            do {
                if (time() >= $deadline) {
                    $timedOut = true;
                    $raw[]    = 'Timeout saat menunggu spider.';
                    break;
                }
                sleep(5);
                $statusResp = $client->get('/JSON/spider/view/status/', [
                    'query' => array_merge($apiParams, ['scanId' => $spiderId]),
                ]);
                $progress = (int) (json_decode($statusResp->getBody(), true)['status'] ?? 0);
                $raw[] = "Spider progress: {$progress}%";
            } while ($progress < 100);

            // ── 2. Active scan (hanya kalau spider selesai) ───────────────────────
            if (! $timedOut) {
                $ascanResp = $client->get('/JSON/ascan/action/scan/', [
                    'query' => array_merge($apiParams, [
                        'url'            => $url,
                        'recurse'        => 'false',
                        'scanPolicyName' => '',
                    ]),
                ]);
                $ascanId = json_decode($ascanResp->getBody(), true)['scan'] ?? '0';
                $raw[]   = "Active scan ID: {$ascanId}";

                // Polling active scan sampai 100% atau deadline
                do {
                    if (time() >= $deadline) {
                        $timedOut = true;
                        $raw[]    = 'Timeout saat menunggu active scan.';
                        break;
                    }
                    sleep(10);
                    $ascanStatus = $client->get('/JSON/ascan/view/status/', [
                        'query' => array_merge($apiParams, ['scanId' => $ascanId]),
                    ]);
                    $progress = (int) (json_decode($ascanStatus->getBody(), true)['status'] ?? 0);
                    $raw[] = "Active scan progress: {$progress}%";
                } while ($progress < 100);
            }

            // ── 3. Ambil alerts dengan paginasi (selalu — ambil yang sudah ada walau timeout) ──
            $alerts      = [];
            $batchSize   = 200;
            $batchStart  = 0;
            $batchClient = new Client(['base_uri' => $this->zapBase, 'timeout' => 15]);
            do {
                try {
                    $alertsResp = $batchClient->get('/JSON/alert/view/alerts/', [
                        'query' => array_merge($apiParams, [
                            'baseurl' => $url,
                            'start'   => $batchStart,
                            'count'   => $batchSize,
                        ]),
                    ]);
                    $batch  = json_decode($alertsResp->getBody(), true)['alerts'] ?? [];
                    $alerts = array_merge($alerts, $batch);
                    $batchStart += $batchSize;
                } catch (GuzzleException) {
                    break;
                }
            } while (count($batch) === $batchSize);
            $raw[]    = "Alerts found: " . count($alerts);
            $findings = $this->buildFindings($alerts, $butirMap, $butirIds);

            if (empty($findings)) {
                $desc = $timedOut
                    ? 'Scan dihentikan karena timeout (300 detik). Tidak ada temuan yang berhasil dikumpulkan.'
                    : 'OWASP ZAP active scan selesai tanpa menemukan alert.';
                $findings[] = [
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => $timedOut ? 'Scan ZAP dihentikan karena timeout' : 'ZAP tidak menemukan kerentanan',
                    'description' => $desc,
                    'evidence'    => implode("\n", $raw),
                    'raw'         => implode("\n", $raw),
                ];
            }

            return [
                'tool'       => 'zap',
                'target_url' => $url,
                'scanned_at' => now()->toIso8601String(),
                'status'     => $timedOut ? 'timeout' : 'selesai',
                'findings'   => $findings,
                'raw_output' => implode("\n", $raw),
            ];

        } catch (GuzzleException $e) {
            return [
                'tool'       => 'zap',
                'target_url' => $url,
                'scanned_at' => now()->toIso8601String(),
                'status'     => 'gagal',
                'findings'   => [[
                    'butir_id'    => $butirIds[0] ?? null,
                    'severity'    => 'Info',
                    'title'       => 'ZAP daemon tidak dapat dihubungi',
                    'description' => 'Pastikan OWASP ZAP daemon berjalan di port 8080. Jalankan: zap.sh -daemon -port 8080 -config api.disablekey=true',
                    'evidence'    => $e->getMessage(),
                    'raw'         => $e->getMessage(),
                ]],
                'raw_output' => "ZAP connection error: " . $e->getMessage(),
            ];
        }
    }

    private function buildFindings(array $alerts, array $butirMap, array $butirIds): array
    {
        // Kelompokkan berdasarkan nama alert + cweid untuk menghilangkan duplikat lintas URL
        $skipNames = ['User Agent Fuzzer', 'Modern Web Application'];

        $groups = [];
        foreach ($alerts as $alert) {
            $cweid = (int) ($alert['cweid'] ?? -1);
            if ($cweid <= 0) {
                continue;
            }
            $name = $alert['alert'] ?? '';
            foreach ($skipNames as $skip) {
                if (str_contains($name, $skip)) {
                    continue 2;
                }
            }
            $name = $name ?: 'Unknown Alert';
            $key  = $name . '|' . $cweid;

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'name'        => $name,
                    'cweid'       => $cweid,
                    'description' => ($alert['description'] ?? '') . "\n\nSolusi: " . ($alert['solution'] ?? ''),
                    'maxRisk'     => 0, // diperbarui via max() di bawah untuk setiap alert dalam kelompok
                    'urls'        => [],
                ];
            }

            // Selalu ambil riskcode tertinggi (3=High > 2=Medium > 1=Low > 0=Info)
            $groups[$key]['maxRisk'] = max($groups[$key]['maxRisk'], (int) ($alert['riskcode'] ?? 0));

            $alertUrl = $alert['url'] ?? '';
            if ($alertUrl && ! in_array($alertUrl, $groups[$key]['urls'], true)) {
                $groups[$key]['urls'][] = $alertUrl;
            }
        }

        $findings = [];
        foreach ($groups as $group) {
            $severity = match ($group['maxRisk']) {
                3       => 'High',
                2       => 'Medium',
                1       => 'Low',
                default => 'Info',
            };

            $butirId = $butirMap[$group['cweid']] ?? ($butirIds[0] ?? null);
            $urls    = $group['urls'];
            $total   = count($urls);

            if ($total === 0) {
                $evidence = '';
            } else {
                $shown    = array_slice($urls, 0, 3);
                $lines    = array_map(fn ($i, $u) => ($i + 1) . ". {$u}", array_keys($shown), $shown);
                $evidence = "Ditemukan di {$total} URL:\n" . implode("\n", $lines);
                if ($total > 3) {
                    $evidence .= "\n...dan " . ($total - 3) . " URL lainnya";
                }
            }

            $findings[] = [
                'butir_id'    => $butirId,
                'severity'    => $severity,
                'title'       => $group['name'],
                'description' => $group['description'],
                'evidence'    => $evidence,
                'count'       => $total,
                'raw'         => json_encode(['alert' => $group['name'], 'cweid' => $group['cweid'], 'url_count' => $total]),
            ];
        }

        return $findings;
    }
}
