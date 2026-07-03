# Dokumentasi Sistem Approval Registrasi

## Deskripsi
Sistem approval registrasi memungkinkan admin untuk menyetujui atau menolak pendaftaran user baru sebelum mereka dapat login ke sistem.

## Fitur
1. **Registrasi dengan Status Pending**: User baru yang mendaftar akan otomatis memiliki status `pending`
2. **Halaman Request Admin**: Admin dapat melihat semua request pending
3. **Detail Pendaftaran**: Admin dapat melihat detail lengkap data pendaftaran
4. **Approve/Reject**: Admin dapat menyetujui atau menolak request
5. **Validasi Login**: User hanya bisa login jika status approval = `approved`

## Instalasi

### 1. Update Database
Jalankan file SQL untuk menambahkan kolom status approval:
```sql
-- File: database/add_approval_status.sql
```

Atau jalankan query berikut:
```sql
ALTER TABLE `tbl_user` 
ADD COLUMN `status_approval` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending' AFTER `level`,
ADD COLUMN `tanggal_registrasi` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `status_approval`,
ADD COLUMN `tanggal_approval` DATETIME NULL AFTER `tanggal_registrasi`,
ADD COLUMN `approved_by` VARCHAR(4) NULL AFTER `tanggal_approval`;

ALTER TABLE `tbl_user` 
ADD INDEX `idx_status_approval` (`status_approval`),
ADD INDEX `idx_tanggal_registrasi` (`tanggal_registrasi`);

UPDATE `tbl_user` SET `status_approval` = 'approved' WHERE `status_approval` IS NULL OR `status_approval` = '';
```

### 2. File yang Telah Dimodifikasi
- `register.php` - Set status pending saat registrasi
- `login.php` - Validasi status approval sebelum login
- `index.php` - Routing untuk halaman request

### 3. File Baru yang Dibuat
- `apps/admin/request.php` - Halaman list request pending
- `apps/admin/detail_request.php` - Halaman detail pendaftaran
- `apps/admin/approve_request.php` - Proses approve/reject

## Cara Penggunaan

### Untuk Admin

1. **Akses Halaman Request**
   - Login sebagai admin
   - Klik menu "Request Pendaftaran" di sidebar
   - Badge akan menampilkan jumlah request pending

2. **Lihat Detail Pendaftaran**
   - Klik tombol "Detail" pada request yang ingin dilihat
   - Semua data pendaftaran akan ditampilkan lengkap

3. **Approve Request**
   - Klik tombol "Accept" pada request
   - Konfirmasi approval
   - User sekarang bisa login

4. **Reject Request**
   - Klik tombol "Reject" pada request
   - Konfirmasi rejection
   - User tidak bisa login meskipun sudah register

### Untuk User

1. **Registrasi**
   - User melakukan registrasi seperti biasa
   - Setelah registrasi, akan muncul pesan bahwa akun sedang menunggu approval

2. **Login**
   - User mencoba login
   - Jika status masih `pending`: muncul pesan "Akun masih menunggu persetujuan"
   - Jika status `rejected`: muncul pesan "Akun telah ditolak"
   - Jika status `approved`: login berhasil

## Status Approval

- **pending**: Menunggu persetujuan admin
- **approved**: Disetujui, user bisa login
- **rejected**: Ditolak, user tidak bisa login

## Logging

Semua aktivitas approval/rejection akan dicatat di:
- `tbl_admin_logs` - Log aktivitas admin
- `tbl_user_logs` - Log aktivitas user

## Catatan Penting

1. User yang sudah ada di database akan otomatis di-set menjadi `approved`
2. Admin tidak bisa login jika status approval bukan `approved` (untuk keamanan)
3. Setelah approve/reject, data tidak bisa diubah kecuali admin mengubah langsung di database
