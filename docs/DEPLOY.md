# Panduan Deploy SPBE-SCAN ke VPS Ubuntu

> **CATATAN PENTING:** Deploy ke VPS bersifat **OPSIONAL** untuk Tugas Akhir ini.
> Pengujian fungsional dan demo dapat dilakukan langsung dari Kali Linux menggunakan
> `php artisan serve` + queue worker lokal. VPS diperlukan hanya jika ingin akses
> publik via domain.

---

## Prasyarat

- VPS Ubuntu 22.04 LTS (minimal 2 vCPU, 2 GB RAM, 20 GB disk)
- Akses root atau sudo
- Domain/subdomain (opsional — bisa pakai IP jika hanya untuk demo internal)
- Port 80 dan 443 terbuka di firewall VPS

---

## 1. Install Dependensi Sistem

```bash
# Update sistem
apt update && apt upgrade -y

# Install Nginx + PHP 8.2 + ekstensi yang dibutuhkan
apt install -y nginx php8.2 php8.2-fpm php8.2-mbstring php8.2-xml \
  php8.2-mysql php8.2-gd php8.2-curl php8.2-zip php8.2-bcmath \
  php8.2-intl php8.2-tokenizer php8.2-redis unzip git curl

# Install MySQL 8
apt install -y mysql-server
mysql_secure_installation

# Install Node 20
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install Supervisor (untuk queue worker)
apt install -y supervisor

# Install Certbot (untuk HTTPS)
apt install -y certbot python3-certbot-nginx
```

---

## 2. Setup Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE spbescan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'spbescan'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';
GRANT ALL PRIVILEGES ON spbescan.* TO 'spbescan'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Deploy Aplikasi

```bash
# Clone repo
cd /var/www
git clone https://github.com/USERNAME/spbe-scan.git spbescan
cd spbescan

# Install PHP dependencies (tanpa dev)
composer install --no-dev --optimize-autoloader

# Install dan build frontend
npm install
npm run build

# Setup permission
chown -R www-data:www-data /var/www/spbescan
chmod -R 755 /var/www/spbescan
chmod -R 775 /var/www/spbescan/storage
chmod -R 775 /var/www/spbescan/bootstrap/cache
```

---

## 4. Konfigurasi `.env` Production

```bash
cp .env.example .env
nano .env
```

Isi nilai berikut:

```env
APP_NAME="SPBE-SCAN"
APP_ENV=production
APP_KEY=                          # Diisi setelah php artisan key:generate
APP_DEBUG=false
APP_URL=https://spbescan.yourdomain.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=spbescan
DB_USERNAME=spbescan
DB_PASSWORD=GANTI_PASSWORD_KUAT

QUEUE_CONNECTION=database

MAIL_MAILER=log                   # Ganti ke SMTP jika perlu kirim email

SESSION_DRIVER=database
SESSION_LIFETIME=120

# Tool keamanan (untuk scan)
ZAP_API_BASE=http://127.0.0.1:8080
ZAP_API_KEY=spbescan
```

---

## 5. Inisialisasi Aplikasi

```bash
# Generate key aplikasi
php artisan key:generate

# Jalankan migration dan seed data awal
php artisan migrate --seed --force

# Buat symlink storage
php artisan storage:link

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 6. Konfigurasi Nginx

Buat file konfigurasi server block:

```bash
nano /etc/nginx/sites-available/spbescan
```

```nginx
server {
    listen 80;
    server_name spbescan.yourdomain.com;    # Ganti dengan domain Anda

    root /var/www/spbescan/public;
    index index.php;

    # Redirect HTTP ke HTTPS (aktifkan setelah setup Certbot)
    # return 301 https://$host$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht { deny all; }

    # Upload file (sesuaikan dengan php.ini)
    client_max_body_size 20M;
}

# Opsional: Juice Shop di port 3000 (jika dijalankan di VPS yang sama)
server {
    listen 80;
    server_name juiceshop.yourdomain.com;
    location / {
        proxy_pass http://127.0.0.1:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

```bash
ln -s /etc/nginx/sites-available/spbescan /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

---

## 7. Setup HTTPS dengan Certbot

```bash
certbot --nginx -d spbescan.yourdomain.com
# Ikuti wizard — pilih "redirect HTTP to HTTPS"

# Tes auto-renew
certbot renew --dry-run
```

---

## 8. Queue Worker (Supervisor)

Supervisor menjalankan `queue:work` otomatis dan restart jika crash.

```bash
nano /etc/supervisor/conf.d/spbescan-worker.conf
```

```ini
[program:spbescan-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/spbescan/artisan queue:work database --sleep=3 --tries=3 --timeout=620
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/spbescan-worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start spbescan-worker:*
supervisorctl status
```

Lihat juga: [docs/SUPERVISOR.md](SUPERVISOR.md)

---

## 9. Cron untuk Laravel Scheduler

```bash
crontab -e -u www-data
```

Tambahkan:

```cron
* * * * * cd /var/www/spbescan && php artisan schedule:run >> /dev/null 2>&1
```

---

## 10. Tips Troubleshooting

| Masalah | Solusi |
|---------|--------|
| `500 Server Error` | Cek `storage/logs/laravel.log`, pastikan `APP_DEBUG=false` tidak menyembunyikan detail |
| Permission denied | `chown -R www-data:www-data storage bootstrap/cache` |
| Queue tidak jalan | `supervisorctl status` — pastikan worker aktif |
| Scan gagal semua | Pastikan tool (nmap, nikto, testssl) terinstall di VPS |
| LHAK gagal generate | Pastikan `wkhtmltopdf` atau extension PDF tersedia |
| `APP_KEY` kosong | Jalankan `php artisan key:generate` ulang |

---

## Update / Redeploy

```bash
cd /var/www/spbescan
git pull origin main
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
supervisorctl restart spbescan-worker:*
```
