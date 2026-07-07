# SPBE-SCAN

**Sistem Audit Keamanan Aplikasi Berbasis SPBE**

Aplikasi web untuk mendukung proses audit keamanan sistem informasi pemerintah
berdasarkan kerangka kerja SPBE (Sistem Pemerintahan Berbasis Elektronik).
Dibangun sebagai Tugas Akhir Program Studi Rekayasa Keamanan Siber, PSSN, 2026.

---

## Fitur Utama

- Multi-role: Admin, Ketua Tim Audit, Auditor, Auditee
- Manajemen pengajuan dan persetujuan audit
- Kuesioner penilaian berbasis butir (TK / MK / FK) dengan upload bukti
- Pemindaian keamanan otomatis: curl, testssl.sh, nmap, nikto, OWASP ZAP
- Penilaian EDK / EIK / EFK per butir
- Hitung konklusi otomatis dan generate Laporan Hasil Audit Keamanan (LHAK) PDF
- Fitur tindak lanjut temuan dengan thread pesan auditee ↔ ketua tim
- Audit trail aksi penting (via spatie/laravel-activitylog)
- Two-factor authentication (via Laravel Fortify)

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 11, PHP 8.3 |
| Auth | Laravel Jetstream + Fortify (Inertia stack) |
| Frontend | Vue 3 + Inertia.js |
| Database | MySQL 8 |
| Queue | Laravel Queue (database driver) |
| Audit Log | spatie/laravel-activitylog |

---

## Setup Lokal di Laragon (Windows)

### Prasyarat
- [Laragon](https://laragon.org/) dengan PHP 8.3 dan MySQL 8
- Node.js 20+
- Composer

### Langkah-langkah

```bash
# 1. Clone repo ke folder Laragon
cd C:\laragon\www
git clone https://github.com/USERNAME/spbe-scan.git spbe-scan
cd spbe-scan

# 2. Install dependensi PHP
composer install

# 3. Install dependensi Node
npm install

# 4. Konfigurasi environment
cp .env.example .env
```

Edit `.env`:
```env
APP_URL=http://spbe-scan.test
DB_DATABASE=spbe_scan
DB_USERNAME=root
DB_PASSWORD=
QUEUE_CONNECTION=database
```

```bash
# 5. Generate app key
php artisan key:generate

# 6. Buat database di MySQL, lalu jalankan migration + seed
php artisan migrate --seed

# 7. Symlink storage
php artisan storage:link

# 8. Build frontend
npm run build

# 9. (Terminal terpisah) Jalankan queue worker untuk scan background
php artisan queue:work database --timeout=620
```

Akses: **http://spbe-scan.test** (via Laragon virtual host)

### Kredensial Default

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@spbescan.go.id | Admin1234! |

---

## Setup di Kali Linux (untuk Pengujian Pemindaian)

Kali Linux sudah menyertakan tool yang dibutuhkan (nmap, nikto, curl, testssl.sh).
Untuk setup lengkap termasuk Juice Shop dan ZAP, lihat:

→ **[docs/JUICE_SHOP.md](docs/JUICE_SHOP.md)**

Langkah singkat:
```bash
# Target: OWASP Juice Shop
docker run -d -p 3000:3000 bkimminich/juice-shop

# ZAP daemon
docker run -d -u zap -p 8080:8080 zaproxy/zap-stable \
  zap.sh -daemon -host 0.0.0.0 -port 8080 -config api.disablekey=true

# Queue worker
php artisan queue:work database --timeout=620
```

---

## Deploy ke VPS (Opsional)

Deploy ke VPS Ubuntu bersifat **opsional** untuk TA ini.
→ **[docs/DEPLOY.md](docs/DEPLOY.md)**

---

## Skenario Pengujian End-to-End

Panduan 11 langkah dari registrasi hingga penutupan tindak lanjut:
→ **[docs/SMOKE_TEST.md](docs/SMOKE_TEST.md)**

---

## Struktur Folder Penting

```
app/Http/Controllers/
  Admin/       — Kelola pengguna, audit log
  Auditee/     — Pengajuan, kuesioner, LHAK, tindak lanjut
  Auditor/     — Pemindaian, penilaian, temuan
  KetuaTim/    — Audit plan, konklusi, LHAK, tindak lanjut

resources/js/Pages/
  Admin/       — Dashboard, Pengguna, AuditLog
  Auditee/     — Dashboard, Pengajuan, Kuesioner, Lhak, TindakLanjut
  Auditor/     — Dashboard, Pemindaian, Penilaian, Temuan, ScanResult
  KetuaTim/    — Dashboard, Pengajuan, AuditPlan, Konklusi, TindakLanjut

docs/
  DEPLOY.md      — Panduan deploy VPS Ubuntu
  JUICE_SHOP.md  — Setup target Juice Shop di Kali Linux
  SMOKE_TEST.md  — Skenario tes end-to-end 11 langkah
  SUPERVISOR.md  — Konfigurasi Supervisor queue worker
```

---

## Catatan Penting

- **Tool pemindaian** (nmap, nikto, testssl.sh) tersedia bawaan di Kali Linux.
  Untuk Windows, gunakan Kali Linux untuk pengujian scan nyata.
- **Queue worker harus berjalan** agar scan diproses.
- **ZAP daemon** harus berjalan di port 8080 sebelum ZAP scan.
- Untuk 2FA: buka `/user/profile` → aktifkan Two Factor Authentication.
