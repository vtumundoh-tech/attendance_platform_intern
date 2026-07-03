-- Modul Sertifikat Magang: pengaturan, hari libur, bypass, pengajuan banding

CREATE TABLE IF NOT EXISTS `tbl_setting_sertifikat` (
  `id_setting` int NOT NULL AUTO_INCREMENT,
  `min_persentase_kehadiran` decimal(5,2) NOT NULL DEFAULT 90.00,
  `max_upload_banding_mb` int NOT NULL DEFAULT 5,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_setting`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_setting_sertifikat` (`min_persentase_kehadiran`, `max_upload_banding_mb`)
SELECT 90.00, 5
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `tbl_setting_sertifikat` LIMIT 1);

CREATE TABLE IF NOT EXISTS `tbl_hari_libur` (
  `id_libur` int NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_libur`),
  UNIQUE KEY `uq_tanggal_libur` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_sertifikat_bypass` (
  `id_bypass` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bypass`),
  KEY `idx_bypass_mahasiswa` (`id_mahasiswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_pengajuan_banding` (
  `id_banding` int NOT NULL AUTO_INCREMENT,
  `id_mahasiswa` int NOT NULL,
  `catatan` text NOT NULL,
  `file_presensi` varchar(255) NOT NULL,
  `status` enum('pending','disetujui','ditolak') NOT NULL DEFAULT 'pending',
  `tanggal_ajuan` datetime DEFAULT CURRENT_TIMESTAMP,
  `tanggal_tinjau` datetime DEFAULT NULL,
  `ditinjau_oleh` varchar(50) DEFAULT NULL,
  `catatan_admin` text DEFAULT NULL,
  PRIMARY KEY (`id_banding`),
  KEY `idx_banding_mahasiswa` (`id_mahasiswa`),
  KEY `idx_banding_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
