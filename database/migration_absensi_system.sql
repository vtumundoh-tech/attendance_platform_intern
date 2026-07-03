-- Migration: Update sistem absensi dengan batas keterlambatan dan ijin pulang cepat
-- Created: 2026-06-03

-- Tambah kolom baru ke tbl_setting_absensi jika belum ada
ALTER TABLE `tbl_setting_absensi` 
ADD COLUMN `jam_mulai_pulang` TIME DEFAULT '17:00:00' AFTER `akhir_absen`,
ADD COLUMN `batas_pulang` TIME DEFAULT '18:00:00' AFTER `jam_mulai_pulang`;

-- Buat table untuk tracking ijin pulang cepat mahasiswa
CREATE TABLE IF NOT EXISTS `tbl_ijin_pulang_cepat` (
  `id_ijin` INT(15) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `id_mahasiswa` INT(15) NOT NULL,
  `tanggal_ijin` DATE NOT NULL,
  `waktu_ijin_dari_admin` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `kode_admin_pemberi_ijin` VARCHAR(4) NOT NULL,
  `status` ENUM('pending', 'disetujui', 'ditolak') DEFAULT 'pending',
  `alasan` VARCHAR(255),
  FOREIGN KEY (`id_mahasiswa`) REFERENCES `tbl_mahasiswa`(`id_mahasiswa`),
  FOREIGN KEY (`kode_admin_pemberi_ijin`) REFERENCES `tbl_admin`(`kode_admin`),
  UNIQUE KEY `unique_ijin_per_hari` (`id_mahasiswa`, `tanggal_ijin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambah kolom untuk mencatat akses ijin pulang cepat yang sudah digunakan
ALTER TABLE `tbl_absensi`
ADD COLUMN `jenis_absensi` ENUM('masuk', 'pulang') DEFAULT 'masuk' AFTER `status`,
ADD COLUMN `dengan_ijin_cepat` BOOLEAN DEFAULT FALSE AFTER `jenis_absensi`;
