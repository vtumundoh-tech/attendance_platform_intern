-- Titik kantor & radius absensi (dikelola admin di Pengaturan > Pengaturan Absensi)
-- Jalankan sekali jika kolom belum ada.

ALTER TABLE `tbl_setting_absensi`
ADD COLUMN `kantor_latitude` DECIMAL(10,7) NOT NULL DEFAULT 1.54545 COMMENT 'Lintang titik absensi' AFTER `akhir_absen`,
ADD COLUMN `kantor_longitude` DECIMAL(10,7) NOT NULL DEFAULT 124.92220 COMMENT 'Bujur titik absensi' AFTER `kantor_latitude`,
ADD COLUMN `radius_meter` INT UNSIGNED NOT NULL DEFAULT 600 COMMENT 'Jarak maksimal absen hadir (meter)' AFTER `kantor_longitude`;
