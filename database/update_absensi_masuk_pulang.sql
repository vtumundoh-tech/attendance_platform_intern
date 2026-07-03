-- SQL untuk menambah fitur absen masuk dan pulang pada tabel absensi
ALTER TABLE tbl_absensi
  ADD COLUMN waktu_masuk TIME AFTER tanggal,
  ADD COLUMN foto_masuk VARCHAR(255) AFTER waktu_masuk,
  ADD COLUMN waktu_pulang TIME AFTER foto_masuk,
  ADD COLUMN foto_pulang VARCHAR(255) AFTER waktu_pulang;

-- Catatan: Jalankan perintah ini di database Anda untuk mengaktifkan fitur absen masuk dan pulang.

-- Add waktu_masuk and waktu_pulang columns
ALTER TABLE tbl_absensi ADD COLUMN waktu_masuk time DEFAULT NULL;
ALTER TABLE tbl_absensi ADD COLUMN waktu_pulang time DEFAULT NULL;

-- Copy existing waktu values to waktu_masuk
UPDATE tbl_absensi SET waktu_masuk = waktu WHERE waktu IS NOT NULL;

-- Drop the old waktu column
ALTER TABLE tbl_absensi DROP COLUMN waktu; 