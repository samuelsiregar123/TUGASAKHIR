# Skenario Tes End-to-End SPBE-SCAN — 11 Langkah

Panduan ini mencakup skenario pengujian lengkap dari pendaftaran pengguna hingga
penyelesaian tindak lanjut. Jalankan di **Kali Linux** dengan Juice Shop dan ZAP
sudah berjalan (lihat [JUICE_SHOP.md](JUICE_SHOP.md)).

**Prasyarat:**
- SPBE-SCAN berjalan di `http://localhost:8000` (atau URL server)
- Queue worker aktif: `php artisan queue:work database --timeout=620`
- Juice Shop berjalan: `http://localhost:3000`
- ZAP daemon berjalan: `http://localhost:8080`
- Akun admin default: `admin@spbescan.go.id` / `Admin1234!`

---

## Langkah 1 — Admin Mendaftarkan Pengguna

**Aksi:**
1. Login sebagai admin (`admin@spbescan.go.id` / `Admin1234!`)
2. Buka **Kelola Pengguna** → klik **Tambah Pengguna**
3. Daftarkan pengguna berikut satu per satu:

| Nama | Email | Role | Instansi |
|------|-------|------|----------|
| Auditee Juice | auditee@juice.go.id | auditee | Dinas Juice Shop |
| Ketua Audit | ketua@spbescan.go.id | ketua_tim | — |
| Auditor TK-MK | auditor1@spbescan.go.id | auditor | — |
| Auditor FK | auditor2@spbescan.go.id | auditor | — |

**Ekspektasi:** Masing-masing pengguna berhasil dibuat, muncul dalam daftar.
Password sementara ditampilkan di notifikasi — catat untuk login.

**Kriteria Sukses:** Tabel pengguna menampilkan 4 pengguna baru tanpa error.

---

## Langkah 2 — Auditee Mengajukan Audit

**Aksi:**
1. Login sebagai `auditee@juice.go.id`
2. Buka **Pengajuan Audit** → klik **Ajukan Pengajuan Baru**
3. Isi form:
   - Nama Instansi: `Dinas Juice Shop`
   - URL Target: `http://localhost:3000`
   - Deskripsi: `Pengujian keamanan aplikasi Juice Shop untuk demo TA`
4. Submit pengajuan

**Ekspektasi:** Pengajuan muncul di daftar dengan status **Menunggu**.

**Kriteria Sukses:** Status pengajuan = "Menunggu", tidak ada error validasi.

---

## Langkah 3 — Ketua Tim Menyetujui dan Membuat Audit Plan

**Aksi:**
1. Login sebagai `ketua@spbescan.go.id`
2. Buka **Pengajuan** — lihat pengajuan dari Dinas Juice Shop
3. Klik **Setujui** pengajuan tersebut
4. Buka **Audit Plan** → klik **Buat Audit Plan** untuk pengajuan yang sudah disetujui
5. Isi form audit plan:
   - Waktu Mulai: hari ini
   - Waktu Selesai: 7 hari ke depan
   - Tambahkan auditor:
     - `Auditor TK-MK` → Peran: Anggota, Bagian: TK & MK
     - `Auditor FK` → Peran: Anggota, Bagian: FK
     - `Ketua Audit` → Peran: Ketua, Bagian: Semua
6. Submit

**Ekspektasi:** Audit plan berhasil dibuat, muncul di daftar.

**Kriteria Sukses:** Audit plan tampil dengan status aktif; auditor yang ditugaskan terlihat.

---

## Langkah 4 — Auditee Mengisi Kuesioner

**Aksi:**
1. Login sebagai `auditee@juice.go.id`
2. Buka **Kuesioner** → pilih audit Dinas Juice Shop
3. Isi jawaban dan upload bukti pada minimal **5 butir TK**, **5 butir MK**, **5 butir FK**:
   - Pilih jawaban (Ya/Tidak/Sebagian) untuk setiap butir
   - Upload file bukti (PDF/PNG) untuk setiap butir yang diisi
4. Setelah mengisi, hard-refresh browser (Ctrl+Shift+R)
5. Kembali ke halaman kuesioner — pastikan data tersimpan
6. Scroll ke bawah — cek **Ringkasan Kelengkapan** muncul dengan progress bar

