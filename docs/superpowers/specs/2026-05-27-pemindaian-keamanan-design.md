# Design: Integrasi Pemindaian Keamanan (Scan Queue)
**Tanggal**: 2026-05-27  
**Proyek**: SPBE-SCAN  
**Penulis**: Samuel Partogian Dominggos Siregar / Claude Sonnet 4.6

---

## 1. Gambaran Umum

Fitur pemindaian keamanan otomatis mengintegrasikan 5 alat bantu (curl/Guzzle,
testssl.sh, Nmap, Nikto, OWASP ZAP) ke dalam proses audit SPBE. Setiap alat
berjalan sebagai **Queue Job independen** — kegagalan satu alat tidak mempengaruhi
yang lain. Hasil scan bersifat **bantu saja**: auditor tetap bisa menilai secara
manual tanpa menjalankan scan.

**Navigasi dua-level** (konsisten dengan pola "daftar instansi dulu, baru detail"):
- **Level 1** — `GET /auditor/pemindaian` → daftar audit plan yang ditugaskan
- **Level 2** — `GET /auditor/pemindaian/{plan}` → kontrol scan + hasil

---

## 2. Perubahan Database

### 2a. Queue tables
```bash
php artisan queue:table && php artisan migrate
QUEUE_CONNECTION=database   # di .env
```

### 2b. Migrasi `scan_results` — tambah kolom
Tabel sudah ada (`tool`, `status`, `hasil_json`). Perlu 4 kolom baru:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `target_url` | string | URL yang dipindai |
| `started_at` | timestamp nullable | Saat job mulai dieksekusi |
| `finished_at` | timestamp nullable | Saat job selesai / gagal |
| `error_message` | text nullable | Pesan error jika status=gagal |

Status enum tetap: `menunggu` → `berjalan` → `selesai` / `gagal`

### 2c. Tidak perlu tabel baru
Bukti scan disisipkan ke `bukti_butir` (tabel sudah ada) via lead penilaian_butir.

---

## 3. Arsitektur Service

### 3a. Interface
```php
// app/Services/ScanServiceInterface.php
interface ScanServiceInterface {
    public function getName(): string;           // 'curl', 'testssl', dll
    public function getMappedButir(): array;     // [1,2,3,4,5,6,7] (FK butir IDs)
    public function scan(string $url): array;    // return struktur standar
}
```

### 3b. Struktur return standar
```php
[
    'tool'       => 'curl',
    'target_url' => 'https://...',
    'scanned_at' => '2026-05-27T10:00:00Z',
    'status'     => 'selesai',
    'findings'   => [
        [
            'butir_id'    => 1,             // FK butir yang relevan
            'severity'    => 'High',        // Critical/High/Medium/Low/Info
            'title'       => 'Missing CSP',
            'description' => '...',
            'evidence'    => 'Header: ...',  // ditampilkan .mono
            'raw'         => '...',
        ]
    ],
    'raw_output' => '...full output...',
]
```

### 3c. 5 Service Classes

| Class | File | Cakupan FK | Implementasi |
|---|---|---|---|
| `CurlScanService` | `app/Services/CurlScanService.php` | FK 1-7 | Guzzle HTTP, cek security headers + cookie flags |
| `TestsslScanService` | `app/Services/TestsslScanService.php` | FK kriptografi | `shell_exec testssl.sh --jsonfile` |
| `NmapScanService` | `app/Services/NmapScanService.php` | FK konfigurasi | `shell_exec nmap -sV -oX` parse XML |
| `NiktoScanService` | `app/Services/NiktoScanService.php` | FK konfigurasi/kode | `shell_exec nikto -h` |
| `ZapScanService` | `app/Services/ZapScanService.php` | FK validasi input | Guzzle → ZAP REST API port 8080 |

**Header yang dicek CurlScanService**: Content-Security-Policy, X-Frame-Options, 
Strict-Transport-Security, X-Content-Type-Options, Referrer-Policy.  
**Cookie flags**: HttpOnly, Secure, SameSite.

---

## 4. Queue Jobs

5 job di `app/Jobs/`:

```
RunCurlScan.php
RunTestsslScan.php  
RunNmapScan.php
RunNiktoScan.php
RunZapScan.php
```

