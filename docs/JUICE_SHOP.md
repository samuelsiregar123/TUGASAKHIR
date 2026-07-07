# Setup Target Pengujian — OWASP Juice Shop di Kali Linux

Panduan ini menjelaskan cara menyiapkan lingkungan pengujian untuk SPBE-SCAN
menggunakan OWASP Juice Shop sebagai target pemindaian keamanan. Semua langkah
dijalankan di **Kali Linux**.

---

## Mengapa OWASP Juice Shop?

OWASP Juice Shop adalah aplikasi web yang **sengaja mengandung kerentanan** untuk
tujuan edukasi dan pengujian. Karakteristiknya:

- Mengandung seluruh OWASP Top 10 (XSS, SQLi, IDOR, dll.)
- Tersedia sebagai Docker image resmi — mudah dijalankan
- Digunakan secara luas dalam pelatihan keamanan dan penelitian
- Kompatibel dengan semua tool pemindaian yang digunakan SPBE-SCAN

---

## 1. Verifikasi Tool Bawaan Kali Linux

Kali Linux sudah menyertakan sebagian besar tool yang dibutuhkan. Verifikasi:

```bash
curl --version
nmap --version
nikto -Version
testssl.sh --version   # atau: /opt/testssl.sh/testssl.sh --version
```

Jika `testssl.sh` belum ada:
```bash
git clone --depth 1 https://github.com/drwetter/testssl.sh.git /opt/testssl.sh
ln -s /opt/testssl.sh/testssl.sh /usr/local/bin/testssl.sh
```

---

## 2. Install Docker di Kali Linux

```bash
# Install Docker
apt update
apt install -y docker.io

# Aktifkan dan jalankan Docker
systemctl enable docker
systemctl start docker

# (Opsional) Tambahkan user ke grup docker agar tidak perlu sudo
usermod -aG docker $USER
newgrp docker
```

Verifikasi Docker berjalan:
```bash
docker --version
docker info
```

---

## 3. Jalankan OWASP Juice Shop

```bash
docker run -d \
  --name juice-shop \
  -p 3000:3000 \
  --restart unless-stopped \
  bkimminich/juice-shop
```

Verifikasi Juice Shop berjalan:
```bash
docker ps
curl -I http://localhost:3000
```

Buka di browser: **http://localhost:3000**

Anda akan melihat toko online yang mengandung banyak kerentanan tersembunyi.

---

## 4. Jalankan OWASP ZAP sebagai Daemon

ZAP harus berjalan dalam mode daemon agar `ZapScanService` SPBE-SCAN dapat
berkomunikasi dengannya via REST API.

```bash
docker run -d \
  --name zap-daemon \
  -u zap \
  -p 8080:8080 \
  --restart unless-stopped \
  zaproxy/zap-stable \
  zap.sh -daemon \
    -host 0.0.0.0 \
    -port 8080 \
    -config api.disablekey=true \
    -config api.addrs.addr.name=.* \
    -config api.addrs.addr.regex=true
```

Verifikasi ZAP berjalan:
```bash
curl "http://localhost:8080/JSON/core/view/version/"
# Respon: {"version":"2.x.x"}
```

---

## 5. Konfigurasi `.env` SPBE-SCAN

Pastikan `.env` aplikasi SPBE-SCAN sudah mengandung:

```env
ZAP_API_BASE=http://127.0.0.1:8080
ZAP_API_KEY=
```

(`api.disablekey=true` dalam perintah docker di atas membuat API key tidak diperlukan)

---

## 6. Update URL Target di SPBE-SCAN

1. Login sebagai **Auditee**
2. Buka **Pengajuan Audit** → buat pengajuan baru
3. Isi URL Target: `http://localhost:3000`
4. Lanjutkan proses audit seperti biasa

> Jika menggunakan testssl.sh dan Juice Shop berjalan tanpa HTTPS,
> scan testssl akan gagal (karena tidak ada TLS). Ini **perilaku benar** —
> testssl akan menandai ketidakhadiran HTTPS sebagai temuan keamanan.

---

## 7. Jalankan Queue Worker SPBE-SCAN

Scan diproses secara asinkron via queue. Jalankan worker di terminal terpisah:

```bash
cd /path/ke/spbe-scan
php artisan queue:work database --timeout=620 --tries=3
```

Atau jika menggunakan Supervisor (lihat [SUPERVISOR.md](SUPERVISOR.md)):
```bash
supervisorctl start spbescan-worker:*
```

---

## 8. Ekspektasi Hasil Scan pada Juice Shop

| Tool       | Temuan yang diharapkan |
|------------|------------------------|
| **curl**   | Missing CSP, X-Frame-Options; Cookie tanpa HttpOnly/Secure/SameSite |
| **testssl**| TLS tidak tersedia (Juice Shop HTTP-only di port 3000) |
| **nmap**   | Port 3000 terbuka (Node.js Express), mungkin port lain |
| **nikto**  | Server header bocor (X-Powered-By), missing security headers |
| **ZAP**    | XSS reflected, SQL Injection, CSRF, Open Redirect, missing headers |

---

## 9. Manajemen Container

```bash
# Hentikan container
docker stop juice-shop zap-daemon

# Mulai ulang
docker start juice-shop zap-daemon

# Lihat log Juice Shop
docker logs juice-shop

# Lihat log ZAP
docker logs zap-daemon

# Hapus container (data hilang)
docker rm juice-shop zap-daemon
```

---

## 10. Uji Isolasi Kegagalan

Untuk memverifikasi bahwa kegagalan satu tool tidak menghentikan tool lain:

```bash
# Hentikan ZAP saja
docker stop zap-daemon
```

Lalu jalankan **Scan Semua** dari UI SPBE-SCAN:
- ZAP → status **Gagal** (tidak bisa konek daemon)
- curl, testssl, nmap, nikto → tetap **Selesai** secara independen

Ini membuktikan isolasi kegagalan antar tool berjalan dengan baik.

---

## 11. Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `docker: command not found` | Install Docker: `apt install docker.io` |
| Port 3000 sudah dipakai | Ganti port: `-p 3001:3000` lalu update URL target |
| ZAP tidak merespons | Tunggu 30–60 detik setelah container start; ZAP butuh waktu init |
| testssl gagal pada Juice Shop | Normal — Juice Shop tidak ada HTTPS di port 3000 |
| Nikto timeout | Juice Shop kadang lambat; tingkatkan timeout di `NiktoScanService` |
| Curl scan no findings | Pastikan Juice Shop dapat diakses: `curl -I http://localhost:3000` |