**Ekspektasi:** Data jawaban dan bukti tersimpan setelah refresh.

**Kriteria Sukses:**
- Ringkasan kelengkapan menampilkan jumlah butir yang sudah diisi
- Data tidak hilang setelah hard refresh
- Upload bukti berhasil dan file dapat diakses

---

## Langkah 5 — Auditor Menjalankan Pemindaian

**Aksi:**
1. Login sebagai `auditor1@spbescan.go.id` (Auditor TK-MK)
2. Buka **Pemindaian** → pilih audit Dinas Juice Shop
3. Pastikan URL Target terisi (`http://localhost:3000`)
4. Klik **Scan Semua** — tunggu status berubah dari "Menunggu" → "Berjalan"
5. Pantau progress di tabel (auto-refresh 5 detik)
6. Untuk menguji isolasi: hentikan ZAP (`docker stop zap-daemon`) setelah memulai scan

**Ekspektasi:**
- 5 tool dimulai secara bersamaan (curl, testssl, nmap, nikto, ZAP)
- Jika ZAP gagal, tool lain tetap berjalan dan selesai
- Tool yang gagal menampilkan tombol "Lihat Error" dan "Jalankan Ulang"

**Kriteria Sukses:**
- Minimal 4 dari 5 tool berhasil selesai
- Kegagalan 1 tool tidak menghentikan tool lain (isolasi terbukti)
- Status polling update otomatis tanpa refresh manual

---

## Langkah 6 — Auditor Menilai EDK/EIK/EFK

**Aksi:**
1. Masih login sebagai `auditor1@spbescan.go.id`
2. Buka **Penilaian** → pilih audit Dinas Juice Shop
3. Nilai setiap butir yang ditugaskan (tab TK dan MK):
   - **EDK** (Efektivitas Desain Kontrol): Memadai / Perlu Peningkatan / Tidak Memadai
   - **EIK** (Efektivitas Implementasi Kontrol): Sesuai / Tidak Sesuai (skip jika EDK = Tidak Memadai)
   - **EFK** (Efektivitas Fungsi Kontrol): Efektif / Perlu Peningkatan / Belum Efektif
4. Klik **Hitung Konklusi** — sistem akan memvalidasi kelengkapan penilaian

**Ekspektasi:**
- Jika ada butir yang belum dinilai, sistem menampilkan pesan error dengan jumlah butir yang belum selesai
- Setelah semua dinilai, tombol berhasil dan menampilkan pesan konfirmasi

**Kriteria Sukses:**
- Sistem menolak hitung konklusi jika ada penilaian yang belum lengkap
- Setelah semua lengkap, validasi berhasil tanpa error

---

## Langkah 7 — Auditor Mencatat Temuan

**Aksi:**
1. Buka **Temuan** → pilih audit Dinas Juice Shop
2. Tambahkan 3 temuan:

| Judul | Risiko | Deskripsi singkat |
|-------|--------|-------------------|
| Missing Security Headers | Rendah | Header CSP dan X-Frame-Options tidak dikonfigurasi |
| Cookie Tanpa Flag Secure | Sedang | Session cookie dapat dicuri via HTTP |
| SQL Injection pada Form Login | Tinggi | Input tidak disanitasi, rentan SQLi |

3. Isi seluruh field: Judul, Deskripsi, Rekomendasi, Butir Terkait, Tingkat Risiko
4. Simpan setiap temuan

**Ekspektasi:** Ketiga temuan muncul di daftar dengan badge risiko yang sesuai.

**Kriteria Sukses:** 3 temuan tersimpan, warna badge (merah/kuning/hijau) sesuai tingkat risiko.

---

## Langkah 8 — Ketua Tim Menghitung Konklusi

**Aksi:**
1. Login sebagai `ketua@spbescan.go.id`
2. Buka **Konklusi & LHAK** → pilih audit Dinas Juice Shop
3. Klik **Hitung Konklusi**
4. Tunggu hasil muncul

