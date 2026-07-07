-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: spbescan
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_plan_auditors`
--

DROP TABLE IF EXISTS `audit_plan_auditors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_plan_auditors` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_plan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `peran` enum('ketua','anggota') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'anggota',
  `bagian` enum('semua','tk_mk','fk') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'semua',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_plan_auditors_audit_plan_id_foreign` (`audit_plan_id`),
  KEY `audit_plan_auditors_user_id_foreign` (`user_id`),
  CONSTRAINT `audit_plan_auditors_audit_plan_id_foreign` FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `audit_plan_auditors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_plan_auditors`
--

LOCK TABLES `audit_plan_auditors` WRITE;
/*!40000 ALTER TABLE `audit_plan_auditors` DISABLE KEYS */;
INSERT INTO `audit_plan_auditors` VALUES (1,1,3,'anggota','tk_mk','2026-05-26 01:58:28','2026-05-26 01:58:28'),(2,2,6,'anggota','fk','2026-05-27 05:19:04','2026-05-27 05:19:04');
/*!40000 ALTER TABLE `audit_plan_auditors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_plans`
--

DROP TABLE IF EXISTS `audit_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_request_id` bigint unsigned NOT NULL,
  `waktu_mulai` date DEFAULT NULL,
  `waktu_selesai` date DEFAULT NULL,
  `status_pengisian` enum('proses','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proses',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_plans_audit_request_id_foreign` (`audit_request_id`),
  CONSTRAINT `audit_plans_audit_request_id_foreign` FOREIGN KEY (`audit_request_id`) REFERENCES `audit_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_plans`
--

LOCK TABLES `audit_plans` WRITE;
/*!40000 ALTER TABLE `audit_plans` DISABLE KEYS */;
INSERT INTO `audit_plans` VALUES (1,1,'2026-05-26','2026-05-27','proses','2026-05-26 01:58:28','2026-05-26 01:58:28'),(2,1,'2026-05-27','2026-05-29','proses','2026-05-27 05:19:04','2026-05-27 05:19:04');
/*!40000 ALTER TABLE `audit_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_requests`
--

DROP TABLE IF EXISTS `audit_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `auditee_id` bigint unsigned NOT NULL,
  `nama_instansi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url_target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `daftar_tim` text COLLATE utf8mb4_unicode_ci,
  `path_nda` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `alasan_tolak` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_requests_auditee_id_foreign` (`auditee_id`),
  CONSTRAINT `audit_requests_auditee_id_foreign` FOREIGN KEY (`auditee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_requests`
--

LOCK TABLES `audit_requests` WRITE;
/*!40000 ALTER TABLE `audit_requests` DISABLE KEYS */;
INSERT INTO `audit_requests` VALUES (1,5,'Diskominfo Kota Bogor','https://bogor.com','Sam','nda/Vv19gZ4NoXiq51eMSUy22gGA2A9SCLXjlKapgjy9.pdf','disetujui',NULL,'2026-05-26 00:42:59','2026-05-26 00:46:32');
/*!40000 ALTER TABLE `audit_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_results`
--

DROP TABLE IF EXISTS `audit_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_plan_id` bigint unsigned NOT NULL,
  `nilai_edk_tk` double DEFAULT NULL,
  `nilai_eik_tk` double DEFAULT NULL,
  `nilai_efk_tk` double DEFAULT NULL,
  `konklusi_tk` text COLLATE utf8mb4_unicode_ci,
  `nilai_edk_mk` double DEFAULT NULL,
  `nilai_eik_mk` double DEFAULT NULL,
  `nilai_efk_mk` double DEFAULT NULL,
  `konklusi_mk` text COLLATE utf8mb4_unicode_ci,
  `nilai_edk_fk` double DEFAULT NULL,
  `nilai_eik_fk` double DEFAULT NULL,
  `nilai_efk_fk` double DEFAULT NULL,
  `konklusi_fk` text COLLATE utf8mb4_unicode_ci,
  `konklusi_keseluruhan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path_lhak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_results_audit_plan_id_foreign` (`audit_plan_id`),
  CONSTRAINT `audit_results_audit_plan_id_foreign` FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_results`
--

LOCK TABLES `audit_results` WRITE;
/*!40000 ALTER TABLE `audit_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bukti_butir`
--

DROP TABLE IF EXISTS `bukti_butir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bukti_butir` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `penilaian_id` bigint unsigned NOT NULL,
  `jenis_acuan` enum('edk','eik','efk') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'edk',
  `auditee_id` bigint unsigned NOT NULL,
  `path_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bukti_butir_penilaian_id_foreign` (`penilaian_id`),
  KEY `bukti_butir_auditee_id_foreign` (`auditee_id`),
  CONSTRAINT `bukti_butir_auditee_id_foreign` FOREIGN KEY (`auditee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bukti_butir_penilaian_id_foreign` FOREIGN KEY (`penilaian_id`) REFERENCES `penilaian_butir` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bukti_butir`
--

LOCK TABLES `bukti_butir` WRITE;
/*!40000 ALTER TABLE `bukti_butir` DISABLE KEYS */;
INSERT INTO `bukti_butir` VALUES (2,76,'eik',5,'bukti/2/76/NtYiCgRlLobc72jBJH6qBSzm8AMO2Iq4HgtQp8ac.pdf','1 part2.pdf','2026-05-27 05:35:59','2026-05-27 05:35:59'),(3,76,'efk',5,'bukti/2/76/c2D0weNW6PtnyDxHA1GfrdGGkBzzQpc352vO0uVR.pdf','Analysis_of_random_number_generated_by_quantum_noise_source_and.pdf','2026-05-27 05:36:08','2026-05-27 05:36:08'),(4,201,'edk',5,'bukti/2/1/qECSuRXFNN7Kg34eqwV1cCffzYkqGhOC4sUrEHhr.png','Flowchart 7 — Tindak Lanjut Temuan.drawio.png','2026-05-27 05:36:35','2026-05-27 05:36:35'),(6,201,'eik',5,'bukti/2/1/l5uVwky9xipx1v9FEWg9rgs5aCWZijWs3R9FMI9W.jpg','112350-sequence-diagram-example-1 (1).jpeg','2026-05-27 05:36:57','2026-05-27 05:36:57'),(7,201,'efk',5,'bukti/2/1/vrbTReMjA1vCcKcCggXOo3iFZrzWHT9C95JB1jEQ.png','Flowchart 5 — Penilaian + Konklusi.drawio.png','2026-05-27 05:37:02','2026-05-27 05:37:02');
/*!40000 ALTER TABLE `bukti_butir` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `butir_penilaian`
--

DROP TABLE IF EXISTS `butir_penilaian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `butir_penilaian` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bagian` enum('tk','mk','fk') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor` smallint unsigned NOT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pertanyaan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul_butir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sumber_acuan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rujukan_mk` text COLLATE utf8mb4_unicode_ci,
  `ada_scan` tinyint(1) NOT NULL DEFAULT '0',
  `acuan_edk` text COLLATE utf8mb4_unicode_ci,
  `acuan_eik` text COLLATE utf8mb4_unicode_ci,
  `acuan_efk` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `butir_penilaian_kode_unique` (`kode`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `butir_penilaian`
--

LOCK TABLES `butir_penilaian` WRITE;
/*!40000 ALTER TABLE `butir_penilaian` DISABLE KEYS */;
INSERT INTO `butir_penilaian` VALUES (1,'TK-1','tk',1,NULL,'Pertanyaan TK-1','Manajemen Keamanan [objek audit] - Evaluasi Kinerja. (Cross-reference ke MK butir 40, kode 9.2)','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (2)',NULL,0,'Kebijakan/peraturan instansi yang menetapkan pelaksanaan evaluasi kinerja keamanan secara berkala terhadap pelaksanaan keamanan pada [Objek Audit], mencakup identifikasi area proses berisiko tinggi, penetapan indikator kinerja, pengukuran kuantitatif, analisis efektivitas, dan dukungan terhadap program audit keamanan.','Bukti pelaksanaan evaluasi kinerja: Laporan Evaluasi Kinerja Keamanan SPBE yang ditandatangani Koordinator SPBE; bukti pengukuran indikator kinerja per area proses; cakupan evaluasi yang menyentuh seluruh aspek pelaksanaan keamanan pada [Objek Audit].','Laporan tindak lanjut atas rekomendasi hasil evaluasi kinerja; bukti perbaikan kinerja keamanan pada periode pelaporan berikutnya.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(2,'TK-2','tk',2,NULL,'Pertanyaan TK-2','Manajemen Keamanan [objek audit] - Penetapan Ruang Lingkup. (Cross-reference ke MK butir 2-3, kode 2.1 & 2.2)','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 4',NULL,0,'Dokumen ruang lingkup SMKI yang ditetapkan pimpinan Instansi Pusat atau Kepala Daerah, memuat: (a) isu internal mencakup 4 area minimum — data dan informasi SPBE, Aplikasi SPBE, aset Infrastruktur SPBE, dan kebijakan keamanan informasi SPBE; serta (b) isu eksternal keamanan informasi sesuai peraturan perundang-undangan.','Bukti pendefinisian isu internal dan eksternal: notulen rapat penetapan ruang lingkup yang melibatkan pimpinan, daftar isu internal yang ditinjau berkala, analisis isu eksternal (lanskap ancaman dan kepatuhan regulasi), dokumentasi kegiatan keamanan informasi terkait isu yang teridentifikasi.','Hasil survei internal organisasi mengenai pengetahuan dan pemahaman terhadap ruang lingkup SMKI; bukti pembaruan ruang lingkup mengikuti perubahan organisasi atau lanskap ancaman.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(3,'TK-3','tk',3,NULL,'Pertanyaan TK-3','Manajemen Keamanan [objek audit] - Penetapan Penanggung Jawab. (Cross-reference ke MK butir 4-5, kode 3.1.1 & 3.1.2)','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 5',NULL,0,'Peraturan/Keputusan Kepala Instansi Pusat atau Kepala Daerah yang menetapkan Sekretaris Instansi atau Sekretaris Daerah sebagai penanggung jawab Keamanan SPBE (Koordinator SPBE) beserta uraian tugas dan tanggung jawabnya.','Bukti penetapan dan pelaksanaan tugas: SK/Perka penanggung jawab yang sah dan berlaku, surat penugasan, notulen rapat koordinasi keamanan yang dipimpin Koordinator SPBE, laporan kegiatan dan pertanggungjawaban kinerja kepada pimpinan.','Laporan pertanggungjawaban kinerja Koordinator SPBE kepada pimpinan; bukti pembaruan SK/Perka mengikuti perubahan struktural organisasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(4,'TK-4','tk',4,NULL,'Pertanyaan TK-4','Manajemen Keamanan [objek audit] - Dukungan Pengoperasian. (Cross-reference ke MK butir 36-38, kode 8.2.1.1, 8.2.1.2, 8.2.2)','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 14',NULL,0,'Dokumen rencana dukungan pengoperasian Keamanan SPBE yang ditetapkan Koordinator SPBE, mencakup: (a) rencana pelatihan dan/atau sertifikasi kompetensi keamanan TIK dan keamanan aplikasi; (b) rencana bimbingan teknis standar Keamanan SPBE; serta (c) rencana anggaran Keamanan SPBE yang disusun berdasarkan perencanaan yang ditetapkan.','Bukti pelaksanaan dukungan pengoperasian: daftar peserta dan sertifikat pelatihan/sertifikasi, dokumentasi bimbingan teknis, dokumen pengajuan dan realisasi anggaran (RKA-KL/RKA-PD), matriks kompetensi SDM yang dipenuhi.','Laporan realisasi anggaran Keamanan SPBE; laporan pemenuhan matriks kompetensi SDM setelah pelatihan/sertifikasi; bukti penerapan hasil pelatihan dalam tugas operasional.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(5,'TK-5','tk',5,NULL,'Pertanyaan TK-5','Audit Keamanan [Objek Audit] internal dilaksanakan untuk kebutuhan internal Instansi Pusat atau Pemerintah Daerah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 13',NULL,0,'Kebijakan/Peraturan Kepala Instansi Pusat atau Pemerintah Daerah yang menetapkan pelaksanaan Audit Keamanan internal untuk kebutuhan internal organisasi.','Bukti pelaksanaan: dokumen penugasan audit internal, Laporan Hasil Audit Keamanan (LHAK) internal, surat tugas auditor internal.','Laporan tindak lanjut atas temuan audit internal; bukti perbaikan kontrol keamanan berdasarkan rekomendasi audit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(6,'TK-6','tk',6,NULL,'Pertanyaan TK-6','Audit Keamanan [objek audit] internal dilaksanakan dengan Tahapan perencanaan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.2.1)',NULL,0,'SOP perencanaan audit yang memuat penyusunan rencana audit tahunan, penetapan objek audit, kriteria audit, jadwal, dan alokasi sumber daya.','Bukti dokumen perencanaan: Rencana Audit Tahunan (RAT), Program Kerja Audit (PKA), surat penugasan tim audit, kertas kerja perencanaan audit.','Bukti persetujuan rencana audit oleh pimpinan/pejabat berwenang; laporan realisasi pelaksanaan audit terhadap rencana.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(7,'TK-7','tk',7,NULL,'Pertanyaan TK-7','Audit Keamanan [objek audit] internal dilaksanakan dengan Tahapan pelaksanaan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.2.2)',NULL,0,'SOP pelaksanaan audit yang memuat prosedur pengumpulan bukti, evaluasi kontrol, dokumentasi temuan, dan komunikasi dengan auditi.','Bukti pelaksanaan: kertas kerja audit (KKA), bukti audit terkumpul, daftar pertanyaan/wawancara, dokumentasi observasi/inspeksi.','Laporan hasil pelaksanaan audit yang dilegalisasi; bukti reviu kertas kerja oleh pengendali mutu.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(8,'TK-8','tk',8,NULL,'Pertanyaan TK-8','Audit Keamanan [objek audit] internal dilaksanakan dengan Tahapan pelaporan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.2.3)',NULL,0,'SOP pelaporan audit yang memuat format LHAK, mekanisme reviu pengendali mutu, dan distribusi laporan.','Bukti pelaporan: Laporan Hasil Audit Keamanan (LHAK) yang ditandatangani, nota dinas penyampaian laporan, bukti distribusi kepada pihak terkait.','Bukti penerimaan LHAK oleh pemangku kepentingan; tanggapan auditi atas hasil audit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(9,'TK-9','tk',9,NULL,'Pertanyaan TK-9','Audit Keamanan [objek audit] internal dilaksanakan dengan Tahapan pemantauan tindak lanjut.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.2.4)',NULL,0,'SOP pemantauan tindak lanjut yang memuat mekanisme monitoring, pelaporan status, dan eskalasi tindak lanjut yang tertunda.','Bukti pemantauan: matriks tindak lanjut, laporan status tindak lanjut periodik, bukti komunikasi kepada auditi terkait status.','Laporan status penyelesaian tindak lanjut rekomendasi; bukti penurunan temuan berulang antar-audit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(10,'TK-10','tk',10,NULL,'Pertanyaan TK-10','Audit Keamanan [Objek Audit] internal dilaksanakan secara periodik oleh auditor pada unit kerja Instansi Pusat atau Pemerintah Daerah yang melaksanakan tugas dan fungsi di bidang pengawasan intern.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.3)',NULL,0,'Kebijakan instansi yang menetapkan frekuensi pelaksanaan audit internal secara periodik dan penunjukan unit kerja pengawasan intern (mis. Inspektorat).','Bukti pelaksanaan periodik: jadwal audit tahunan, riwayat pelaksanaan audit, identitas unit kerja pelaksana (Inspektorat/APIP).','Laporan rekapitulasi pelaksanaan audit per tahun; bukti tinjauan periodisasi terhadap seluruh Objek Audit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(11,'TK-11','tk',11,NULL,'Pertanyaan TK-11','Unit kerja Instansi Pusat atau Pemerintah Daerah yang melaksanakan tugas dan fungsi di bidang pengawasan intern dapat melibatkan pegawai aparatur sipil negara dari unit kerja lain yang memiliki kompetensi audit TIK dan/atau audit keamanan informasi.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.4)',NULL,0,'Kebijakan instansi yang memperbolehkan pelibatan ASN dari unit kerja lain yang memiliki kompetensi audit TIK dan/atau audit keamanan informasi.','Bukti pelibatan: surat penugasan ASN dari unit kerja lain dalam tim audit, daftar tim audit beserta unit asal dan kompetensinya.','Laporan kontribusi ASN lintas-unit dalam pelaksanaan audit; bukti penguatan kapasitas tim audit melalui pelibatan ASN lintas-unit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(12,'TK-12','tk',12,NULL,'Pertanyaan TK-12','Kompetensi audit dibuktikan dengan sertifikat pelatihan audit TIK dan/atau audit keamanan informasi.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.5)',NULL,0,'Kebijakan instansi yang mensyaratkan kompetensi auditor dibuktikan dengan sertifikat pelatihan audit TIK dan/atau audit keamanan informasi.','Bukti kompetensi: sertifikat pelatihan auditor yang masih berlaku, daftar auditor beserta kualifikasinya, dokumentasi penugasan sesuai kompetensi.','Daftar auditor beserta status kualifikasinya; bukti pemutakhiran kompetensi auditor secara berkala (refreshment training).','2026-05-11 08:33:00','2026-05-26 03:26:04'),(13,'TK-13','tk',13,NULL,'Pertanyaan TK-13','Pelatihan audit diselenggarakan oleh BSSN atau lembaga pelatihan lain yang mendapat pengakuan dari BSSN.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.6)',NULL,0,'Kebijakan instansi yang mensyaratkan pelatihan auditor diselenggarakan oleh BSSN atau lembaga yang diakui BSSN.','Bukti penyelenggara pelatihan: sertifikat pelatihan yang menyebut BSSN atau lembaga ter-akreditasi BSSN sebagai penyelenggara.','Daftar sertifikat auditor beserta penyelenggaranya; bukti verifikasi keabsahan lembaga penyelenggara pelatihan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(14,'TK-14','tk',14,NULL,'Pertanyaan TK-14','Audit Keamanan [Objek Audit] internal mengacu pada kebijakan Instansi Pusat atau Pemerintah Daerah dengan objek dan kriteria Audit Keamanan SPBE yang sesuai dengan peraturan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.7)',NULL,0,'Kebijakan instansi tentang audit yang menetapkan objek dan kriteria audit sesuai dengan peraturan perundang-undangan.','Bukti acuan audit: dokumen kebijakan/pedoman audit instansi, daftar objek audit, kriteria audit (mis. STPK Keamanan SPBE) yang dirujuk dalam pelaksanaan audit.','Bukti pembaruan kebijakan audit mengikuti perubahan regulasi; laporan kesesuaian pelaksanaan audit dengan kebijakan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(15,'TK-15','tk',15,NULL,'Pertanyaan TK-15','Audit Keamanan [Objek Audit] internal dilaksanakan sebelum pelaksanaan Audit Keamanan SPBE oleh LATIK pemerintah atau LATIK Terakreditasi yang terdaftar.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.8)',NULL,0,'Kebijakan instansi yang menetapkan urutan pelaksanaan audit: audit internal dilaksanakan terlebih dahulu sebelum audit oleh LATIK (Lembaga Audit Teknologi Informasi dan Komunikasi).','Bukti urutan pelaksanaan: jadwal audit internal yang mendahului jadwal audit LATIK, LHAK internal yang dijadikan bahan audit LATIK.','Laporan pemanfaatan hasil audit internal sebagai bahan persiapan audit LATIK; bukti perbaikan yang dilakukan sebelum audit LATIK.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(16,'TK-16','tk',16,NULL,'Pertanyaan TK-16','Prosedur pelaksanaan Audit Keamanan [objek audit] internal paling sedikit meliputi tahapan pemahaman kontrol keamanan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.9.1)',NULL,0,'Metodologi audit yang menetapkan tahap pemahaman kontrol keamanan oleh auditi sebagai bagian dari prosedur audit.','Bukti pelaksanaan tahap pemahaman kontrol: kertas kerja yang berisi penilaian pemahaman kontrol keamanan, hasil wawancara dengan auditi, hasil observasi.','Hasil survei pemahaman auditi terhadap kontrol keamanan; laporan rekomendasi peningkatan pemahaman dan tindak lanjutnya.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(17,'TK-17','tk',17,NULL,'Pertanyaan TK-17','Prosedur pelaksanaan Audit Keamanan [objek audit] internal paling sedikit meliputi tahapan evaluasi desain kontrol keamanan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.9.2)',NULL,0,'Metodologi audit yang menetapkan pelaksanaan Evaluasi Desain Kontrol (EDK) dengan acuan/kriteria yang ditetapkan.','Bukti pelaksanaan EDK: kertas kerja EDK yang terisi, daftar acuan EDK per kontrol, status EDK (Memadai/Perlu Peningkatan/Tidak Memadai).','Laporan hasil EDK beserta tindak lanjut atas kontrol yang berstatus belum memadai.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(18,'TK-18','tk',18,NULL,'Pertanyaan TK-18','Prosedur pelaksanaan Audit Keamanan [objek audit] internal paling sedikit meliputi tahapan evaluasi implementasi kontrol keamanan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.9.3)',NULL,0,'Metodologi audit yang menetapkan pelaksanaan Evaluasi Implementasi Kontrol (EIK) untuk menilai apakah desain kontrol benar-benar diterapkan.','Bukti pelaksanaan EIK: kertas kerja EIK yang terisi, bukti dukung implementasi kontrol, status EIK (Sesuai/Tidak Sesuai Desain).','Laporan hasil EIK beserta tindak lanjut atas kontrol yang tidak diimplementasikan sesuai desain.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(19,'TK-19','tk',19,NULL,'Pertanyaan TK-19','Prosedur pelaksanaan Audit Keamanan [objek audit] internal paling sedikit meliputi tahapan evaluasi efektivitas kontrol keamanan.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.9.4)',NULL,0,'Metodologi audit yang menetapkan pelaksanaan Evaluasi Efektivitas Kontrol (EFK) untuk menilai apakah kontrol yang diterapkan benar-benar efektif mencapai tujuan keamanan.','Bukti pelaksanaan EFK: kertas kerja EFK yang terisi, indikator kinerja kontrol, status EFK (Efektif/Perlu Peningkatan/Belum Efektif).','Laporan hasil EFK beserta tindak lanjut atas kontrol yang belum efektif; perbandingan hasil EFK antar-audit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(20,'TK-20','tk',20,NULL,'Pertanyaan TK-20','Audit Keamanan [Objek Audit] internal harus dituangkan dalam Peta Rencana SPBE pada Instansi Pusat dan Pemerintah Daerah.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.10)',NULL,0,'Kebijakan instansi yang mensyaratkan rencana audit Keamanan SPBE dituangkan dalam Peta Rencana SPBE.','Bukti integrasi: dokumen Peta Rencana SPBE yang memuat rencana audit Keamanan SPBE, jadwal terintegrasi dengan kegiatan SPBE lainnya.','Bukti pembaruan Peta Rencana SPBE secara berkala; laporan kesesuaian pelaksanaan audit dengan jadwal di Peta Rencana SPBE.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(21,'TK-21','tk',21,NULL,'Pertanyaan TK-21','Pelaksanaan Audit Keamanan [objek audit] internal wajib mempertimbangkan (atau memperhatikan) Aplikasi SPBE dan/atau Infrastruktur SPBE yang dimiliki Instansi Pusat dan Pemerintah Daerah.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.11.1)',NULL,0,'Kebijakan instansi yang menetapkan Aplikasi SPBE dan/atau Infrastruktur SPBE sebagai dasar pertimbangan pelaksanaan audit.','Bukti penetapan: daftar Aplikasi SPBE dan Infrastruktur SPBE yang dimiliki, daftar objek audit yang ditetapkan berdasarkan inventarisasi tersebut.','Bukti pembaruan daftar objek audit mengikuti perubahan aset; laporan cakupan audit terhadap total aset yang dimiliki.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(22,'TK-22','tk',22,NULL,'Pertanyaan TK-22','Pelaksanaan Audit Keamanan [objek audit] internal wajib mempertimbangkan (atau memperhatikan) risiko pada Aplikasi SPBE dan/atau Infrastruktur SPBE.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.11.2)',NULL,0,'Kebijakan instansi yang menetapkan tingkat risiko sebagai salah satu pertimbangan pelaksanaan audit (risk-based audit selection).','Bukti pertimbangan berbasis risiko: risk register Aplikasi/Infrastruktur SPBE, mapping tingkat risiko terhadap prioritas audit.','Laporan pelaksanaan audit pada aset berisiko tinggi/kritikal; bukti penurunan risiko residual pada aset yang sudah diaudit.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(23,'TK-23','tk',23,NULL,'Pertanyaan TK-23','Pelaksanaan Audit Keamanan [objek audit] internal wajib mempertimbangkan (atau memperhatikan) kategori Sistem Elektronik.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.11.3)',NULL,0,'Kebijakan instansi yang menetapkan kategori Sistem Elektronik (Strategis/Tinggi/Rendah, sesuai UU ITE & PP PSTE) sebagai pertimbangan pelaksanaan audit.','Bukti kategorisasi: dokumen penetapan kategori Sistem Elektronik per aset, mapping kategori terhadap prioritas audit.','Laporan pelaksanaan audit pada Sistem Elektronik kategori Strategis/Tinggi; bukti pembaruan kategorisasi SE sesuai perubahan layanan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(24,'TK-24','tk',24,NULL,'Pertanyaan TK-24','Pelaksanaan Audit Keamanan [objek audit] internal wajib mempertimbangkan (atau memperhatikan) tingkat vitalitas Sistem Elektronik.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.11.4)',NULL,0,'Kebijakan instansi yang menetapkan tingkat vitalitas Sistem Elektronik sebagai pertimbangan pelaksanaan audit.','Bukti pertimbangan: dokumen penilaian vitalitas SE, mapping tingkat vitalitas terhadap prioritas audit.','Laporan pelaksanaan audit pada SE dengan tingkat vitalitas tinggi; bukti tinjauan ulang tingkat vitalitas secara berkala.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(25,'TK-25','tk',25,NULL,'Pertanyaan TK-25','Pelaksanaan Audit Keamanan [objek audit] internal wajib mempertimbangkan (atau memperhatikan) Audit Keamanan [Objek Audit] yang sudah dilaksanakan sebelumnya.','Kepka BSSN Nomor 60 Tahun 2026 (TK.2 EDK 3.1.11.5)',NULL,0,'Kebijakan instansi yang menetapkan riwayat audit sebelumnya sebagai salah satu pertimbangan pelaksanaan audit.','Bukti penggunaan riwayat audit: daftar Objek Audit beserta riwayat audit sebelumnya, justifikasi pelaksanaan berdasarkan riwayat.','Laporan pelaksanaan follow-up audit pada Objek Audit dengan temuan signifikan; bukti pemanfaatan LHAK sebelumnya sebagai input audit baru.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(26,'MK-1','mk',1,NULL,'Pertanyaan MK-1','Manajemen risiko keamanan sesuai peraturan perundangan tentang manajemen risiko.','*(tidak diatur eksplisit di Peraturan BSSN Nomor 4 Tahun 2021)*',NULL,0,'Kebijakan/pedoman manajemen risiko keamanan yang ditetapkan instansi, mengacu pada kerangka manajemen risiko yang berlaku.','Bukti penerapan proses manajemen risiko: dokumen identifikasi, analisis, evaluasi, dan perlakuan risiko keamanan; risk register yang dipelihara.','Hasil tinjauan periodik atas penerapan manajemen risiko keamanan; bukti pembaruan risk register dan tindak lanjut atas risiko yang teridentifikasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(27,'MK-2','mk',2,NULL,'Pertanyaan MK-2','Pendefinisian isu internal keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 4 ayat (2) huruf a; ayat (3) & (4)',NULL,0,'Dokumen ruang lingkup SMKI yang memuat isu internal mencakup minimum 4 area: data dan informasi SPBE, Aplikasi SPBE, aset Infrastruktur SPBE, dan kebijakan keamanan informasi SPBE.','Bukti identifikasi isu internal: notulen rapat penetapan ruang lingkup, daftar isu internal, dokumentasi kegiatan terkait isu internal.','Hasil survei internal organisasi mengenai pengetahuan dan pemahaman terhadap ruang lingkup SMKI; bukti pembaruan daftar isu internal mengikuti perubahan organisasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(28,'MK-3','mk',3,NULL,'Pertanyaan MK-3','Pendefinisian isu eksternal keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 4 ayat (2) huruf b; ayat (5)',NULL,0,'Dokumen ruang lingkup SMKI yang memuat isu eksternal keamanan informasi SPBE sesuai ketentuan peraturan perundang-undangan.','Bukti analisis isu eksternal: lanskap ancaman terkini, kepatuhan regulasi, tren keamanan siber yang relevan, dokumentasi peninjauan periodik.','Hasil tinjauan periodik atas isu eksternal yang teridentifikasi; bukti penyesuaian program keamanan mengikuti perubahan lanskap ancaman atau regulasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(29,'MK-4','mk',4,NULL,'Pertanyaan MK-4','Penetapan penanggung jawab dilaksanakan oleh pimpinan Instansi Pusat dan kepala daerah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 5 ayat (1)',NULL,0,'Peraturan/Keputusan Kepala Instansi Pusat atau Kepala Daerah yang menetapkan penanggung jawab Keamanan SPBE, ditandatangani oleh pimpinan.','Bukti penetapan: salinan SK/Perka yang sah, dokumentasi pelantikan/penyerahan tugas, surat penugasan.','Bukti pelaksanaan tugas oleh penanggung jawab yang ditetapkan; laporan pertanggungjawaban kinerja kepada pimpinan; dokumen pembaruan SK mengikuti perubahan struktural.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(30,'MK-5','mk',5,NULL,'Pertanyaan MK-5','Penanggung jawab dijabat oleh sekretaris Instansi Pusat dan sekretaris daerah pada Pemerintah Daerah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 5 ayat (2) & (3)',NULL,0,'SK/Perka yang menetapkan Sekretaris Instansi/Sekretaris Daerah sebagai penanggung jawab Keamanan SPBE (Koordinator SPBE).','Bukti pelaksanaan tugas oleh Sekretaris/Koordinator SPBE: notulen rapat koordinasi, laporan kegiatan, surat penugasan turunan.','Notulen rapat koordinasi Keamanan SPBE yang dipimpin Koordinator SPBE; bukti keputusan strategis Keamanan SPBE yang ditetapkan; bukti dukungan operasional yang diberikan kepada pelaksana teknis.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(31,'MK-6','mk',6,NULL,'Pertanyaan MK-6','Penetapan pelaksana teknis manajemen keamanan dilaksanakan oleh penanggung jawab.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 6 ayat (1)',NULL,0,'SK Koordinator SPBE tentang penetapan pelaksana teknis Keamanan SPBE.','Dokumen penetapan pelaksana teknis beserta uraian tugas yang ditandatangani Koordinator SPBE.','Laporan pelaksanaan tugas operasional Keamanan SPBE oleh pelaksana teknis; bukti pelaporan rutin pelaksana teknis kepada Koordinator SPBE.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(32,'MK-7','mk',7,NULL,'Pertanyaan MK-7','Pejabat pimpinan tinggi pratama yang melaksanakan tugas dan fungsi di bidang keamanan TIK pada Instansi Pusat dan Pemerintah Daerah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 6 ayat (2) huruf a',NULL,0,'SK penetapan PPT Pratama (Pejabat Pimpinan Tinggi Pratama / Eselon II) bidang keamanan TIK sebagai pelaksana teknis Keamanan SPBE.','Bukti PPT Pratama yang menduduki jabatan tersebut beserta uraian tugas dan tanggung jawabnya.','Laporan pelaksanaan tugas keamanan TIK oleh PPT Pratama; bukti koordinasi dengan Penanggung Jawab Aplikasi SPBE; laporan kinerja kepada Koordinator SPBE.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(33,'MK-8','mk',8,NULL,'Pertanyaan MK-8','Pejabat pimpinan tinggi atau pejabat administrator yang membawahi, membangun, memelihara, dan/atau mengembangkan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 6 ayat (2) huruf b',NULL,0,'SK penetapan PPT atau pejabat administrator sebagai penanggung jawab Aplikasi SPBE/Objek Audit.','Bukti penetapan PJ Aplikasi beserta uraian tugas dan tanggung jawabnya.','Laporan pengelolaan pengembangan/pemeliharaan Aplikasi SPBE; bukti koordinasi dengan PPT Pratama Keamanan TIK; laporan penerapan STPK pada aplikasi yang diampu.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(34,'MK-9','mk',9,NULL,'Pertanyaan MK-9','Memastikan penerapan standar teknis dan prosedur keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (1) huruf a',NULL,0,'Uraian tugas PPT Pratama Keamanan TIK yang memuat kewajiban memastikan penerapan STPK Keamanan SPBE.','Bukti pelaksanaan tugas: dokumen STPK yang diterapkan, laporan pemantauan kepatuhan terhadap STPK.','Laporan hasil pemantauan kepatuhan terhadap STPK; bukti tindak lanjut atas ketidakpatuhan yang ditemukan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(35,'MK-10','mk',10,NULL,'Pertanyaan MK-10','Merumuskan, mengoordinasikan, dan melaksanakan program kerja dan anggaran keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (1) huruf b',NULL,0,'Uraian tugas PPT Pratama Keamanan TIK yang memuat kewajiban merumuskan, mengoordinasikan, dan melaksanakan program kerja & anggaran keamanan.','Dokumen program kerja Keamanan SPBE yang disusun, notulen rapat koordinasi, laporan pelaksanaan program kerja.','Laporan realisasi program kerja Keamanan SPBE; laporan realisasi anggaran keamanan; bukti koordinasi lintas unit dalam pelaksanaan program.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(36,'MK-11','mk',11,NULL,'Pertanyaan MK-11','Melaporkan pelaksanaan MKI SPBE dan penerapan STPK kepada koordinator SPBE.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (1) huruf c',NULL,0,'Uraian tugas PPT Pratama Keamanan TIK yang memuat kewajiban pelaporan kepada Koordinator SPBE.','Bukti laporan pelaksanaan MKI dan STPK yang disampaikan kepada Koordinator SPBE (nota dinas, laporan periodik).','Notulen rapat pembahasan laporan oleh Koordinator SPBE; bukti tindak lanjut atas arahan Koordinator yang dihasilkan dari laporan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(37,'MK-12','mk',12,NULL,'Pertanyaan MK-12','Menerapkan STPK [Objek Audit] di unit kerja masing-masing.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (2) huruf a',NULL,0,'Uraian tugas PJ Aplikasi yang memuat kewajiban menerapkan STPK di unit kerja masing-masing.','Bukti penerapan STPK pada aplikasi/unit kerja: dokumen konfigurasi, hasil hardening, log penerapan kontrol keamanan.','Laporan hasil verifikasi penerapan STPK; bukti tindak lanjut atas temuan ketidaksesuaian penerapan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(38,'MK-13','mk',13,NULL,'Pertanyaan MK-13','Memastikan seluruh pembangunan/pengembangan [Objek Audit] oleh pihak ketiga memenuhi STPK yang ditetapkan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (2) huruf b',NULL,0,'Klausul kepatuhan STPK dalam dokumen pengadaan (KAK, SLA, kontrak) dengan pihak ketiga.','Bukti verifikasi kepatuhan STPK pada deliverable pihak ketiga (laporan reviu, hasil uji penerimaan, audit vendor).','Laporan hasil uji penerimaan deliverable pihak ketiga; bukti enforcement klausul kontrak jika terjadi pelanggaran.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(39,'MK-14','mk',14,NULL,'Pertanyaan MK-14','Memastikan keberlangsungan proses bisnis [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (2) huruf c',NULL,0,'Dokumen rencana keberlangsungan bisnis (BCP/DRP) untuk Aplikasi SPBE/Objek Audit.','Bukti pelaksanaan BCP/DRP: hasil uji coba, mekanisme failover, laporan ketersediaan layanan.','Laporan hasil uji coba BCP/DRP secara berkala; laporan ketersediaan (uptime) layanan Aplikasi SPBE; dokumentasi pemulihan saat terjadi gangguan.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(40,'MK-15','mk',15,NULL,'Pertanyaan MK-15','Berkoordinasi dengan PPT pratama bidang keamanan TIK terkait perumusan program kerja dan anggaran keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 7 ayat (2) huruf d',NULL,0,'Uraian tugas PJ Aplikasi yang memuat kewajiban berkoordinasi dengan PPT Pratama Keamanan TIK.','Bukti koordinasi: notulen rapat bersama, dokumen rencana terintegrasi, korespondensi program/anggaran.','Notulen rapat koordinasi yang dilaksanakan; bukti penyelesaian isu lintas-unit melalui koordinasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(41,'MK-16','mk',16,NULL,'Pertanyaan MK-16','Perencanaan keamanan dilakukan oleh pelaksana teknis manajemen keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 8 ayat (1)',NULL,0,'Kebijakan/SOP yang menetapkan bahwa perencanaan Keamanan SPBE dilakukan oleh pelaksana teknis (PPT Pratama Keamanan TIK).','Bukti pelaksanaan perencanaan oleh pelaksana teknis: dokumen rencana yang ditandatangani pelaksana teknis, notulen rapat perencanaan.','Dokumen rencana Keamanan SPBE yang telah disetujui Koordinator SPBE; notulen rapat pembahasan rencana.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(42,'MK-17','mk',17,NULL,'Pertanyaan MK-17','Program kerja manajemen keamanan [Objek Audit] berdasarkan kategori risiko keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 8 ayat (2) huruf a & ayat (4)',NULL,0,'Dokumen Program Kerja Keamanan SPBE yang disusun berdasarkan kategori risiko sesuai peraturan perundang-undangan.','Bukti penyusunan program kerja: hasil penilaian risiko sebagai input, dokumen program kerja yang disahkan.','Laporan realisasi program kerja terhadap target; bukti pembaruan program kerja mengikuti perubahan risiko.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(43,'MK-18','mk',18,NULL,'Pertanyaan MK-18','Target realisasi program kerja keamanan [Objek Audit] ditetapkan berdasarkan kebutuhan instansi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 8 ayat (2) huruf b & ayat (5)',NULL,0,'Dokumen target realisasi yang ditetapkan berdasarkan kebutuhan spesifik instansi dan disahkan Koordinator SPBE.','Bukti penetapan target realisasi: dokumen target yang dilegalisasi, justifikasi kebutuhan instansi.','Laporan capaian target realisasi pada periode pelaporan; bukti tindak lanjut atas target yang tidak tercapai.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(44,'MK-19','mk',19,NULL,'Pertanyaan MK-19','Sosialisasi (edukasi kesadaran Keamanan SPBE).','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 9 huruf a',NULL,0,'Dokumen rencana program sosialisasi Keamanan SPBE (kalender sosialisasi, materi, sasaran peserta).','Bukti pelaksanaan sosialisasi: daftar hadir, materi, dokumentasi kegiatan (foto, notulen), evaluasi peserta.','Hasil survei internal organisasi mengenai pengetahuan dan pemahaman peserta terhadap materi sosialisasi Keamanan SPBE; laporan rekapitulasi kegiatan sosialisasi.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(45,'MK-20','mk',20,NULL,'Pertanyaan MK-20','Pelatihan (edukasi kesadaran Keamanan SPBE).','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 9 huruf b',NULL,0,'Dokumen rencana pelatihan Keamanan SPBE (kurikulum, jadwal, sasaran kompetensi).','Bukti pelaksanaan pelatihan: daftar hadir, sertifikat, materi pelatihan, evaluasi peserta dan instruktur.','Hasil evaluasi pelatihan oleh peserta dan instruktur; laporan tindak lanjut peningkatan kompetensi SDM Keamanan SPBE.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(46,'MK-21','mk',21,NULL,'Pertanyaan MK-21','Menginventarisasi seluruh aset terkait [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 10 huruf a',NULL,0,'Kebijakan/SOP manajemen aset SPBE dan template Daftar Aset.','Daftar Aset SPBE yang terdokumentasi, mencakup data dan informasi, aplikasi, dan infrastruktur; ditinjau berkala.','Laporan hasil verifikasi Daftar Aset terhadap kondisi lapangan; bukti pembaruan Daftar Aset mengikuti perubahan aset.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(47,'MK-22','mk',22,NULL,'Pertanyaan MK-22','Mengidentifikasi kerentanan dan ancaman terhadap [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 10 huruf b',NULL,0,'SOP penilaian kerentanan dan ancaman terhadap aset SPBE.','Laporan hasil identifikasi kerentanan: vulnerability scan report, threat assessment, daftar kerentanan teridentifikasi.','Laporan tindak lanjut penanganan kerentanan kritikal yang teridentifikasi; dokumentasi pembaruan threat assessment secara berkala.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(48,'MK-23','mk',23,NULL,'Pertanyaan MK-23','Mengukur tingkat risiko keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 10 huruf c',NULL,0,'Metodologi pengukuran tingkat risiko keamanan (kriteria likelihood × impact, threshold).','Laporan pengukuran tingkat risiko: risk register dengan tingkat risiko terkuantifikasi, peta risiko.','Laporan perbandingan tingkat risiko sebelum dan sesudah perlakuan risiko; bukti tinjauan periodik atas peta risiko.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(49,'MK-24','mk',24,NULL,'Pertanyaan MK-24','Menerapkan STPK [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 11 ayat (2) huruf a',NULL,0,'Dokumen STPK yang ditetapkan instansi sebagai acuan penerapan.','Bukti penerapan STPK: konfigurasi sistem yang sesuai, dokumen hardening, hasil reviu kepatuhan STPK.','Laporan hasil audit kepatuhan terhadap STPK; bukti tindak lanjut atas temuan ketidaksesuaian.','2026-05-11 08:33:00','2026-05-26 03:26:04'),(50,'MK-25','mk',25,NULL,'Pertanyaan MK-25','Menguji fungsi keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 11 ayat (2) huruf b',NULL,0,'Rencana dan SOP uji fungsi keamanan terhadap Aplikasi SPBE dan Infrastruktur SPBE.','Laporan hasil uji fungsi keamanan (penetration test, vulnerability assessment, security testing report).','Laporan tindak lanjut perbaikan atas temuan uji fungsi keamanan; perbandingan hasil pengujian antar-periode.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(51,'MK-26','mk',26,NULL,'Pertanyaan MK-26','Mengidentifikasi sumber serangan atas [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 12 huruf a',NULL,0,'SOP penanganan insiden yang memuat tahap identifikasi sumber serangan.','Laporan insiden yang memuat identifikasi sumber serangan (IP, vektor, threat actor jika diketahui).','Laporan rekapitulasi insiden beserta hasil identifikasi sumber serangan; dokumentasi lesson learned.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(52,'MK-27','mk',27,NULL,'Pertanyaan MK-27','Menganalisis informasi yang berkaitan dengan insiden atas [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 12 huruf b',NULL,0,'SOP analisis insiden yang memuat metodologi pengumpulan dan analisis bukti.','Laporan analisis insiden: timeline, indikator kompromi (IoC), dampak teknis dan bisnis.','Dokumentasi lesson learned dari analisis insiden; laporan tindak lanjut pencegahan insiden serupa.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(53,'MK-28','mk',28,NULL,'Pertanyaan MK-28','Memprioritaskan penanganan insiden atas [Objek Audit] berdasarkan tingkat dampak.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 12 huruf c',NULL,0,'SOP klasifikasi insiden berdasarkan tingkat dampak (severity matrix).','Bukti klasifikasi insiden dalam laporan: tier/level severity, justifikasi prioritas penanganan.','Laporan rekapitulasi insiden berdasarkan severity; bukti kesesuaian alokasi sumber daya dengan tingkat severity.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(54,'MK-29','mk',29,NULL,'Pertanyaan MK-29','Mendokumentasi bukti insiden atas [Objek Audit] yang terjadi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 12 huruf d',NULL,0,'SOP dokumentasi insiden dan rantai bukti (chain of custody).','Dokumentasi insiden: log, screenshot, artifact forensik, laporan insiden ter-arsip.','Dokumentasi insiden yang dapat diakses untuk reviu pasca-insiden; bukti pemanfaatan dokumentasi sebagai bahan pembelajaran.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(55,'MK-30','mk',30,NULL,'Pertanyaan MK-30','Memitigasi atau mengurangi dampak risiko keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 12 huruf e',NULL,0,'SOP mitigasi/containment insiden Keamanan SPBE.','Bukti tindakan mitigasi: laporan containment, patch yang diterapkan, kontrol kompensasi.','Laporan hasil pelaksanaan mitigasi; dokumentasi pemulihan layanan setelah mitigasi; tindak lanjut pencegahan insiden berulang.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(56,'MK-31','mk',31,NULL,'Pertanyaan MK-31','Audit Keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 13',NULL,0,'Rencana audit Keamanan SPBE yang ditetapkan sesuai peraturan perundang-undangan.','Bukti pelaksanaan audit: Laporan Hasil Audit Keamanan (LHAK), laporan audit internal, nota dinas penyampaian hasil audit.','Laporan tindak lanjut atas temuan audit; bukti perbaikan kontrol keamanan berdasarkan rekomendasi audit.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(57,'MK-32','mk',32,NULL,'Pertanyaan MK-32','Pengembangan desain kendali keamanan [Objek Audit] sesuai manajemen risiko keamanan.','*(tidak diatur eksplisit di Peraturan BSSN Nomor 4 Tahun 2021)*',NULL,0,'Kebijakan/pedoman desain kendali keamanan berbasis hasil penilaian risiko.','Bukti desain kendali keamanan yang diturunkan dari risk register: peta kendali, dokumen arsitektur keamanan, dokumen mitigasi per risiko.','Laporan hasil uji efektivitas desain kendali sebelum produksi; bukti tindak lanjut atas desain kendali yang tidak efektif.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(58,'MK-33','mk',33,NULL,'Pertanyaan MK-33','Pengembangan desain kendali keamanan [Objek Audit] memenuhi persyaratan kepatuhan kepada peraturan perundangan keamanan.','*(tidak diatur eksplisit di Peraturan BSSN Nomor 4 Tahun 2021)*',NULL,0,'Daftar persyaratan kepatuhan (compliance matrix) terhadap peraturan perundang-undangan keamanan; desain kendali dipetakan terhadap daftar tersebut.','Bukti pemenuhan kepatuhan: gap analysis report, compliance attestation, dokumen mapping kontrol-ke-regulasi.','Laporan tindak lanjut atas gap kepatuhan yang teridentifikasi; bukti pembaruan compliance matrix mengikuti perubahan regulasi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(59,'MK-34','mk',34,NULL,'Pertanyaan MK-34','Pengoperasian keamanan sesuai peraturan perundangan tentang STPK.','*(tidak diatur eksplisit di Peraturan BSSN Nomor 4 Tahun 2021)*',NULL,0,'SOP/Runbook pengoperasian keamanan yang mengacu pada STPK.','Bukti pengoperasian sesuai STPK: log operasional, hasil monitoring, dokumen prosedur operasional yang diikuti.','Laporan hasil audit kepatuhan operasional terhadap SOP/STPK; bukti tindak lanjut atas penyimpangan operasional.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(60,'MK-35','mk',35,NULL,'Pertanyaan MK-35','Dukungan pengoperasian dilakukan oleh koordinator SPBE.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 14 ayat (1)',NULL,0,'Kebijakan instansi yang menetapkan Koordinator SPBE bertanggung jawab atas dukungan pengoperasian Keamanan SPBE.','Bukti dukungan Koordinator SPBE: surat penugasan dukungan, alokasi sumber daya, persetujuan program.','Bukti pemenuhan permintaan dukungan dari pelaksana teknis; notulen rapat keputusan strategis dukungan operasional.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(61,'MK-36','mk',36,NULL,'Pertanyaan MK-36','Pelatihan dan/atau sertifikasi kompetensi keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 14 ayat (3) & ayat (4) huruf a',NULL,0,'Rencana pelatihan dan sertifikasi yang memuat kompetensi keamanan infrastruktur TIK dan keamanan aplikasi.','Bukti pelaksanaan: daftar peserta, sertifikat, evaluasi pelatihan, anggaran pelatihan/sertifikasi.','Laporan pemenuhan matriks kompetensi SDM Keamanan SPBE setelah pelatihan/sertifikasi; bukti penerapan hasil pelatihan dalam tugas operasional.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(62,'MK-37','mk',37,NULL,'Pertanyaan MK-37','Bimbingan teknis mengenai standar keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 14 ayat (4) huruf b',NULL,0,'Rencana bimbingan teknis standar Keamanan SPBE.','Bukti pelaksanaan bimtek: daftar hadir, materi, dokumentasi, evaluasi peserta.','Hasil survei pemahaman peserta terhadap standar keamanan setelah bimtek; bukti penerapan standar pasca-bimtek.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(63,'MK-38','mk',38,NULL,'Pertanyaan MK-38','Anggaran keamanan [Objek Audit] disusun berdasarkan perencanaan yang ditetapkan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 14 ayat (5)',NULL,0,'Dokumen pengajuan anggaran Keamanan SPBE yang selaras dengan rencana program kerja.','Bukti penyusunan anggaran: RKA-KL/RKA-PD, kertas kerja anggaran, persetujuan anggaran.','Laporan realisasi anggaran terhadap pengajuan; laporan audit anggaran (bila ada).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(64,'MK-39','mk',39,NULL,'Pertanyaan MK-39','Evaluasi kinerja dilakukan oleh koordinator SPBE.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (1)',NULL,0,'Kebijakan instansi yang menetapkan Koordinator SPBE sebagai pelaksana evaluasi kinerja Keamanan SPBE.','Bukti pelaksanaan evaluasi oleh Koordinator: notulen rapat evaluasi, laporan evaluasi yang ditandatangani Koordinator.','Laporan tindak lanjut atas hasil evaluasi oleh Koordinator; bukti penyampaian hasil evaluasi kepada pemangku kepentingan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(65,'MK-40','mk',40,NULL,'Pertanyaan MK-40','Evaluasi kinerja dilakukan terhadap pelaksanaan keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (2)',NULL,0,'Lingkup evaluasi yang ditetapkan: mencakup seluruh aspek pelaksanaan Keamanan SPBE pada Objek Audit.','Bukti cakupan evaluasi: laporan evaluasi yang mencakup seluruh aspek pelaksanaan keamanan.','Laporan rekomendasi perluasan cakupan evaluasi untuk area yang belum tercakup; bukti tinjauan cakupan antar-periode.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(66,'MK-41','mk',41,NULL,'Pertanyaan MK-41','Mengidentifikasi area proses yang memiliki risiko tinggi terhadap keberhasilan pelaksanaan keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (3) huruf a',NULL,0,'Metodologi identifikasi area proses berisiko tinggi.','Daftar area proses berisiko tinggi yang teridentifikasi dengan justifikasinya.','Laporan tindak lanjut atas area berisiko tinggi yang teridentifikasi; bukti pembaruan daftar area berisiko.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(67,'MK-42','mk',42,NULL,'Pertanyaan MK-42','Menetapkan indikator kinerja pada setiap area proses keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (3) huruf b',NULL,0,'Daftar indikator kinerja Keamanan SPBE per area proses yang ditetapkan formal.','Bukti penetapan dan pengukuran indikator: dashboard kinerja, laporan pengukuran berkala.','Laporan capaian indikator kinerja terhadap target; bukti penggunaan hasil pengukuran sebagai dasar pengambilan keputusan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(68,'MK-43','mk',43,NULL,'Pertanyaan MK-43','Memformulasi pelaksanaan keamanan [Objek Audit] dengan mengukur kinerja secara kuantitatif.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (3) huruf c',NULL,0,'Metodologi pengukuran kuantitatif kinerja Keamanan SPBE.','Bukti pengukuran kuantitatif: laporan data kinerja numerik dengan dukungan sumber data.','Laporan tren kinerja antar-periode; bukti tindak lanjut atas penyimpangan dari target.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(69,'MK-44','mk',44,NULL,'Pertanyaan MK-44','Menganalisis efektivitas pelaksanaan keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (3) huruf d',NULL,0,'Metodologi analisis efektivitas pelaksanaan keamanan.','Laporan analisis efektivitas: penilaian pencapaian, identifikasi penyebab kesenjangan, rekomendasi perbaikan.','Laporan realisasi rekomendasi hasil analisis efektivitas; bukti perbaikan kondisi setelah implementasi rekomendasi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(70,'MK-45','mk',45,NULL,'Pertanyaan MK-45','Mendukung dan merealisasikan program audit keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (3) huruf e',NULL,0,'Komitmen dan alokasi sumber daya untuk pelaksanaan program audit Keamanan SPBE.','Bukti dukungan program audit: alokasi anggaran audit, persetujuan rencana audit, fasilitasi tim audit.','Laporan realisasi program audit; bukti tindak lanjut hasil audit oleh manajemen.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(71,'MK-46','mk',46,NULL,'Pertanyaan MK-46','Evaluasi kinerja dilaksanakan paling sedikit 1 (satu) kali dalam 1 (satu) tahun.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 15 ayat (4)',NULL,0,'Kebijakan instansi yang menetapkan frekuensi evaluasi kinerja minimum 1 (satu) kali per tahun.','Bukti pelaksanaan evaluasi: laporan evaluasi tahunan beserta tanggal pelaksanaannya.','Laporan evaluasi tahunan yang dilegalisasi; bukti evaluasi tambahan insidental (jika ada).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(72,'MK-47','mk',47,NULL,'Pertanyaan MK-47','Perbaikan berkelanjutan dilakukan oleh pelaksana teknis keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 16 ayat (1)',NULL,0,'Kebijakan instansi yang menetapkan pelaksana teknis keamanan sebagai penanggung jawab perbaikan berkelanjutan.','Bukti pelaksanaan oleh pelaksana teknis: dokumen rencana perbaikan yang disusun pelaksana teknis, laporan progres.','Laporan progres pelaksanaan perbaikan kepada Koordinator SPBE; bukti penyelesaian rencana perbaikan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(73,'MK-48','mk',48,NULL,'Pertanyaan MK-48','Perbaikan berkelanjutan merupakan tindak lanjut dari hasil evaluasi kinerja keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 16 ayat (2)',NULL,0,'Mekanisme tindak lanjut: rencana perbaikan yang diturunkan dari hasil evaluasi kinerja.','Bukti traceability: pemetaan rencana perbaikan terhadap temuan/rekomendasi evaluasi kinerja.','Laporan status tindak lanjut atas rekomendasi evaluasi; bukti penyelesaian rekomendasi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(74,'MK-49','mk',49,NULL,'Pertanyaan MK-49','Mengatasi permasalahan dalam pelaksanaan keamanan [Objek Audit].','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 16 ayat (3) huruf a',NULL,0,'SOP penanganan permasalahan dalam pelaksanaan Keamanan SPBE.','Bukti penanganan permasalahan: daftar permasalahan, akar penyebab, rencana solusi, status penyelesaian.','Laporan penyelesaian permasalahan; bukti pencegahan permasalahan berulang.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(75,'MK-50','mk',50,NULL,'Pertanyaan MK-50','Memperbaiki pelaksanaan keamanan [Objek Audit] secara periodik.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 16 ayat (3) huruf b',NULL,0,'Siklus perbaikan periodik yang ditetapkan (frekuensi, mekanisme, output).','Bukti pelaksanaan perbaikan periodik: laporan perbaikan per siklus, perbandingan kondisi antar-periode.','Laporan tinjauan manajemen atas hasil perbaikan periodik; bukti perbandingan kondisi keamanan antar-periode.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(76,'FK-1','fk',1,NULL,'Pertanyaan FK-1','Menggunakan manajemen kata sandi untuk proses autentikasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf a',NULL,0,'Dokumen Standar Teknis dan Prosedur Keamanan (STPK) yang ditetapkan instansi, memuat penggunaan manajemen kata sandi untuk proses autentikasi aplikasi.','Bukti penerapan manajemen kata sandi pada aplikasi: mekanisme manajemen kata sandi, teknologi yang digunakan dalam proses autentikasi, kode sumber yang mengatur autentikasi.','Hasil pengujian keamanan terhadap manajemen kata sandi: respons aplikasi saat jumlah maksimum kesalahan pemasukan kata sandi tercapai; respons aplikasi saat pengujian ulang pemasukan kata sandi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(77,'FK-2','fk',2,NULL,'Pertanyaan FK-2','Menerapkan verifikasi kata sandi pada sisi server.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf b',NULL,0,'Dokumen STPK yang memuat penerapan verifikasi kata sandi pada sisi server yang diakui oleh organisasi.','Bukti penerapan verifikasi sisi server: mekanisme verifikasi kata sandi oleh aplikasi, kemampuan aplikasi mendeteksi penggunaan kata sandi lemah, kode sumber verifikasi, penyimpanan username dan kata sandi pada aplikasi.','Hasil pengujian verifikasi kata sandi pada sisi server: respons aplikasi saat menerima kata sandi yang tidak memenuhi syarat keamanan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(78,'FK-3','fk',3,NULL,'Pertanyaan FK-3','Mengatur jumlah karakter, kombinasi jenis karakter, dan masa berlaku dari kata sandi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf c',NULL,0,'Dokumen STPK yang memuat pengaturan jumlah karakter, kombinasi jenis karakter, dan masa berlaku dari kata sandi.','Bukti penerapan pengaturan kata sandi: fitur reset kata sandi, petunjuk jumlah dan kombinasi karakter kata sandi, mekanisme aplikasi ketika kata sandi melebihi masa berlaku, kode sumber pengaturan.','Hasil pengujian pemenuhan persyaratan kata sandi: respons aplikasi saat menerima kata sandi yang tidak memenuhi jumlah/kombinasi karakter; respons aplikasi saat masa berlaku kata sandi terlampaui.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(79,'FK-4','fk',4,NULL,'Pertanyaan FK-4','Mengatur jumlah maksimum kesalahan dalam pemasukan kata sandi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf d',NULL,0,'Dokumen STPK yang memuat pengaturan jumlah maksimum kesalahan dalam pemasukan kata sandi.','Bukti penerapan pembatasan kesalahan: pengaturan jumlah maksimum kesalahan, mekanisme ketika melebihi percobaan, kode sumber yang mengatur mekanisme tersebut.','Hasil pengujian respons aplikasi saat jumlah maksimum kesalahan pemasukan kata sandi tercapai (mis. account lockout, captcha, delay).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(80,'FK-5','fk',5,NULL,'Pertanyaan FK-5','Mengatur mekanisme pemulihan kata sandi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf e',NULL,0,'Dokumen STPK yang memuat pengaturan mekanisme pemulihan kata sandi.','Bukti penerapan pemulihan kata sandi: mekanisme pemulihan kata sandi, notifikasi pada pengguna ketika melakukan pemulihan, kode sumber pemulihan kata sandi.','Hasil pengujian fitur pemulihan kata sandi: respons aplikasi saat pengguna meminta pemulihan; bukti notifikasi terkirim kepada pengguna.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(81,'FK-6','fk',6,NULL,'Pertanyaan FK-6','Menjaga kerahasiaan kata sandi yang disimpan melalui mekanisme kriptografi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf f',NULL,0,'Dokumen STPK yang memuat mekanisme menjaga kerahasiaan kata sandi yang disimpan melalui mekanisme kriptografi.','Bukti penerapan: atribut kata sandi untuk memastikan tidak disimpan dalam plain text, kode sumber yang menunjukkan algoritma dan proses hashing, kode sumber pengamanan password saat penyimpanan database.','Hasil verifikasi penyimpanan kata sandi pada database: bukti kata sandi tersimpan dalam bentuk hash/terenkripsi, bukan plain text.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(82,'FK-7','fk',7,NULL,'Pertanyaan FK-7','Menggunakan jalur komunikasi yang diamankan untuk proses autentikasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (1) huruf g',NULL,0,'Dokumen STPK yang memuat penggunaan jalur komunikasi yang diamankan untuk proses autentikasi.','Bukti penerapan jalur aman: protokol keamanan komunikasi yang digunakan, sertifikat, cakupan penerapan, dan konfigurasi SSL/TLS.','Hasil pengujian keamanan jalur komunikasi autentikasi: bukti enkripsi end-to-end saat proses login; hasil SSL/TLS scan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(83,'FK-8','fk',8,NULL,'Pertanyaan FK-8','Menggunakan pengendali sesi untuk proses manajemen sesi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf a',NULL,0,'Dokumen STPK yang memuat penggunaan pengendali sesi untuk proses manajemen sesi.','Bukti penerapan: sesi-cookie pada aplikasi, kode sumber pembuatan sesi.','Hasil pengujian manajemen sesi: bukti sesi unik per pengguna; bukti sesi tidak dapat dipalsukan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(84,'FK-9','fk',9,NULL,'Pertanyaan FK-9','Menggunakan pengendali sesi yang disediakan oleh kerangka kerja aplikasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf b',NULL,0,'Dokumen STPK yang memuat penggunaan pengendali sesi yang disediakan oleh kerangka kerja aplikasi.','Bukti penerapan: penggunaan header Set-Cookie pada header response HTTP, penggunaan skrip JavaScript yang menangani sesi, indikator Session ID atau Token, kode sumber pengaturan sesi.','Hasil pengujian kerangka kerja yang digunakan: bukti penggunaan pengendali sesi standar dari framework yang dipakai.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(85,'FK-10','fk',10,NULL,'Pertanyaan FK-10','Mengatur pembuatan dan keacakan token sesi yang dihasilkan oleh pengendali sesi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf c',NULL,0,'Dokumen STPK yang memuat pembuatan dan keacakan token sesi yang dihasilkan oleh pengendali sesi.','Kode sumber berisi mekanisme generate token session yang acak.','Hasil pengujian keacakan token sesi: bukti token sesi tidak dapat diprediksi (entropy yang memadai).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(86,'FK-11','fk',11,NULL,'Pertanyaan FK-11','Mengatur kondisi dan jangka waktu habis sesi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf d',NULL,0,'Dokumen STPK yang memuat kondisi dan jangka waktu habis sesi.','Bukti penerapan: aktivitas login kedua aplikasi yang menyebabkan pengguna lain dengan akun sama log out, informasi batas waktu sesi, informasi terkait penghancuran sesi yang habis masa berlaku.','Hasil pengujian timeout sesi: respons aplikasi saat sesi melewati batas waktu; bukti penghancuran sesi otomatis.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(87,'FK-12','fk',12,NULL,'Pertanyaan FK-12','Validasi dan pencantuman session id.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf e',NULL,0,'Dokumen STPK yang memuat validasi dan pencantuman session id.','Bukti penerapan atribut pada session ID.','Hasil pengujian validasi session ID: bukti session ID divalidasi pada setiap request.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(88,'FK-13','fk',13,NULL,'Pertanyaan FK-13','Pelindungan terhadap lokasi dan pengiriman token untuk sesi terautentikasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf f',NULL,0,'Dokumen STPK yang memuat pelindungan terhadap lokasi dan pengiriman token untuk sesi terautentikasi.','Bukti penggunaan atribut \"Secure\", \"HttpOnly\", \"Strict-Transport-Security\", \"Domain\", \"Path\", \"SameSite\" pada cookie.','Hasil pengujian header response: bukti atribut keamanan cookie diterapkan pada seluruh sesi terautentikasi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(89,'FK-14','fk',14,NULL,'Pertanyaan FK-14','Pelindungan terhadap duplikasi dan mekanisme persetujuan pengguna.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (2) huruf g',NULL,0,'Dokumen STPK yang memuat pelindungan terhadap duplikasi dan mekanisme persetujuan pengguna.','Bukti penerapan: sesi-cookie pada interface aplikasi, kode sumber yang mengatur sesi.','Hasil pengujian pencegahan duplikasi sesi: respons aplikasi saat terjadi login bersamaan dari perangkat berbeda.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(90,'FK-15','fk',15,NULL,'Pertanyaan FK-15','Menetapkan otorisasi pengguna untuk membatasi kontrol akses.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (3) huruf a',NULL,0,'Dokumen STPK yang memuat penetapan otorisasi pengguna untuk membatasi kontrol akses.','Bukti penerapan: dokumentasi aturan kontrol akses pada aplikasi, fitur pemberian otorisasi pada pengguna tertentu, dokumen pengajuan akses.','Hasil pengujian kontrol akses: respons aplikasi saat pengguna mengakses fitur di luar otorisasinya.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(91,'FK-16','fk',16,NULL,'Pertanyaan FK-16','Mengatur peringatan terhadap bahaya serangan otomatis apabila terjadi akses yang bersamaan atau akses yang terus-menerus pada fungsi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (3) huruf b',NULL,0,'Dokumen STPK yang memuat peringatan terhadap bahaya serangan otomatis.','Gambar/video/dokumen yang memperlihatkan interface aplikasi dan potongan kode sumber yang menunjukkan penerapan anti-CSRF dan anti-automation.','Hasil pengujian anti-automation: respons aplikasi saat menerima request berulang dalam waktu singkat (mis. throttling, captcha).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(92,'FK-17','fk',17,NULL,'Pertanyaan FK-17','Mengatur antarmuka pada sisi administrator.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (3) huruf c',NULL,0,'Dokumen STPK yang memuat antarmuka pada sisi administrator.','Bukti penerapan: halaman yang hanya dapat diakses oleh administrator, konfigurasi file robots.txt, penggunaan kata sandi untuk halaman administrator, pembatasan akses terhadap halaman administrator.','Hasil pengujian akses halaman administrator: respons aplikasi saat pengguna non-admin mencoba mengakses; bukti halaman tidak terekspos di public scanner.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(93,'FK-18','fk',18,NULL,'Pertanyaan FK-18','Mengatur verifikasi kebenaran token ketika mengakses data dan informasi yang dikecualikan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (3) huruf d',NULL,0,'Dokumen STPK yang memuat verifikasi kebenaran token ketika mengakses data dan informasi yang dikecualikan.','Bukti penerapan: cara akses ke halaman yang dikecualikan seperti antarmuka admin, atau bukti terdapatnya CSRF-Token pada header permintaan, perbedaan antar token CSRF tiap kali ada HTTP request.','Hasil pengujian validasi token: respons aplikasi saat token tidak valid atau dipalsukan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(94,'FK-19','fk',19,NULL,'Pertanyaan FK-19','Menerapkan fungsi validasi input pada sisi server.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf a',NULL,0,'Dokumen STPK yang memuat penerapan validasi input pada sisi server.','Bukti penerapan pengaturan validasi input pada kode sumber aplikasi.','Hasil pengujian validasi sisi server: respons aplikasi saat menerima input tidak valid yang melewati validasi client-side.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(95,'FK-20','fk',20,NULL,'Pertanyaan FK-20','Menerapkan mekanisme penolakan input jika terjadi kesalahan validasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf b',NULL,0,'Dokumen STPK yang memuat penerapan mekanisme penolakan input jika terjadi kesalahan validasi.','Bukti penerapan mekanisme penolakan input saat terjadi kesalahan validasi.','Hasil pengujian penolakan input: respons aplikasi saat menerima input yang gagal validasi (mis. pesan error informatif tanpa membocorkan internal logic).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(96,'FK-21','fk',21,NULL,'Pertanyaan FK-21','Memastikan runtime environment aplikasi tidak rentan terhadap serangan validasi input.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf c',NULL,0,'Dokumen STPK yang memuat pengaturan runtime environment yang aman terhadap serangan validasi input.','Bukti penerapan: konfigurasi runtime environment aplikasi yang aman, patch yang terpasang pada runtime.','Hasil pengujian kerentanan runtime: hasil scanning kerentanan terhadap runtime environment; bukti tindak lanjut atas temuan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(97,'FK-22','fk',22,NULL,'Pertanyaan FK-22','Melakukan validasi positif pada seluruh input.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf d',NULL,0,'Dokumen STPK yang memuat validasi positif pada seluruh input.','Bukti penerapan: kode sumber penyaringan input atau atribut HTML yang diperbolehkan, penggunaan tools/software seperti Web Application Firewall, konfigurasi daftar putih (white listing), catatan/daftar input yang tidak sesuai (terblokir).','Hasil pengujian validasi positif: respons aplikasi saat menerima input di luar daftar putih; log percobaan input yang diblokir.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(98,'FK-23','fk',23,NULL,'Pertanyaan FK-23','Melakukan filter terhadap data yang tidak dipercaya.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf e',NULL,0,'Dokumen STPK yang memuat filter terhadap data yang tidak dipercaya.','Bukti penerapan filter terhadap data yang tidak dipercaya.','Hasil pengujian filter: respons aplikasi saat menerima data dari sumber tidak dipercaya (mis. URL parameter, header HTTP).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(99,'FK-24','fk',24,NULL,'Pertanyaan FK-24','Menggunakan fitur kode dinamis.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf f',NULL,0,'Dokumen STPK yang memuat pengaturan penggunaan fitur kode dinamis.','Bukti penerapan: sumber kode yang bertujuan melakukan proses encoding atau unicode mengganti dengan karakter terhadap input pengguna yang tidak sesuai.','Hasil pengujian encoding: respons aplikasi saat menerima input yang mengandung karakter khusus.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(100,'FK-25','fk',25,NULL,'Pertanyaan FK-25','Melakukan pelindungan terhadap akses yang mengandung konten skrip.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf g',NULL,0,'Dokumen STPK yang memuat pelindungan terhadap akses yang mengandung konten skrip.','Bukti penerapan pelindungan terhadap akses yang mengandung konten skrip.','Hasil pengujian XSS (Cross-Site Scripting): respons aplikasi saat menerima input berisi skrip; bukti sanitasi output.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(101,'FK-26','fk',26,NULL,'Pertanyaan FK-26','Melakukan pelindungan dari serangan injeksi basis data.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (4) huruf h',NULL,0,'Dokumen STPK yang memuat pelindungan dari serangan injeksi basis data.','Bukti penerapan pelindungan dari serangan injeksi basis data (prepared statement, parameterized query, ORM).','Hasil pengujian SQL Injection: respons aplikasi saat menerima input yang berisi payload injeksi; hasil scanning kerentanan basis data.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(102,'FK-27','fk',27,NULL,'Pertanyaan FK-27','Menggunakan algoritma kriptografi, modul kriptografi, protokol kriptografi, dan kunci kriptografi sesuai dengan ketentuan peraturan perundang-undangan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (5) huruf a',NULL,0,'Dokumen STPK yang memuat penggunaan algoritma kriptografi, modul kriptografi, protokol kriptografi, dan kunci kriptografi sesuai peraturan.','Bukti penerapan: kode sumber atau konfigurasi klasifikasi data dan informasi sensitif serta penggunaan algoritma, modul, protokol, dan kunci kriptografi.','Hasil pengujian kesesuaian kriptografi: bukti algoritma yang digunakan tidak deprecated (mis. MD5, SHA-1); hasil audit konfigurasi kriptografi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(103,'FK-28','fk',28,NULL,'Pertanyaan FK-28','Melakukan autentikasi data yang dienkripsi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (5) huruf b',NULL,0,'Dokumen STPK yang memuat autentikasi data yang dienkripsi.','Bukti penerapan: klasifikasi data dan informasi sensitif, penggunaan enkripsi, implementasi kriptografi terbaru.','Hasil pengujian autentikasi data terenkripsi: bukti integritas data terenkripsi terjaga (mis. HMAC, AEAD).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(104,'FK-29','fk',29,NULL,'Pertanyaan FK-29','Menerapkan manajemen kunci kriptografi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (5) huruf c',NULL,0,'Dokumen STPK yang memuat penerapan manajemen kunci kriptografi.','Sumber kode atau konfigurasi yang menunjukkan penerapan manajemen kunci kriptografi.','Hasil pengujian manajemen kunci: bukti rotasi kunci secara berkala; bukti kunci tidak hardcoded di kode sumber.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(105,'FK-30','fk',30,NULL,'Pertanyaan FK-30','Membuat angka acak yang menggunakan generator angka acak kriptografi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (5) huruf d',NULL,0,'Dokumen STPK yang memuat pembuatan angka acak yang menggunakan generator angka acak kriptografi.','Sumber kode atau konfigurasi yang menunjukkan pembuatan angka acak menggunakan generator angka acak kriptografi.','Hasil pengujian keacakan: bukti penggunaan CSPRNG (Cryptographically Secure Pseudo-Random Number Generator), bukan random standar.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(106,'FK-31','fk',31,NULL,'Pertanyaan FK-31','Mengatur konten pesan yang ditampilkan ketika terjadi kesalahan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf a',NULL,0,'Dokumen STPK yang memuat pengaturan konten pesan ketika terjadi kesalahan.','Bukti penerapan: halaman aplikasi berisi pesan yang ditampilkan jika terjadi error.','Hasil pengujian pesan eror: respons aplikasi saat terjadi error (mis. tidak membocorkan stack trace, query, path file).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(107,'FK-32','fk',32,NULL,'Pertanyaan FK-32','Menggunakan metode penanganan eror untuk mencegah kesalahan terprediksi dan tidak terduga serta menangani seluruh pengecualian yang tidak ditangani.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf b',NULL,0,'Dokumen STPK yang memuat penggunaan metode penanganan eror.','Bukti penerapan: kode sumber Library/package sebagai exception handler dalam penanganan error.','Hasil pengujian exception handling: respons aplikasi saat menemui kondisi tidak terduga; bukti seluruh exception tertangani.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(108,'FK-33','fk',33,NULL,'Pertanyaan FK-33','Tidak mencantumkan informasi yang dikecualikan dalam pencatatan log.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf c',NULL,0,'Dokumen STPK yang memuat pengaturan pencantuman informasi yang dikecualikan dalam pencatatan log.','Bukti penerapan: pencatatan log aplikasi berupa log otentikasi, log kegagalan validasi input, log aktivitas user di aplikasi, dan kegagalan akses kontrol serta pencatatan log saat error.','Hasil verifikasi konten log: bukti log tidak memuat informasi sensitif (mis. password plain text, nomor kartu kredit, PII).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(109,'FK-34','fk',34,NULL,'Pertanyaan FK-34','Mengatur cakupan log yang dicatat untuk mendukung upaya penyelidikan ketika terjadi insiden.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf d',NULL,0,'Dokumen STPK yang memuat pengaturan cakupan log untuk mendukung penyelidikan insiden.','Bukti penerapan: log dan daftar insiden yang pernah terjadi pada aplikasi.','Hasil verifikasi kelengkapan log: bukti log memuat informasi yang cukup untuk forensik (timestamp, user, action, source IP, dll).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(110,'FK-35','fk',35,NULL,'Pertanyaan FK-35','Mengatur pelindungan log aplikasi dari akses dan modifikasi yang tidak sah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf e',NULL,0,'Dokumen STPK yang memuat pelindungan log aplikasi dari akses dan modifikasi yang tidak sah.','Bukti penerapan: pengaturan role akses ke log dan mekanisme monitoring log.','Hasil pengujian integritas log: bukti log tidak dapat dimodifikasi oleh pengguna biasa; bukti audit trail untuk akses log.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(111,'FK-36','fk',36,NULL,'Pertanyaan FK-36','Melakukan enkripsi pada data yang disimpan untuk mencegah injeksi log.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf f',NULL,0,'Dokumen STPK yang memuat enkripsi pada data yang disimpan untuk mencegah injeksi log.','Bukti penerapan mekanisme encoding pada data sebelum dicatat di log.','Hasil pengujian injeksi log: respons aplikasi saat menerima input yang mengandung karakter kontrol log (mis. newline injection).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(112,'FK-37','fk',37,NULL,'Pertanyaan FK-37','Melakukan sinkronisasi sumber waktu sesuai dengan zona waktu dan waktu yang benar.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (6) huruf g',NULL,0,'Dokumen STPK yang memuat sinkronisasi sumber waktu sesuai zona waktu yang benar.','Bukti penerapan sinkronisasi sumber waktu pada beberapa server.','Hasil verifikasi waktu: bukti seluruh server tersinkronisasi dengan NTP server terpercaya; bukti zona waktu konsisten.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(113,'FK-38','fk',38,NULL,'Pertanyaan FK-38','Melakukan identifikasi dan penyimpanan salinan informasi yang dikecualikan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf a',NULL,0,'Dokumen STPK yang memuat identifikasi dan penyimpanan salinan informasi yang dikecualikan.','Bukti penerapan: daftar informasi yang dikecualikan, penyimpanan pengamanan informasi yang dikecualikan dan salinannya.','Hasil verifikasi penyimpanan informasi dikecualikan: bukti informasi sensitif teridentifikasi dan tersimpan dengan kontrol akses yang sesuai.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(114,'FK-39','fk',39,NULL,'Pertanyaan FK-39','Melakukan pelindungan dari akses yang tidak sah terhadap informasi yang dikecualikan yang disimpan sementara dalam aplikasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf b',NULL,0,'Dokumen STPK yang memuat pelindungan dari akses tidak sah terhadap informasi sementara dalam aplikasi.','Bukti penerapan: penyimpanan informasi dikecualikan secara temporary, direktori temporary, perlindungan akses dari direktori temporary, penyimpanan informasi/file yang dilakukan di sisi client.','Hasil pengujian akses data temporary: respons aplikasi saat upaya akses tidak sah ke data sementara.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(115,'FK-40','fk',40,NULL,'Pertanyaan FK-40','Melakukan pertukaran, penghapusan, dan audit informasi yang dikecualikan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf c',NULL,0,'Dokumen STPK yang memuat pertukaran, penghapusan, dan audit informasi yang dikecualikan.','Bukti penerapan pertukaran, penghapusan, dan audit informasi yang dikecualikan.','Hasil verifikasi audit trail: bukti pertukaran dan penghapusan informasi sensitif tercatat dan dapat diaudit.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(116,'FK-41','fk',41,NULL,'Pertanyaan FK-41','Melakukan penentuan jumlah parameter.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf d',NULL,0,'Dokumen STPK yang memuat penentuan jumlah parameter.','Bukti penerapan pembatasan jumlah parameter pada aplikasi.','Hasil pengujian batasan parameter: respons aplikasi saat menerima request dengan parameter berlebih (mis. parameter pollution).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(117,'FK-42','fk',42,NULL,'Pertanyaan FK-42','Memastikan data disimpan dengan aman.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf e',NULL,0,'Dokumen STPK yang memuat mekanisme memastikan data disimpan dengan aman.','Bukti penerapan: penyimpanan dan pengamanan data/informasi pada aplikasi, mekanisme dan lokasi backup seluruh data.','Hasil pengujian keamanan penyimpanan: bukti enkripsi at-rest; bukti backup teruji dapat dipulihkan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(118,'FK-43','fk',43,NULL,'Pertanyaan FK-43','Menentukan metode untuk menghapus dan mengekspor data sesuai permintaan pengguna.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf f',NULL,0,'Dokumen STPK yang memuat penentuan metode menghapus dan mengekspor data sesuai permintaan pengguna.','Bukti penerapan fitur untuk menghapus dan melakukan ekspor data.','Hasil pengujian fitur penghapusan/ekspor: bukti data benar-benar terhapus (tidak hanya soft delete); bukti data ekspor lengkap.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(119,'FK-44','fk',44,NULL,'Pertanyaan FK-44','Membersihkan memori setelah tidak diperlukan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (7) huruf g',NULL,0,'Dokumen STPK yang memuat pembersihan memori setelah tidak diperlukan.','Bukti penerapan mekanisme pembersihan data pada memori setelah tidak diperlukan.','Hasil pengujian pembersihan memori: bukti data sensitif tidak tersisa di memori setelah penggunaan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(120,'FK-45','fk',45,NULL,'Pertanyaan FK-45','Menggunakan komunikasi terenkripsi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (8) huruf a',NULL,0,'Dokumen STPK yang memuat penggunaan komunikasi terenkripsi.','Bukti penerapan penggunaan protokol TLS pada seluruh koneksi masuk dan keluar.','Hasil pengujian enkripsi komunikasi: bukti seluruh komunikasi menggunakan HTTPS/TLS; hasil scan SSL Labs.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(121,'FK-46','fk',46,NULL,'Pertanyaan FK-46','Mengatur koneksi masuk dan keluar yang aman dan terenkripsi dari sisi pengguna.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (8) huruf b',NULL,0,'Dokumen STPK yang memuat pengaturan koneksi masuk dan keluar yang aman dan terenkripsi dari sisi pengguna.','Bukti penerapan penggunaan protokol TLS pada seluruh koneksi masuk dan keluar.','Hasil pengujian koneksi: bukti redirect HTTP ke HTTPS; bukti HSTS diterapkan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(122,'FK-47','fk',47,NULL,'Pertanyaan FK-47','Mengatur jenis algoritma yang digunakan dan alat pengujiannya.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (8) huruf c',NULL,0,'Dokumen STPK yang memuat jenis algoritma yang digunakan dan alat pengujiannya.','Bukti penerapan: versi TLS yang digunakan dan jenis algoritma yang digunakan.','Hasil pengujian cipher suites: bukti tidak menggunakan algoritma deprecated (mis. TLS 1.0/1.1, RC4, 3DES).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(123,'FK-48','fk',48,NULL,'Pertanyaan FK-48','Mengatur aktivasi dan konfigurasi sertifikat elektronik yang diterbitkan oleh penyelenggara sertifikasi elektronik.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (8) huruf d',NULL,0,'Dokumen STPK yang memuat aktivasi dan konfigurasi sertifikat elektronik.','Bukti penerapan domain yang tercakup dalam penggunaan TLS dan validitas sertifikat.','Hasil verifikasi sertifikat: bukti sertifikat valid, tidak expired, dan diterbitkan oleh CA terpercaya/PSrE Indonesia.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(124,'FK-49','fk',49,NULL,'Pertanyaan FK-49','Menggunakan analisis kode dalam kontrol kode berbahaya.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (9) huruf a',NULL,0,'Dokumen STPK yang memuat penggunaan analisis kode dalam kontrol kode berbahaya.','Bukti penerapan mekanisme dan hasil analisis kode sumber.','Hasil pengujian analisis kode: laporan SAST/DAST scan; bukti tindak lanjut atas temuan kerentanan kode.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(125,'FK-50','fk',50,NULL,'Pertanyaan FK-50','Memastikan kode sumber aplikasi dan pustaka tidak mengandung kode berbahaya dan fungsionalitas lain yang tidak diinginkan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (9) huruf b',NULL,0,'Dokumen STPK yang memuat mekanisme memastikan kode sumber dan pustaka tidak mengandung kode berbahaya.','Bukti penerapan mekanisme pengecekan kode sumber aplikasi dan pustaka supaya tidak mengandung kode berbahaya.','Hasil pengujian kode sumber: laporan dependency scanning; bukti pustaka berasal dari sumber terpercaya.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(126,'FK-51','fk',51,NULL,'Pertanyaan FK-51','Mengatur izin terkait fitur atau sensor terkait privasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (9) huruf c',NULL,0,'Dokumen STPK yang memuat pengaturan izin terkait fitur atau sensor terkait privasi.','Bukti penerapan pengaturan izin terkait fitur atau sensor terkait privasi.','Hasil verifikasi pengaturan izin: bukti aplikasi hanya meminta izin yang relevan dengan fungsinya.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(127,'FK-52','fk',52,NULL,'Pertanyaan FK-52','Mengatur pelindungan integritas.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (9) huruf d',NULL,0,'Dokumen STPK yang memuat pelindungan integritas.','Bukti penerapan pengaturan izin terkait File Integrity Monitoring.','Hasil pengujian integritas: bukti FIM aktif dan menghasilkan alert saat terjadi modifikasi file kritis.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(128,'FK-53','fk',53,NULL,'Pertanyaan FK-53','Mengatur mekanisme fitur pembaruan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (9) huruf e',NULL,0,'Dokumen STPK yang memuat mekanisme fitur pembaruan.','Bukti penerapan pembaruan fitur aplikasi dengan penambahan digital signature dan dilakukan secara otomatis.','Hasil pengujian mekanisme pembaruan: bukti pembaruan tervalidasi (digital signature); bukti rollback tersedia jika pembaruan gagal.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(129,'FK-54','fk',54,NULL,'Pertanyaan FK-54','Memproses alur logika bisnis dalam urutan langkah dan waktu yang realistis.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (10) huruf a',NULL,0,'Dokumen STPK yang memuat pemrosesan alur logika bisnis dalam urutan langkah dan waktu yang realistis.','Bukti penerapan alur proses bisnis utama dari awal sampai akhir sesuai dengan urutan langkah dan waktu yang realistis.','Hasil pengujian alur bisnis: respons aplikasi saat upaya melewati langkah (mis. skipping checkout step); respons saat input waktu tidak realistis.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(130,'FK-55','fk',55,NULL,'Pertanyaan FK-55','Memastikan logika bisnis memiliki batasan dan validasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (10) huruf b',NULL,0,'Dokumen STPK yang memuat batasan dan validasi pada logika bisnis.','Bukti penerapan alur proses bisnis utama dari awal sampai akhir sesuai dengan hak akses yang diberikan.','Hasil pengujian batasan logika bisnis: respons aplikasi saat upaya melewati batas (mis. pembelian dengan kuantitas negatif).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(131,'FK-56','fk',56,NULL,'Pertanyaan FK-56','Memonitor aktivitas yang tidak biasa.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (10) huruf c',NULL,0,'Dokumen STPK yang memuat pemantauan aktivitas yang tidak biasa.','Bukti penerapan: mekanisme monitoring terhadap insiden, konfigurasi dashboard monitoring, kode sumber aplikasi sistem monitoring aktivitas.','Hasil pengujian monitoring: bukti alert terpicu saat aktivitas anomali (mis. login massal, akses di luar jam kerja).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(132,'FK-57','fk',57,NULL,'Pertanyaan FK-57','Membantu dalam kontrol antiotomatisasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (10) huruf d',NULL,0,'Dokumen STPK yang memuat mekanisme kontrol anti-otomatisasi.','Bukti penerapan pengaturan kontrol anti-automation beserta kode sumber yang menunjukkan pengaturan tersebut.','Hasil pengujian anti-bot: respons aplikasi saat menerima request otomatis (mis. captcha, rate limiting).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(133,'FK-58','fk',58,NULL,'Pertanyaan FK-58','Memberikan peringatan ketika terjadi serangan otomatis atau aktivitas yang tidak biasa.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (10) huruf e',NULL,0,'Dokumen STPK yang memuat mekanisme pemberian peringatan ketika terjadi serangan otomatis atau aktivitas tidak biasa.','Bukti penerapan: mekanisme notifikasi hasil monitoring terhadap insiden, konfigurasi pemberian notifikasi hasil monitoring, kode sumber pemberian peringatan.','Hasil pengujian notifikasi: bukti alert/peringatan terkirim ke administrator saat serangan terdeteksi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(134,'FK-59','fk',59,NULL,'Pertanyaan FK-59','Mengatur jumlah file untuk setiap pengguna dan kuota ukuran file yang diunggah.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (11) huruf a',NULL,0,'Dokumen STPK yang memuat pengaturan jumlah file dan kuota ukuran file unggahan.','Bukti penerapan: fitur unggah file pada aplikasi, pembatasan jumlah dan ukuran file pada fitur unggah, kode sumber yang mengaturnya.','Hasil pengujian batasan file: respons aplikasi saat upload file melebihi kuota atau jumlah maksimum.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(135,'FK-60','fk',60,NULL,'Pertanyaan FK-60','Melakukan validasi file sesuai dengan tipe konten yang diharapkan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (11) huruf b',NULL,0,'Dokumen STPK yang memuat pengaturan validasi file sesuai tipe konten.','Bukti penerapan: fitur unggah file pada aplikasi, pembatasan jenis file berdasarkan tipe kontennya, kode sumber mekanisme validasi jenis file.','Hasil pengujian validasi file: respons aplikasi saat upload file dengan ekstensi disamarkan atau tipe konten tidak sesuai.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(136,'FK-61','fk',61,NULL,'Pertanyaan FK-61','Melakukan pelindungan terhadap metadata input dan metadata file.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (11) huruf c',NULL,0,'Dokumen STPK yang memuat pelindungan terhadap metadata input dan metadata file.','Bukti penerapan: fitur unggah dan unduh file pada aplikasi, mekanisme penyimpanan metadata input dan metadata file pada fitur unggah dan unduh, kode sumber penyimpanan file.','Hasil pengujian metadata file: bukti metadata sensitif (EXIF, author, lokasi) dibersihkan saat upload.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(137,'FK-62','fk',62,NULL,'Pertanyaan FK-62','Melakukan pemindaian file yang diperoleh dari sumber yang tidak dipercaya.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (11) huruf d',NULL,0,'Dokumen STPK yang memuat mekanisme pemindaian file dari sumber tidak dipercaya.','Bukti penerapan: fitur pemindaian file yang diperoleh dari sumber tidak dipercaya seperti proses pemindaian antivirus, kode sumber yang menunjukkan fitur pemindaian file.','Hasil pengujian antivirus: respons aplikasi saat menerima file yang terdeteksi malware.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(138,'FK-63','fk',63,NULL,'Pertanyaan FK-63','Melakukan konfigurasi server untuk mengunduh file sesuai ekstensi yang ditentukan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (11) huruf e',NULL,0,'Dokumen STPK yang memuat mekanisme konfigurasi server untuk mengunduh file sesuai ekstensi yang ditentukan.','Bukti penerapan: fitur unduh file pada aplikasi, kode sumber yang menunjukkan konfigurasi pada proses unduh file.','Hasil pengujian unduhan: bukti server tidak meng-eksekusi file (mis. .php) saat di-request sebagai unduhan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(139,'FK-64','fk',64,NULL,'Pertanyaan FK-64','Melakukan konfigurasi layanan web.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf a',NULL,0,'Dokumen STPK yang memuat mekanisme konfigurasi layanan web.','Bukti penerapan konfigurasi layanan web telah dilakukan.','Hasil pengujian konfigurasi: bukti layanan web hanya membuka port yang diperlukan; bukti security headers terkonfigurasi.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(140,'FK-65','fk',65,NULL,'Pertanyaan FK-65','Memverifikasi uniform resource identifier API tidak menampilkan informasi yang berpotensi sebagai celah keamanan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf b',NULL,0,'Dokumen STPK yang memuat mekanisme verifikasi URI API.','Bukti penerapan: daftar layanan API, perimeter keamanan (fungsi otentikasi, manajemen, dan otorisasi) pada API, demonstrasi request API dari aplikasi.','Hasil pengujian URI API: bukti URI tidak membocorkan struktur internal (mis. tidak ada parameter ID berurutan).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(141,'FK-66','fk',66,NULL,'Pertanyaan FK-66','Membuat keputusan otorisasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf c',NULL,0,'Dokumen STPK yang memuat mekanisme pembuatan keputusan otorisasi.','Bukti penerapan mekanisme otorisasi API.','Hasil pengujian otorisasi API: respons API saat permintaan tanpa token atau dengan token tidak valid.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(142,'FK-67','fk',67,NULL,'Pertanyaan FK-67','Menampilkan metode RESTful hypertext transfer protocol apabila input pengguna dinyatakan valid.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf d',NULL,0,'Dokumen STPK yang memuat mekanisme menampilkan metode RESTful.','Bukti penerapan: kode sumber method request API, kode sumber penanganan request yang tidak sesuai dengan yang telah didefinisikan.','Hasil pengujian method HTTP: respons API saat menerima method yang tidak diizinkan (405 Method Not Allowed).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(143,'FK-68','fk',68,NULL,'Pertanyaan FK-68','Menggunakan validasi skema dan verifikasi sebelum menerima input.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf e',NULL,0,'Dokumen STPK yang memuat penggunaan validasi skema dan verifikasi sebelum menerima input.','Bukti penerapan validasi input pada API (lihat juga Kontrol Pemeriksaan No.19).','Hasil pengujian validasi skema: respons API saat menerima payload yang tidak sesuai skema (mis. JSON Schema validation).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(144,'FK-69','fk',69,NULL,'Pertanyaan FK-69','Menggunakan metode pelindungan layanan berbasis web.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf f',NULL,0,'Dokumen STPK yang memuat penggunaan metode pelindungan layanan berbasis web.','Bukti penerapan: konfigurasi Web Application Firewall (WAF), API Gateway, atau mekanisme pelindungan layanan web lainnya.','Hasil pengujian pelindungan WAF: bukti WAF memblokir serangan umum (OWASP Top 10) yang dikirim ke layanan.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(145,'FK-70','fk',70,NULL,'Pertanyaan FK-70','Menerapkan kontrol antiotomatisasi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (12) huruf g',NULL,0,'Dokumen STPK yang memuat pengaturan penerapan kontrol anti-otomatisasi.','Bukti penerapan keamanan atas request masif (DDoS) pada aplikasi.','Hasil pengujian anti-DDoS: respons aplikasi saat menerima request masif (mis. throttling, rate limiting per IP).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(146,'FK-71','fk',71,NULL,'Pertanyaan FK-71','Mengonfigurasi server sesuai rekomendasi server aplikasi dan kerangka kerja aplikasi yang digunakan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (13) huruf a',NULL,0,'Dokumen STPK yang memuat konfigurasi server sesuai rekomendasi server aplikasi dan kerangka kerja aplikasi.','Bukti penerapan: versi patch sistem operasi dan perangkat lunak, kesesuaian konfigurasi server dan kerangka kerja aplikasi dengan rekomendasi server yang digunakan.','Hasil hardening assessment: bukti konfigurasi server sesuai dengan benchmark keamanan (mis. CIS Benchmark).','2026-05-11 08:33:00','2026-05-26 03:26:05'),(147,'FK-72','fk',72,NULL,'Pertanyaan FK-72','Mendokumentasi, menyalin konfigurasi, dan semua dependensi.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (13) huruf b',NULL,0,'Dokumen STPK yang memuat mekanisme mendokumentasi, menyalin konfigurasi, dan semua dependensi.','Bukti penerapan: media penyimpanan salinan konfigurasi, daftar dependensi konfigurasi dan dependensi.','Hasil verifikasi dokumentasi: bukti konfigurasi terdokumentasi dan dapat dipulihkan dari backup.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(148,'FK-73','fk',73,NULL,'Pertanyaan FK-73','Menghapus fitur, dokumentasi, sampel, dan konfigurasi yang tidak diperlukan.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (13) huruf c',NULL,0,'Dokumen STPK yang memuat mekanisme menghapus fitur, dokumentasi, sampel, dan konfigurasi yang tidak diperlukan.','Bukti penerapan proses menghapus fitur, dokumentasi, sampel, dan konfigurasi yang tidak diperlukan.','Hasil pengujian attack surface: bukti tidak ada default credential, sample app, atau dokumen development yang ter-expose.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(149,'FK-74','fk',74,NULL,'Pertanyaan FK-74','Memvalidasi integritas aset jika aset aplikasi diakses secara eksternal.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (13) huruf d',NULL,0,'Dokumen STPK yang memuat validasi integritas aset jika aset aplikasi diakses secara eksternal.','Bukti penerapan indikator integritas aset pada kode sumber aplikasi.','Hasil pengujian integritas aset eksternal: bukti penggunaan Subresource Integrity (SRI) untuk script/CSS dari CDN.','2026-05-11 08:33:00','2026-05-26 03:26:05'),(150,'FK-75','fk',75,NULL,'Pertanyaan FK-75','Menggunakan respons aplikasi dan konten yang aman.','Peraturan BSSN Nomor 4 Tahun 2021 Pasal 27 ayat (13) huruf e',NULL,0,'Dokumen STPK yang memuat penggunaan respons aplikasi dan konten yang aman.','Bukti penerapan: respons aplikasi atas input yang dilakukan, konfigurasi HTTP Header.','Hasil pengujian security headers: bukti header keamanan (CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy) terkonfigurasi.','2026-05-11 08:33:00','2026-05-26 03:26:05');
/*!40000 ALTER TABLE `butir_penilaian` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('spbe-scan-cache-3ee2699664b970206500c218b52e101e','i:1;',1780144085),('spbe-scan-cache-3ee2699664b970206500c218b52e101e:timer','i:1780144085;',1780144085),('spbe-scan-cache-ebc4aa41da70f5692153d2c12b435741','i:1;',1780144112),('spbe-scan-cache-ebc4aa41da70f5692153d2c12b435741:timer','i:1780144112;',1780144112),('spbe-scan-cache-f945940de36297a6d5b45f6010606ed9','i:1;',1780144050),('spbe-scan-cache-f945940de36297a6d5b45f6010606ed9:timer','i:1780144050;',1780144050);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'default','{\"uuid\":\"7912d3b3-9699-4a60-b26e-eaba989324a9\",\"displayName\":\"App\\\\Jobs\\\\RunNmapScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunNmapScan\",\"command\":\"O:20:\\\"App\\\\Jobs\\\\RunNmapScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:3;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780040299,\"delay\":null}',0,NULL,1780040299,1780040299),(2,'default','{\"uuid\":\"02eb184e-eb80-45ba-a711-5557386bbed6\",\"displayName\":\"App\\\\Jobs\\\\RunCurlScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunCurlScan\",\"command\":\"O:20:\\\"App\\\\Jobs\\\\RunCurlScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780040299,\"delay\":null}',0,NULL,1780040299,1780040299),(3,'default','{\"uuid\":\"17b2a148-a50e-4233-bec6-aeadcf75525a\",\"displayName\":\"App\\\\Jobs\\\\RunTestsslScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunTestsslScan\",\"command\":\"O:23:\\\"App\\\\Jobs\\\\RunTestsslScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:2;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780040299,\"delay\":null}',0,NULL,1780040299,1780040299),(4,'default','{\"uuid\":\"9444432c-050f-414d-89da-01d7386aeed6\",\"displayName\":\"App\\\\Jobs\\\\RunCurlScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunCurlScan\",\"command\":\"O:20:\\\"App\\\\Jobs\\\\RunCurlScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:1;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780043342,\"delay\":null}',0,NULL,1780043342,1780043342),(5,'default','{\"uuid\":\"1e9ced34-51d6-4027-9724-57ff3d1585f6\",\"displayName\":\"App\\\\Jobs\\\\RunNiktoScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunNiktoScan\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\RunNiktoScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:4;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780143337,\"delay\":null}',0,NULL,1780143337,1780143337),(6,'default','{\"uuid\":\"d6863a30-9603-43f2-a8ca-6576393cd9ea\",\"displayName\":\"App\\\\Jobs\\\\RunZapScan\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":2,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":600,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"App\\\\Jobs\\\\RunZapScan\",\"command\":\"O:19:\\\"App\\\\Jobs\\\\RunZapScan\\\":1:{s:4:\\\"scan\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:21:\\\"App\\\\Models\\\\ScanResult\\\";s:2:\\\"id\\\";i:5;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}\",\"batchId\":null},\"createdAt\":1780143337,\"delay\":null}',0,NULL,1780143337,1780143337);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2024_01_01_000001_create_audit_requests_table',1),(5,'2024_01_01_000002_create_audit_plans_table',1),(6,'2024_01_01_000003_create_audit_plan_auditors_table',1),(7,'2024_01_01_000004_create_butir_penilaian_table',1),(8,'2024_01_01_000005_create_penilaian_butir_table',1),(9,'2024_01_01_000006_create_bukti_butir_table',1),(10,'2024_01_01_000007_create_scan_results_table',1),(11,'2024_01_01_000008_create_temuan_audit_table',1),(12,'2024_01_01_000009_create_pesan_tindak_lanjut_table',1),(13,'2024_01_01_000010_create_audit_results_table',1),(14,'2026_05_11_140232_add_two_factor_columns_to_users_table',1),(15,'2026_05_11_140233_create_passkeys_table',1),(16,'2026_05_11_140413_create_personal_access_tokens_table',1),(17,'2026_05_11_200000_add_role_and_nama_instansi_to_users_table',2),(18,'2026_05_20_000001_add_substansi_columns_to_butir_penilaian_table',3),(19,'2026_05_20_000002_add_jenis_acuan_to_bukti_butir_table',3),(20,'2026_05_26_000001_add_judul_and_sumber_to_butir_penilaian_table',4),(21,'2026_05_26_000002_add_status_pengisian_to_audit_plans_table',5),(22,'2026_05_28_000001_add_columns_to_scan_results_table',6),(23,'2026_05_28_000002_update_audit_results_add_konklusi_keseluruhan',7),(24,'2026_05_28_000003_update_temuan_audit_add_judul_change_enum',7),(25,'2026_05_30_000001_create_activity_log_table',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passkeys`
--

DROP TABLE IF EXISTS `passkeys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passkeys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `credential` json NOT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `passkeys_credential_id_unique` (`credential_id`),
  KEY `passkeys_user_id_index` (`user_id`),
  CONSTRAINT `passkeys_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passkeys`
--

LOCK TABLES `passkeys` WRITE;
/*!40000 ALTER TABLE `passkeys` DISABLE KEYS */;
/*!40000 ALTER TABLE `passkeys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `penilaian_butir`
--

DROP TABLE IF EXISTS `penilaian_butir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `penilaian_butir` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_plan_id` bigint unsigned NOT NULL,
  `auditor_id` bigint unsigned NOT NULL,
  `butir_id` bigint unsigned NOT NULL,
  `jawaban_auditee` text COLLATE utf8mb4_unicode_ci,
  `edk` enum('memadai','perlu_peningkatan','tidak_memadai') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_edk` text COLLATE utf8mb4_unicode_ci,
  `eik` enum('sesuai','tidak_sesuai','skip') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_eik` text COLLATE utf8mb4_unicode_ci,
  `efk` enum('efektif','perlu_peningkatan','belum_efektif') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_efk` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `penilaian_butir_audit_plan_id_foreign` (`audit_plan_id`),
  KEY `penilaian_butir_auditor_id_foreign` (`auditor_id`),
  KEY `penilaian_butir_butir_id_foreign` (`butir_id`),
  CONSTRAINT `penilaian_butir_audit_plan_id_foreign` FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penilaian_butir_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `penilaian_butir_butir_id_foreign` FOREIGN KEY (`butir_id`) REFERENCES `butir_penilaian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `penilaian_butir`
--

LOCK TABLES `penilaian_butir` WRITE;
/*!40000 ALTER TABLE `penilaian_butir` DISABLE KEYS */;
INSERT INTO `penilaian_butir` VALUES (1,1,3,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(2,1,3,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(3,1,3,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(4,1,3,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(5,1,3,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(6,1,3,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(7,1,3,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(8,1,3,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(9,1,3,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(10,1,3,10,'asdadaa',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:33:59'),(11,1,3,11,'asdadaadss',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:34:02'),(12,1,3,12,'asdas',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:34:05'),(13,1,3,13,'asdaadad',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:34:07'),(14,1,3,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(15,1,3,15,'asdsa',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:34:11'),(16,1,3,16,'asda',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 03:34:13'),(17,1,3,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(18,1,3,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(19,1,3,19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(20,1,3,20,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(21,1,3,21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(22,1,3,22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(23,1,3,23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(24,1,3,24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(25,1,3,25,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(26,1,3,26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(27,1,3,27,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(28,1,3,28,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(29,1,3,29,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(30,1,3,30,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(31,1,3,31,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(32,1,3,32,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(33,1,3,33,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(34,1,3,34,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(35,1,3,35,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(36,1,3,36,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(37,1,3,37,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(38,1,3,38,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(39,1,3,39,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(40,1,3,40,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(41,1,3,41,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(42,1,3,42,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(43,1,3,43,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(44,1,3,44,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(45,1,3,45,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(46,1,3,46,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(47,1,3,47,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(48,1,3,48,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(49,1,3,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(50,1,3,50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(51,1,3,51,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(52,1,3,52,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(53,1,3,53,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(54,1,3,54,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(55,1,3,55,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(56,1,3,56,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(57,1,3,57,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(58,1,3,58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(59,1,3,59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(60,1,3,60,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(61,1,3,61,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(62,1,3,62,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(63,1,3,63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(64,1,3,64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(65,1,3,65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(66,1,3,66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(67,1,3,67,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(68,1,3,68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(69,1,3,69,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(70,1,3,70,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(71,1,3,71,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(72,1,3,72,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(73,1,3,73,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(74,1,3,74,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(75,1,3,75,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-26 01:58:28','2026-05-26 01:58:28'),(76,2,6,76,'halohai','tidak_memadai',NULL,'skip',NULL,'belum_efektif',NULL,'2026-05-27 05:19:04','2026-05-30 05:26:49'),(77,2,6,77,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(78,2,6,78,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(79,2,6,79,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(80,2,6,80,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(81,2,6,81,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(82,2,6,82,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(83,2,6,83,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(84,2,6,84,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(85,2,6,85,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(86,2,6,86,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(87,2,6,87,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(88,2,6,88,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(89,2,6,89,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(90,2,6,90,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(91,2,6,91,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(92,2,6,92,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(93,2,6,93,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(94,2,6,94,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(95,2,6,95,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(96,2,6,96,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(97,2,6,97,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(98,2,6,98,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(99,2,6,99,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(100,2,6,100,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(101,2,6,101,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(102,2,6,102,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(103,2,6,103,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(104,2,6,104,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(105,2,6,105,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(106,2,6,106,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(107,2,6,107,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(108,2,6,108,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(109,2,6,109,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(110,2,6,110,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(111,2,6,111,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(112,2,6,112,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(113,2,6,113,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(114,2,6,114,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(115,2,6,115,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(116,2,6,116,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(117,2,6,117,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(118,2,6,118,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(119,2,6,119,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(120,2,6,120,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(121,2,6,121,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(122,2,6,122,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(123,2,6,123,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(124,2,6,124,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(125,2,6,125,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(126,2,6,126,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(127,2,6,127,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(128,2,6,128,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(129,2,6,129,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(130,2,6,130,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(131,2,6,131,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(132,2,6,132,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(133,2,6,133,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(134,2,6,134,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(135,2,6,135,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(136,2,6,136,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(137,2,6,137,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(138,2,6,138,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(139,2,6,139,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(140,2,6,140,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(141,2,6,141,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(142,2,6,142,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(143,2,6,143,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(144,2,6,144,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(145,2,6,145,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(146,2,6,146,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(147,2,6,147,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(148,2,6,148,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(149,2,6,149,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(150,2,6,150,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:19:04','2026-05-27 05:19:04'),(151,2,6,26,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(152,2,6,35,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(153,2,6,36,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(154,2,6,37,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(155,2,6,38,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(156,2,6,39,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(157,2,6,40,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(158,2,6,41,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(159,2,6,42,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(160,2,6,43,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(161,2,6,44,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(162,2,6,27,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(163,2,6,45,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(164,2,6,46,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(165,2,6,47,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(166,2,6,48,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(167,2,6,49,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(168,2,6,50,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(169,2,6,51,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(170,2,6,52,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(171,2,6,53,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(172,2,6,54,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(173,2,6,28,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(174,2,6,55,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(175,2,6,56,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(176,2,6,57,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(177,2,6,58,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(178,2,6,59,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(179,2,6,60,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(180,2,6,61,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(181,2,6,62,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(182,2,6,63,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(183,2,6,64,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(184,2,6,29,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(185,2,6,65,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(186,2,6,66,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(187,2,6,67,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(188,2,6,68,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(189,2,6,69,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(190,2,6,70,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(191,2,6,71,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(192,2,6,72,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(193,2,6,73,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(194,2,6,74,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(195,2,6,30,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(196,2,6,75,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(197,2,6,31,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(198,2,6,32,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(199,2,6,33,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(200,2,6,34,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(201,2,6,1,'halo',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:36:32'),(202,2,6,10,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(203,2,6,11,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(204,2,6,12,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(205,2,6,13,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(206,2,6,14,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(207,2,6,15,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(208,2,6,16,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(209,2,6,17,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(210,2,6,18,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(211,2,6,19,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(212,2,6,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(213,2,6,20,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(214,2,6,21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(215,2,6,22,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(216,2,6,23,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(217,2,6,24,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(218,2,6,25,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(219,2,6,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(220,2,6,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(221,2,6,5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(222,2,6,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(223,2,6,7,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(224,2,6,8,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27'),(225,2,6,9,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:35:27','2026-05-27 05:35:27');
/*!40000 ALTER TABLE `penilaian_butir` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesan_tindak_lanjut`
--

DROP TABLE IF EXISTS `pesan_tindak_lanjut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesan_tindak_lanjut` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `temuan_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pesan_tindak_lanjut_temuan_id_foreign` (`temuan_id`),
  KEY `pesan_tindak_lanjut_user_id_foreign` (`user_id`),
  CONSTRAINT `pesan_tindak_lanjut_temuan_id_foreign` FOREIGN KEY (`temuan_id`) REFERENCES `temuan_audit` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pesan_tindak_lanjut_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesan_tindak_lanjut`
--

LOCK TABLES `pesan_tindak_lanjut` WRITE;
/*!40000 ALTER TABLE `pesan_tindak_lanjut` DISABLE KEYS */;
/*!40000 ALTER TABLE `pesan_tindak_lanjut` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scan_results`
--

DROP TABLE IF EXISTS `scan_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scan_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_plan_id` bigint unsigned NOT NULL,
  `tool` enum('curl','testssl','nmap','nikto','zap') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('menunggu','berjalan','selesai','gagal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `hasil_json` json DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scan_results_audit_plan_id_foreign` (`audit_plan_id`),
  CONSTRAINT `scan_results_audit_plan_id_foreign` FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scan_results`
--

LOCK TABLES `scan_results` WRITE;
/*!40000 ALTER TABLE `scan_results` DISABLE KEYS */;
INSERT INTO `scan_results` VALUES (1,1,'curl','https://bogor.com','gagal',NULL,NULL,'2026-05-29 01:29:10','Dibatalkan oleh auditor','2026-05-29 00:38:16','2026-05-29 01:29:10'),(2,1,'testssl','https://bogor.com','gagal',NULL,NULL,'2026-05-29 01:28:49','Dibatalkan oleh auditor','2026-05-29 00:38:16','2026-05-29 01:28:49'),(3,1,'nmap','https://bogor.com','gagal',NULL,NULL,'2026-05-29 01:28:49','Dibatalkan oleh auditor','2026-05-29 00:38:17','2026-05-29 01:28:49'),(4,1,'nikto','https://bogor.com','gagal',NULL,NULL,'2026-05-30 05:15:52','Dibatalkan oleh auditor','2026-05-30 05:15:37','2026-05-30 05:15:52'),(5,1,'zap','https://bogor.com','gagal',NULL,NULL,'2026-05-30 05:15:52','Dibatalkan oleh auditor','2026-05-30 05:15:37','2026-05-30 05:15:52');
/*!40000 ALTER TABLE `scan_results` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('NS8jeyb10Peo1Nc1s7oVVHBQX1qijs5A7CgIK2cJ',3,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJYQXRwNGsyUFJzSFhaT1VKNUcwVWs5NDF0ekRxT1ExVHJOOHp1bkE1IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MywicGFzc3dvcmRfaGFzaF9zYW5jdHVtIjoiZmMzOTc2NzgxNTIyNDFjMmUyZjYyM2I5NTIxNDAxYjM5MTkyNmU3MDg2OGFjMzY1MjgzODc0Zjc2NDYwYmUwMCJ9',1780144061);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `temuan_audit`
--

DROP TABLE IF EXISTS `temuan_audit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `temuan_audit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `audit_plan_id` bigint unsigned NOT NULL,
  `auditor_id` bigint unsigned NOT NULL,
  `butir_id` bigint unsigned NOT NULL,
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `risiko` enum('tinggi','sedang','rendah') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rekomendasi` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_tindak_lanjut` enum('proses','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'proses',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `temuan_audit_audit_plan_id_foreign` (`audit_plan_id`),
  KEY `temuan_audit_auditor_id_foreign` (`auditor_id`),
  KEY `temuan_audit_butir_id_foreign` (`butir_id`),
  CONSTRAINT `temuan_audit_audit_plan_id_foreign` FOREIGN KEY (`audit_plan_id`) REFERENCES `audit_plans` (`id`) ON DELETE CASCADE,
  CONSTRAINT `temuan_audit_auditor_id_foreign` FOREIGN KEY (`auditor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `temuan_audit_butir_id_foreign` FOREIGN KEY (`butir_id`) REFERENCES `butir_penilaian` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `temuan_audit`
--

LOCK TABLES `temuan_audit` WRITE;
/*!40000 ALTER TABLE `temuan_audit` DISABLE KEYS */;
/*!40000 ALTER TABLE `temuan_audit` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','ketua_tim','auditor','auditee') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'auditee',
  `nama_instansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_team_id` bigint unsigned DEFAULT NULL,
  `profile_photo_path` varchar(2048) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin SPBE-SCAN','admin@spbescan.go.id','admin',NULL,NULL,'$2y$12$I/yzyylQFvRIFf6Lbx6HMe7DJ0CvFhdomrc3J444KIRsV1DORfKAe',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-11 08:33:00','2026-05-11 08:33:00'),(2,'Ketua tim Test','ketua_tim@test.go.id','ketua_tim','Diskominfo Kota Bogor',NULL,'$2y$12$LnBUoY97BHqxs4K4xkSKgO/nL6bOta5FTtYsNSQjuVLfepRJihAFq',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-20 03:59:40','2026-05-26 00:44:47'),(3,'Auditor Test','auditor@test.go.id','auditor','Diskominfo Kota Bogor',NULL,'$2y$12$shknXWtUUA.xoj14rILg/O1E9dSNVFiRY3II2OxO3lsbBIw1AbQTi',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-20 03:59:41','2026-05-27 05:21:10'),(4,'Auditee Test','auditee@test.go.id','auditee','Diskominfo Kota Bogor',NULL,'$2y$12$ALOVqINbe2jf26e/HVQKceJyb2M0i4p03dD8PmWQwodODgeGwtDNu',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-20 03:59:41','2026-05-26 02:06:28'),(5,'bang bogor','bogor@bogor.go.id','auditee','Diskominfo Kota Bogor',NULL,'$2y$12$TzaMotjBpwExPFf8C5KByuyt.v7VaoxpqsKnH0O1PnfAczQ6Q/YgK',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-22 07:30:31','2026-05-26 00:41:12'),(6,'bangauditor','bangauditor@auditor.co.id','auditor','BSSN',NULL,'$2y$12$wMHU91l8W1tWYnN2Pf4tE.AiZkr/aXrNmivtZ4HP1Vl3OwqwP9Ce2',NULL,NULL,NULL,NULL,NULL,NULL,'2026-05-27 05:17:52','2026-05-27 05:20:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-31 10:58:31
