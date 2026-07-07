<?php

/*
 * Mapping tool pemindaian ke butir FK (IDs dari tabel butir_penilaian).
 *
 * FK-1..FK-7   → ID 76-82   (autentikasi & header keamanan)
 * FK-8..FK-12  → ID 83-87   (sesi)
 * FK-13..FK-18 → ID 88-93   (kontrol akses)
 * FK-19..FK-26 → ID 94-101  (validasi input & SQL injection)
 * FK-27..FK-30 → ID 102-105 (kriptografi)
 * FK-31..FK-35 → ID 106-110 (penanganan error & log)
 * FK-36..FK-44 → ID 111-119 (data sensitif)
 * FK-45..FK-48 → ID 120-123 (komunikasi terenkripsi / TLS)
 * FK-49..FK-50 → ID 124-125 (kode berbahaya)
 * FK-51..FK-58 → ID 126-133 (privasi & logika bisnis)
 * FK-59..FK-63 → ID 134-138 (file upload)
 * FK-64..FK-70 → ID 139-145 (konfigurasi layanan web)
 * FK-71..FK-75 → ID 146-150 (konfigurasi server)
 */

return [
    'curl' => [
        'butir_ids' => [76, 77, 78, 79, 80, 81, 82, 88, 100, 114, 150],
        'butir_map' => [
            'csp'            => 100, // FK-25: perlindungan konten skrip (CSP)
            'xfo'            => 150, // FK-75: konfigurasi server (clickjacking)
            'hsts'           => 150, // FK-75: konfigurasi server (HSTS)
            'xcto'           => 150, // FK-75: konfigurasi server (MIME sniff)
            'rp'             => 114, // FK-39: data sensitif (Referrer-Policy)
            'cookie_httponly' => 88, // FK-13: kontrol akses sesi
            'cookie_secure'   => 88, // FK-13: kontrol akses sesi
            'cookie_samesite' => 88, // FK-13: kontrol akses sesi
        ],
    ],

    'testssl' => [
        'butir_ids' => [102, 103, 104, 105, 120, 121, 122, 123],
        'butir_map' => [
            'SSLv2'       => 121, // FK-46: protokol TLS/SSL lemah
            'SSLv3'       => 121,
            'TLS1'        => 121,
            'TLS1_1'      => 121,
            'BEAST'       => 122, // FK-47: cipher dan serangan TLS
            'CRIME'       => 122,
            'HEARTBLEED'  => 122,
            'LOGJAM'      => 122,
            'RC4'         => 122,
            'cert_expiry' => 123, // FK-48: sertifikat elektronik
            'cert_chain'  => 123,
        ],
    ],

    'nmap' => [
        'butir_ids' => [139, 146, 147, 148, 149, 150],
        'butir_map' => [
            22    => 146, // FK-71: konfigurasi server
            3306  => 146,
            5432  => 146,
            27017 => 146,
            6379  => 146,
            3389  => 146,
            21    => 146,
            8080  => 139, // FK-64: konfigurasi layanan web
            8443  => 139,
        ],
    ],

    'nikto' => [
        'butir_ids' => [124, 125, 139, 146, 147, 148, 149, 150],
        'butir_map' => [
            'missing_header'  => 150, // FK-75: konfigurasi server (header keamanan)
            'sensitive_file'  => 148, // FK-73: file/direktori sensitif terekspos
            'directory_index' => 148, // FK-73: directory listing
            'cors_issue'      => 139, // FK-64: konfigurasi layanan web (CORS)
            'info'            => 148, // FK-73: default fallback nikto
        ],
    ],

    'zap' => [
        'butir_ids' => [88, 94, 95, 96, 97, 98, 99, 100, 101, 106, 114, 121, 129, 130, 131, 132, 133, 134, 135, 139, 150],
        'butir_map' => [
            79   => 100, // CWE-79   XSS → FK-25: perlindungan konten skrip
            89   => 101, // CWE-89   SQL Injection → FK-26: pelindungan injeksi basis data
            352  => 88,  // CWE-352  CSRF → FK-13: pelindungan token sesi
            200  => 106, // CWE-200  Information Exposure → FK-31: penanganan error & log
            601  => 97,  // CWE-601  Open Redirect → FK-22: validasi positif
            497  => 150, // CWE-497  Timestamp Disclosure → FK-75: konfigurasi server
            264  => 139, // CWE-264  CORS Misconfiguration → FK-64: konfigurasi layanan web
            693  => 150, // CWE-693  CSP Missing → FK-75: konfigurasi server
            1021 => 150, // CWE-1021 Anti-clickjacking → FK-75: konfigurasi server
            525  => 114, // CWE-525  Cache-control → FK-39: data sensitif
            319  => 150, // CWE-319  HSTS missing → FK-75: konfigurasi server
            1004 => 88,  // CWE-1004 Cookie HttpOnly → FK-13: kontrol akses sesi
            1275 => 88,  // CWE-1275 Cookie SameSite → FK-13: kontrol akses sesi
            614  => 88,  // CWE-614  Cookie Secure → FK-13: kontrol akses sesi
            20   => 88,  // CWE-20   Cookie Poisoning → FK-13: kontrol akses sesi
            311  => 121, // CWE-311  HTTPS via HTTP → FK-46: protokol TLS/SSL lemah
            615  => 106, // CWE-615  Suspicious Comments → FK-31: penanganan error & log
        ],
        'api_base' => env('ZAP_API_BASE', 'http://127.0.0.1:8080'),
        'api_key'  => env('ZAP_API_KEY', 'spbescan'),
    ],
];