**Ekspektasi:** Skor konklusi ditampilkan untuk masing-masing bagian:
- **TK** (Tata Kelola): nilai EDK/EIK/EFK + konklusi bagian
- **MK** (Manajemen Keamanan): nilai EDK/EIK/EFK + konklusi bagian
- **FK** (Fungsi Keamanan): nilai EDK/EIK/EFK + konklusi bagian
- **Konklusi Keseluruhan**: Memadai / Perlu Peningkatan / Tidak Memadai

**Kriteria Sukses:**
- Skor dan konklusi per bagian ditampilkan
- Konklusi keseluruhan muncul sesuai perhitungan tertimbang

---

## Langkah 9 — Ketua Tim Generate LHAK

**Aksi:**
1. Masih di halaman Konklusi & LHAK (Dinas Juice Shop)
2. Klik **Generate LHAK** (hanya aktif setelah konklusi dihitung)
3. Tunggu proses generate PDF

**Ekspektasi:** PDF LHAK berhasil digenerate, tombol **Unduh PDF** muncul.

**Kriteria Sukses:**
- File PDF dapat diunduh
- PDF berisi minimal: identitas instansi, tanggal audit, skor per bagian, konklusi, daftar temuan

---

## Langkah 10 — Auditee Unduh LHAK & Kirim Tindak Lanjut

**Aksi:**
1. Login sebagai `auditee@juice.go.id`
2. Buka **Unduh LHAK** → cek Dinas Juice Shop sudah memiliki LHAK
3. Klik **Unduh PDF** — verifikasi file terbuka
4. Buka **Tindak Lanjut** → pilih audit Dinas Juice Shop
5. Pilih salah satu temuan → klik untuk expand
6. Kirim pesan: `"Kami telah menambahkan header CSP dan mengaktifkan flag Secure pada cookie. Bukti terlampir."`
7. (Jika tersedia) Upload file bukti perbaikan

**Ekspektasi:**
- LHAK dapat diunduh sebagai PDF
- Pesan tindak lanjut terkirim dan muncul di thread percakapan

**Kriteria Sukses:**
- PDF LHAK berhasil didownload
- Pesan auditee muncul di thread dengan nama dan timestamp
- Status temuan masih "Proses" (belum ditandai selesai)

---

## Langkah 11 — Ketua Tim Menutup Tindak Lanjut

**Aksi:**
1. Login sebagai `ketua@spbescan.go.id`
2. Buka **Tindak Lanjut** → pilih audit Dinas Juice Shop
3. Buka thread temuan yang sudah dikirim pesan oleh auditee
4. Balas pesan: `"Bukti perbaikan diterima. Temuan ini dinyatakan selesai."`
5. Klik **Tandai Selesai** — konfirmasi pada dialog yang muncul

**Ekspektasi:**
- Pesan ketua tim muncul di thread
- Status temuan berubah dari **Proses** ke **Selesai**
- Card temuan berubah warna (hijau) menandakan selesai

**Kriteria Sukses:**
- Status temuan = "Selesai" tanpa perlu refresh
- Thread menampilkan urutan pesan yang benar (auditee → ketua tim)
- Tombol "Tandai Selesai" menghilang setelah temuan selesai

---

## Checklist Akhir

| # | Fitur | Status |
|---|-------|--------|
| 1 | Admin CRUD pengguna | ☐ |
| 2 | Auditee ajukan pengajuan | ☐ |
| 3 | Ketua setujui + buat audit plan | ☐ |
| 4 | Auditee isi kuesioner + upload bukti | ☐ |
| 5 | Auditor scan semua tool (isolasi kegagalan) | ☐ |
| 6 | Auditor nilai EDK/EIK/EFK + validasi konklusi | ☐ |
| 7 | Auditor catat 3 temuan | ☐ |
| 8 | Ketua hitung konklusi (TK+MK+FK+keseluruhan) | ☐ |
| 9 | Ketua generate LHAK PDF | ☐ |
| 10 | Auditee unduh LHAK + kirim tindak lanjut | ☐ |
| 11 | Ketua balas + tandai selesai | ☐ |

Jika semua 11 langkah berhasil tanpa error kritis, sistem SPBE-SCAN dinyatakan
**LULUS smoke test** dan siap untuk demo Tugas Akhir.
