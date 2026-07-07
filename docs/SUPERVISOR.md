# Konfigurasi Supervisor — Queue Worker SPBE-SCAN

Supervisor memastikan queue worker Laravel tetap berjalan setelah server restart atau crash.

## Instalasi Supervisor

```bash
sudo apt-get install supervisor
```

## File Konfigurasi

Buat file `/etc/supervisor/conf.d/spbescan-worker.conf`:

```ini
[program:spbescan-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/spbe-scan/artisan queue:work database --sleep=3 --tries=2 --max-time=3600 --timeout=620
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/spbe-scan/storage/logs/worker.log
stopwaitsecs=630
```

> `timeout=620` harus lebih besar dari `$timeout` job (600 detik) agar worker
> tidak membunuh job yang masih berjalan.

## Aktifkan dan Jalankan

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start spbescan-worker:*
```

## Cek Status

```bash
sudo supervisorctl status
```

## Restart Worker (setelah deploy)

```bash
sudo supervisorctl restart spbescan-worker:*
```

atau gunakan artisan:

```bash
php artisan queue:restart
```

## Catatan Penting

- `numprocs=2` berarti 2 worker berjalan paralel — cukup untuk menjalankan
  beberapa scan sekaligus tanpa membebani server.
- Log worker ada di `storage/logs/worker.log`.
- Job timeout per-job adalah **600 detik** (10 menit). Scan eksternal seperti
  ZAP bisa memakan waktu lama — pastikan PHP `max_execution_time` di php.ini
  cukup besar atau di-set 0 untuk CLI.
- `QUEUE_CONNECTION=database` sudah di-set di `.env`.