**Pola setiap job** (identik, berbeda di service yang dipanggil):
```php
class RunCurlScan implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries   = 2;

    public function __construct(public ScanResult $scan) {}

    public function handle(CurlScanService $service): void
    {
        $this->scan->update(['status' => 'berjalan', 'started_at' => now()]);
        try {
            $result = $service->scan($this->scan->target_url);
            $this->scan->update([
                'status'      => 'selesai',
                'hasil_json'  => $result,
                'finished_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->scan->update([
                'status'        => 'gagal',
                'error_message' => $e->getMessage(),
                'finished_at'   => now(),
            ]);
            // Tidak re-throw — job lain tetap berjalan
        }
    }
}
```

**Isolasi kegagalan**: setiap job catch sendiri, tidak throw ke Queue. 
Jika satu gagal, queue worker tidak mempengaruhi 4 job lain.

---

## 5. Controller

### ScanController (`app/Http/Controllers/Auditor/ScanController.php`)

| Method | Route | Deskripsi |
|---|---|---|
| `index()` | GET `/auditor/pemindaian` | Daftar audit plan auditor (Level 1) |
| `show(int $planId)` | GET `/auditor/pemindaian/{plan}` | Halaman scan Level 2 |
| `start(Request, int $planId)` | POST `/auditor/pemindaian/{plan}/scan/start` | Dispatch 1 atau 5 job |
| `status(int $planId)` | GET `/auditor/pemindaian/{plan}/scan/status` | Polling — return status semua scan |
| `result(ScanResult $scan)` | GET `/auditor/scan/{scan}/result` | Detail findings satu tool |
| `tagBukti(Request, ScanResult $scan)` | POST `/auditor/scan/{scan}/tag-bukti` | Insert bukti_butir dari finding |
| `rerun(ScanResult $scan)` | POST `/auditor/scan/{scan}/rerun` | Reset + dispatch ulang job yang gagal |

**`start()` request**: `{ tool: 'curl'|'testssl'|'nmap'|'nikto'|'zap'|'semua', target_url: '...' }`

**`tagBukti()` request**: `{ butir_id: 5, finding_index: 2 }`  
→ cari lead penilaian_butir untuk (audit_plan_id, butir_id)  
→ simpan evidence finding ke file teks: `Storage::disk('public')->put("bukti-scan/{planId}/{scanId}-{index}.txt", $evidence)`  
→ INSERT `bukti_butir` dengan:
  - `jenis_acuan='efk'`
  - `auditee_id = $plan->auditRequest->auditee_id` (bukan auth()->id())
  - `nama_file = "Scan [{tool}]: {finding.title}"`
  - `path_file = "bukti-scan/{planId}/{scanId}-{index}.txt"`

Keuntungan: tidak perlu migrasi `path_file` (tetap NOT NULL), file bisa di-download oleh auditor.

---

## 6. Routes (penambahan di grup `auditor`)

```php
// Ganti placeholder lama:
Route::get('/pemindaian',          [Auditor\ScanController::class, 'index'])->name('pemindaian');
Route::get('/pemindaian/{plan}',   [Auditor\ScanController::class, 'show'])->name('pemindaian.show');
Route::post('/pemindaian/{plan}/scan/start',  [Auditor\ScanController::class, 'start'])->name('scan.start');
Route::get('/pemindaian/{plan}/scan/status',  [Auditor\ScanController::class, 'status'])->name('scan.status');
Route::get('/scan/{scan}/result',  [Auditor\ScanController::class, 'result'])->name('scan.result');
Route::post('/scan/{scan}/tag-bukti', [Auditor\ScanController::class, 'tagBukti'])->name('scan.tag_bukti');
Route::post('/scan/{scan}/rerun',  [Auditor\ScanController::class, 'rerun'])->name('scan.rerun');
```

---

## 7. UI — Vue Components

### 7a. PemindaianIndex.vue (Level 1)
- Tabel: No, Instansi, Aplikasi (url_target), Status (badge), Aksi ("Buka")
- Search (nama instansi / URL), Filter Status, Filter Tahun
- "Buka" → `router.visit('/auditor/pemindaian/{plan}')`
- Semua audit plan yang auditor ini ditugaskan (via `audit_plan_auditors`)

**Status badge** menggunakan `audit_plans.status_pengisian` + progress penilaian:
- Pengisian → chip biru
- Penilaian berlangsung → chip kuning
- Siap dipindai → chip hijau (dipakai sebagai akses Level 2)

