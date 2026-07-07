<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class CurlScanService implements ScanServiceInterface
{
    public function getName(): string
    {
        return 'curl';
    }

    public function getMappedButir(): array
    {
        return config('scan_mapping.curl.butir_ids', []);
    }

    public function scan(string $url): array
    {
        $client   = new Client(['timeout' => 30, 'verify' => false, 'allow_redirects' => true]);
        $findings = [];
        $rawParts = [];

        try {
            $response = $client->get($url);
            $headers  = $response->getHeaders();
            $rawParts[] = "HTTP/{$response->getProtocolVersion()} {$response->getStatusCode()} {$response->getReasonPhrase()}";
            foreach ($headers as $name => $values) {
                $rawParts[] = "{$name}: " . implode(', ', $values);
            }
        } catch (GuzzleException $e) {
            return [
                'tool'       => 'curl',
                'target_url' => $url,
                'scanned_at' => now()->toIso8601String(),
                'status'     => 'gagal',
                'findings'   => [],
                'raw_output' => "Koneksi gagal: " . $e->getMessage(),
            ];
        }

        $butirIds  = $this->getMappedButir();
        $butirMap  = config('scan_mapping.curl.butir_map', []);

        // --- Security headers ---
        $securityHeaders = [
            'Content-Security-Policy'   => ['severity' => 'High',   'title' => 'Content-Security-Policy tidak ada', 'butir_key' => 'csp'],
            'X-Frame-Options'           => ['severity' => 'Medium', 'title' => 'X-Frame-Options tidak ada (Clickjacking risk)', 'butir_key' => 'xfo'],
            'Strict-Transport-Security' => ['severity' => 'High',   'title' => 'HSTS (Strict-Transport-Security) tidak ada', 'butir_key' => 'hsts'],
            'X-Content-Type-Options'    => ['severity' => 'Low',    'title' => 'X-Content-Type-Options tidak ada (MIME sniff risk)', 'butir_key' => 'xcto'],
            'Referrer-Policy'           => ['severity' => 'Low',    'title' => 'Referrer-Policy tidak ada', 'butir_key' => 'rp'],
        ];

        $headerKeys = array_change_key_case($headers, CASE_LOWER);

        foreach ($securityHeaders as $header => $meta) {
            $headerLower = strtolower($header);
            if (! isset($headerKeys[$headerLower])) {
                $findings[] = [
                    'butir_id'    => $butirMap[$meta['butir_key']] ?? ($butirIds[0] ?? null),
                    'severity'    => $meta['severity'],
                    'title'       => $meta['title'],
                    'description' => "Header HTTP {$header} tidak ditemukan pada respons server. Header ini penting untuk melindungi aplikasi dari serangan umum.",
                    'evidence'    => "GET {$url}\n(Header {$header} tidak ada dalam respons)",
                    'raw'         => implode("\n", $rawParts),
                ];
            }
        }

        // --- Cookie flags ---
        $setCookieHeaders = $headerKeys['set-cookie'] ?? [];
        foreach ($setCookieHeaders as $cookieStr) {
            $cookieName = explode('=', $cookieStr)[0];
            $lowerCookie = strtolower($cookieStr);

            if (! str_contains($lowerCookie, 'httponly')) {
                $findings[] = [
                    'butir_id'    => $butirMap['cookie_httponly'] ?? ($butirIds[0] ?? null),
                    'severity'    => 'Medium',
                    'title'       => "Cookie '{$cookieName}' tidak memiliki flag HttpOnly",
                    'description' => 'Cookie tanpa HttpOnly dapat diakses via JavaScript, rentan terhadap XSS.',
                    'evidence'    => "Set-Cookie: {$cookieStr}",
                    'raw'         => implode("\n", $rawParts),
                ];
            }

            if (! str_contains($lowerCookie, 'secure')) {
                $findings[] = [
                    'butir_id'    => $butirMap['cookie_secure'] ?? ($butirIds[0] ?? null),
                    'severity'    => 'Medium',
                    'title'       => "Cookie '{$cookieName}' tidak memiliki flag Secure",
                    'description' => 'Cookie tanpa Secure dapat dikirim melalui koneksi HTTP yang tidak terenkripsi.',
                    'evidence'    => "Set-Cookie: {$cookieStr}",
                    'raw'         => implode("\n", $rawParts),
                ];
            }

            if (! str_contains($lowerCookie, 'samesite')) {
                $findings[] = [
                    'butir_id'    => $butirMap['cookie_samesite'] ?? ($butirIds[0] ?? null),
                    'severity'    => 'Low',
                    'title'       => "Cookie '{$cookieName}' tidak memiliki atribut SameSite",
                    'description' => 'Cookie tanpa SameSite rentan terhadap CSRF.',
                    'evidence'    => "Set-Cookie: {$cookieStr}",
                    'raw'         => implode("\n", $rawParts),
                ];
            }
        }

        if (empty($findings)) {
            $findings[] = [
                'butir_id'    => $butirIds[0] ?? null,
                'severity'    => 'Info',
                'title'       => 'Semua security header dasar ditemukan',
                'description' => 'Tidak ditemukan header keamanan yang hilang pada pemindaian ini.',
                'evidence'    => implode("\n", $rawParts),
                'raw'         => implode("\n", $rawParts),
            ];
        }

        return [
            'tool'       => 'curl',
            'target_url' => $url,
            'scanned_at' => now()->toIso8601String(),
            'status'     => 'selesai',
            'findings'   => $findings,
            'raw_output' => implode("\n", $rawParts),
        ];
    }
}
