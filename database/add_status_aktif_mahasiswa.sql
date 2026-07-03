-- Tambah kolom status_aktif di tbl_mahasiswa
-- Mahasiswa aktif = masih dalam periode mulai_magang s/d akhir_magang
-- Admin bisa mengatur manual di Edit Data Mahasiswa

ALTER TABLE `tbl_mahasiswa`
ADD COLUMN `status_aktif` ENUM('aktif','tidak_aktif') NOT NULL DEFAULT 'aktif'
COMMENT 'Diatur admin; aktif = dalam periode magang, tidak aktif = lewat periode'
AFTER `akhir_magang`;

-- Set default untuk data lama: tidak aktif jika akhir_magang < hari ini
UPDATE `tbl_mahasiswa`
SET `status_aktif` = IF(CURDATE() > `akhir_magang`, 'tidak_aktif', 'aktif');
