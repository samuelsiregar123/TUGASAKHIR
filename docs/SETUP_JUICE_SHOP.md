# Setup OWASP Juice Shop + ZAP — Target Pengujian SPBE-SCAN

Juice Shop adalah aplikasi web yang sengaja memiliki kerentanan, digunakan sebagai
target pengujian fitur pemindaian keamanan SPBE-SCAN.

## 1. Jalankan Juice Shop via Docker

```bash
docker run -d -p 3000:3000 bkimminich/juice-shop
```

Juice Shop dapat diakses di: **http://localhost:3000**

Verifikasi berjalan:

```bash
curl -I http://localhost:3000
```

## 2. Jalankan OWASP ZAP sebagai Daemon

ZAP harus berjalan sebagai daemon di port 8080 agar `ZapScanService` dapat
menghubunginya via REST API.

### Linux / macOS

```bash
zap.sh -daemon -port 8080 -config api.key=spbescan
```

### Windows

```cmd
zap.bat -daemon -port 8080 -config api.key=spbescan
```

### Via Docker

```bash
docker run -d -p 8080:8080 zaproxy/zap-stable \
  zap.sh -daemon -host 0.0.0.0 -port 8080 \
  -config api.key=spbescan \
  -config api.addrs.addr.name=.* \
  -config api.addrs.addr.regex=true
```

Verifikasi ZAP berjalan:

```bash
curl "http://localhost:8080/JSON/core/view/version/?apikey=spbescan"
```

## 3. Konfigurasi `.env`

```env
ZAP_API_BASE=http://127.0.0.1:8080
ZAP_API_KEY=spbescan
```

## 4. Jalankan Queue Worker

```bash
php artisan queue:work database --timeout=620
```

## 5. Jalankan Scan dari UI

1. Login sebagai auditor
2. Buka **Pemindaian** → pilih audit plan
3. Set URL Target: `http://localhost:3000`
4. Klik **Scan Semua**
5. Pantau status polling di tabel (auto-refresh tiap 5 detik)

## Ekspektasi Hasil

| Tool    | Temuan yang diharapkan pada Juice Shop |
|---------|---------------------------------------|
| curl    | Missing CSP, X-Frame-Options; Cookie tanpa HttpOnly/Secure/SameSite |
| testssl | TLSv1.0/1.1 aktif (jika diakses via HTTPS), cipher lemah |
| nmap    | Port 3000 terbuka (Node.js), mungkin port lain |
| nikto   | Server header bocor, direktori terbuka |
| zap     | XSS, SQL Injection, CSRF, Open Redirect, Missing security headers |

## Uji Isolasi Kegagalan

Untuk memverifikasi bahwa kegagalan satu tool tidak memengaruhi yang lain:

1. Matikan ZAP daemon: `pkill -f "zap.sh"`
2. Jalankan **Scan Semua** dari UI
3. ZAP akan berstatus **Gagal** (tidak bisa konek daemon)
4. curl, testssl, nmap, nikto tetap berjalan dan selesai

## Troubleshooting

**ZAP tidak bisa scan Juice Shop (cross-origin):**  
Tambahkan Juice Shop ke ZAP context via API atau UI ZAP sebelum scan.

**Scan curl gagal karena SSL:**  
Juice Shop default di port 3000 tanpa HTTPS — curl akan mendapat temuan CSP/HSTS
(karena tidak ada HTTPS), yang memang benar secara keamanan.

**testssl gagal karena bukan HTTPS:**  
testssl.sh membutuhkan HTTPS. Untuk menguji, setup reverse proxy nginx dengan SSL
atau gunakan `https://juice-sh.op` (instance publik).
