-- SQL untuk menambahkan field status_approval
-- Jalankan file ini untuk menambahkan kolom status_approval

-- Tambahkan kolom status_approval di tbl_user
ALTER TABLE `tbl_user` 
ADD COLUMN `status_approval` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER `level`,
ADD COLUMN `tanggal_registrasi` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `status_approval`,
ADD COLUMN `tanggal_approval` DATETIME NULL AFTER `tanggal_registrasi`,
ADD COLUMN `approved_by` VARCHAR(4) NULL AFTER `tanggal_approval`;

-- Tambahkan index untuk mempercepat query
ALTER TABLE `tbl_user` 
ADD INDEX `idx_status_approval` (`status_approval`),
ADD INDEX `idx_tanggal_registrasi` (`tanggal_registrasi`);

-- Update data existing menjadi approved (untuk user yang sudah ada)
UPDATE `tbl_user` SET `status_approval` = 'approved' WHERE `status_approval` IS NULL OR `status_approval` = '';