### 7b. Pemindaian.vue (Level 2)
**Header**: nama instansi, URL target (read-only untuk anggota, editable untuk ketua_tim)

**5 tombol tool** (+ tombol "Scan Semua"):
- Warna: curl=kuning, zap/nikto=merah, testssl=biru, nmap=abu
- Tombol disabled jika tool sudah berjalan

**Tabel status scan** (auto-polling tiap 5 detik via `setInterval`, dibersihkan saat unmount):

| Kolom | Keterangan |
|---|---|
| Tool | Nama + badge warna khas |
| Status | `menunggu`=kuning, `berjalan`=biru+spinner, `selesai`=hijau, `gagal`=merah |
| Mulai | `started_at` |
| Selesai | `finished_at` |
| Aksi | Selesai→"Lihat Hasil", Gagal→"Lihat Error"+"Jalankan Ulang" |

**Stop polling** saat semua scan selesai/gagal (tidak ada lagi yang `menunggu`/`berjalan`).

### 7c. ScanResultCard.vue (komponen)
- Badge tool dengan warna khas
- Ringkasan severity: `Critical X · High X · Medium X · Low X · Info X`
- Setiap finding dalam `.scan-box` (border-left warna tool):
  - Title (bold), description, evidence (`.mono`/monospace)
  - Butir terkait: chip kode butir FK
  - Tombol "Gunakan sebagai bukti EFK butir FK-X"
- Raw output collapsible (toggle show/hide)

---

## 8. Integrasi ke Penilaian.vue

Pada butir FK yang memiliki hasil scan, tampilkan box collapsible:
```
┌─ Hasil Pemindaian Otomatis ──────────────────┐
│ [curl] Missing CSP Header — High             │
│ [nmap] Port 8080 terbuka — Medium            │
│ [Lihat detail scan]                          │
└──────────────────────────────────────────────┘
```

**Implementasi**: ScanController yang sudah ada akan pass `scanFindings` 
(findings dari `hasil_json` untuk butir FK ini) ke Penilaian via Inertia props.
Tidak memblokir penilaian manual — box hanya informatif.

---

## 9. Config & Docs

### config/scan_mapping.php
```php
return [
    'curl'    => ['butir_ids' => [/* FK 1-7 IDs */], 'bagian' => 'fk'],
    'testssl' => ['butir_ids' => [/* FK kriptografi */], 'bagian' => 'fk'],
    'nmap'    => ['butir_ids' => [/* FK konfigurasi */], 'bagian' => 'fk'],
    'nikto'   => ['butir_ids' => [/* FK konfigurasi/kode */], 'bagian' => 'fk'],
    'zap'     => ['butir_ids' => [/* FK validasi input */], 'bagian' => 'fk'],
];
```
IDs diisi dengan nilai aktual dari DB setelah mapping FK butir dikonfirmasi.

### docs/SUPERVISOR.md
Konfigurasi supervisor worker untuk production deploy.

### docs/SETUP_JUICE_SHOP.md
```bash
docker run -d -p 3000:3000 bkimminich/juice-shop
# ZAP daemon:
zap.sh -daemon -port 8080 -config api.key=spbescan
```

---

## 10. Urutan Implementasi

1. DB: queue table + alter scan_results
2. ScanResult model update (fillable baru)
3. Interface + 5 Service classes
4. 5 Queue Jobs
5. ScanController (semua methods)
6. Routes (ganti placeholder)
7. config/scan_mapping.php
8. PemindaianIndex.vue + Pemindaian.vue + ScanResultCard.vue
9. Integrasi Penilaian.vue (box scan findings)
10. SUPERVISOR.md + SETUP_JUICE_SHOP.md
11. Build + verifikasi Juice Shop

---

## 11. Batasan Penting

- **Scan adalah fitur bantu** — tidak wajib dijalankan sebelum penilaian
- **Isolasi kegagalan** — catch per-job, tidak throw, job lain tidak terpengaruh
- **Timeout 600 detik per job, tries 2** — tool eksternal lambat
- **ZAP**: asumsi daemon sudah berjalan di port 8080 di server yang sama
- **testssl/nmap/nikto**: asumsi binary tersedia di PATH server
- **CurlScanService**: bisa jalan di semua environment (Guzzle, no binary needed)
