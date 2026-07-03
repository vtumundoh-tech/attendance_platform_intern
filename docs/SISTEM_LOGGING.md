# SISTEM LOGGING APLIKASI ABSENSI MAHASISWA MAGANG

## 📋 DAFTAR ISI
1. [Pendahuluan](#pendahuluan)
2. [Jenis Log yang Diimplementasikan](#jenis-log-yang-diimplementasikan)
3. [Struktur Database](#struktur-database)
4. [Cara Implementasi](#cara-implementasi)
5. [Contoh Penggunaan](#contoh-penggunaan)
6. [Halaman Riwayat Log](#halaman-riwayat-log)
7. [Best Practices](#best-practices)
8. [Troubleshooting](#troubleshooting)

## 🎯 PENDAHULUAN

Sistem logging ini dirancang untuk mencatat semua aktivitas penting dalam aplikasi absensi mahasiswa magang. Tujuan utamanya adalah:

- **Audit Trail**: Mencatat semua aktivitas untuk keperluan audit
- **Keamanan**: Mendeteksi aktivitas mencurigakan
- **Monitoring**: Memantau penggunaan sistem
- **Troubleshooting**: Memudahkan debugging saat ada masalah

## 📊 JENIS LOG YANG DIIMPLEMENTASIKAN

### 1. **Log Autentikasi** (`tbl_auth_logs`)
- Login berhasil/gagal
- Logout
- Perubahan password
- Session timeout
- Percobaan login gagal

### 2. **Log Aktivitas User** (`tbl_user_logs`)
- Semua aktivitas umum user
- Akses halaman
- Upload file
- Export data
- Print laporan

### 3. **Log Absensi** (`tbl_attendance_logs`)
- Absen masuk/pulang
- Lokasi GPS
- Foto selfie
- Status kehadiran
- Alasan izin/sakit

### 4. **Log Kegiatan** (`tbl_activity_logs`)
- Input kegiatan harian
- Edit kegiatan
- Hapus kegiatan
- Waktu kegiatan

### 5. **Log Profil** (`tbl_profile_logs`)
- Update foto profil
- Perubahan data pribadi
- Update data akademik

### 6. **Log Admin** (`tbl_admin_logs`)
- CRUD mahasiswa
- Export data
- Print laporan
- Update pengaturan

### 7. **Log Sistem** (`tbl_system_logs`)
- Error/Exception
- Warning
- Info
- Critical error

## 🗄️ STRUKTUR DATABASE

### Tabel `tbl_user_logs`
```sql
CREATE TABLE `tbl_user_logs` (
  `id_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('mahasiswa','admin') NOT NULL,
  `activity_type` varchar(100) NOT NULL,
  `activity_description` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `additional_data` json,
  PRIMARY KEY (`id_log`)
);
```

### Tabel `tbl_auth_logs`
```sql
CREATE TABLE `tbl_auth_logs` (
  `id_auth_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `user_type` enum('mahasiswa','admin') NOT NULL,
  `action` enum('login','logout','password_change','login_failed','session_timeout') NOT NULL,
  `ip_address` varchar(45),
  `user_agent` text,
  `status` enum('success','failed') NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `session_duration` int(11) NULL,
  PRIMARY KEY (`id_auth_log`)
);
```

### Tabel `tbl_attendance_logs`
```sql
CREATE TABLE `tbl_attendance_logs` (
  `id_attendance_log` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `attendance_type` enum('masuk','pulang','izin','sakit') NOT NULL,
  `attendance_time` datetime NOT NULL,
  `location_lat` decimal(10,8) NULL,
  `location_lng` decimal(11,8) NULL,
  `location_address` text,
  `photo_filename` varchar(255) NULL,
  `status` enum('on_time','late','early_leave') NOT NULL,
  `reason` text NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_attendance_log`)
);
```

## 🚀 CARA IMPLEMENTASI

### 1. **Setup Database**
Jalankan file SQL untuk membuat tabel-tabel log:
```bash
mysql -u username -p database_name < database/create_log_tables.sql
```

### 2. **Include Logger Class**
Tambahkan di file yang memerlukan logging:
```php
include "config/logger.php";
$logger = new Logger($kon);
```

### 3. **Implementasi Logging**
Contoh implementasi di `login.php`:
```php
// Setelah login berhasil
if ($admin>0){
    $row = mysqli_fetch_assoc($cek_tabel_admin);
    // ... kode session yang sudah ada ...
    
    // Log login berhasil
    $logger->logAuth($row["kode_pengguna"], 'admin', 'login', 'success');
    $logger->logUserActivity($row["kode_pengguna"], 'admin', 'login', 'Login berhasil sebagai admin');
    
    header("Location:index.php?page=beranda");
} else {
    // Log login gagal
    $logger->logAuth($username, 'unknown', 'login', 'failed');
    $logger->logUserActivity($username, 'unknown', 'login_failed', 'Percobaan login gagal');
    
    $pesan="<div class='alert alert-danger'>Username dan Password Salah.</div>";
}
```

## 💡 CONTOH PENGGUNAAN

### Logging Login/Logout
```php
// Login berhasil
$logger->logAuth($user_id, 'mahasiswa', 'login', 'success');
$logger->logUserActivity($user_id, 'mahasiswa', 'login', 'Login berhasil');

// Logout
$logger->logAuth($user_id, 'mahasiswa', 'logout', 'success', $session_duration);
$logger->logUserActivity($user_id, 'mahasiswa', 'logout', 'Logout berhasil');
```

### Logging Absensi
```php
// Absen masuk
$logger->logAttendance(
    $id_mahasiswa,
    'masuk',
    date('Y-m-d H:i:s'),
    $location_lat,
    $location_lng,
    $location_address,
    $photo_filename,
    'on_time',
    null
);

$logger->logUserActivity($id_mahasiswa, 'mahasiswa', 'absensi', 'Absen masuk berhasil');
```

### Logging Kegiatan
```php
// Tambah kegiatan
$logger->logActivity(
    $id_mahasiswa,
    'create',
    $activity_id,
    $kegiatan,
    $waktu_awal,
    $waktu_akhir,
    $tanggal
);

$logger->logUserActivity($id_mahasiswa, 'mahasiswa', 'kegiatan', 'Menambah kegiatan baru');
```

### Logging Admin
```php
// Tambah mahasiswa
$logger->logAdminAction(
    $admin_id,
    'create_mahasiswa',
    $mahasiswa_id,
    'Menambahkan mahasiswa: ' . $nama_mahasiswa
);

$logger->logUserActivity($admin_id, 'admin', 'admin_action', 'Menambah mahasiswa baru');
```

### Logging Error
```php
try {
    $result = mysqli_query($kon, $sql);
    if (!$result) {
        throw new Exception("Database error: " . mysqli_error($kon));
    }
} catch (Exception $e) {
    $logger->logSystem(
        'error',
        'database',
        'Database query failed: ' . $e->getMessage(),
        $e->getTraceAsString(),
        $_SESSION["kode_pengguna"] ?? null
    );
}
```

## 📱 HALAMAN RIWAYAT LOG

### Fitur Halaman Riwayat Log:
1. **Tabel Log**: Menampilkan semua aktivitas user
2. **Filter**: Filter berdasarkan jenis aktivitas dan tanggal
3. **Pagination**: Navigasi halaman untuk data yang banyak
4. **Statistik**: Grafik aktivitas 30 hari terakhir
5. **Detail**: Informasi lengkap setiap aktivitas

### Akses Halaman:
```
index.php?page=riwayat_log
```

### Menu Navigation:
Tambahkan di sidebar untuk mahasiswa:
```php
<li><a href="index.php?page=riwayat_log"><em class="fa fa-history">&nbsp;</em> Riwayat Aktivitas</a></li>
```

## ✅ BEST PRACTICES

### 1. **Konsistensi Naming**
- Gunakan nama aktivitas yang konsisten
- Contoh: `login`, `logout`, `password_change`, `absensi`, `kegiatan`

### 2. **Informasi yang Berguna**
- Catat informasi yang relevan untuk audit
- Jangan log data sensitif (password, token)
- Sertakan context yang cukup

### 3. **Performance**
- Gunakan prepared statements
- Index pada kolom yang sering diquery
- Batasi jumlah log yang ditampilkan

### 4. **Security**
- Sanitasi semua input
- Validasi user permissions
- Enkripsi data sensitif jika diperlukan

### 5. **Maintenance**
- Backup log secara berkala
- Archive log lama
- Monitor ukuran database

## 🔧 TROUBLESHOOTING

### Masalah Umum:

#### 1. **Log tidak tersimpan**
```php
// Cek koneksi database
if (!$kon) {
    die("Database connection failed");
}

// Cek apakah tabel log sudah dibuat
$result = mysqli_query($kon, "SHOW TABLES LIKE 'tbl_user_logs'");
if (mysqli_num_rows($result) == 0) {
    die("Log tables not found");
}
```

#### 2. **Error "Class Logger not found"**
```php
// Pastikan file logger.php ada dan dapat diakses
if (!file_exists("config/logger.php")) {
    die("Logger file not found");
}

include "config/logger.php";
```

#### 3. **Performance lambat**
```php
// Tambahkan index pada kolom yang sering diquery
ALTER TABLE tbl_user_logs ADD INDEX idx_user_created (user_id, created_at);
ALTER TABLE tbl_auth_logs ADD INDEX idx_user_action (user_id, action);
```

#### 4. **Database penuh**
```php
// Buat script untuk archive log lama
// Contoh: Archive log lebih dari 1 tahun
DELETE FROM tbl_user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

## 📈 MONITORING & ANALYTICS

### Query untuk Monitoring:

#### 1. **Login Gagal dalam 24 Jam**
```sql
SELECT COUNT(*) as failed_logins 
FROM tbl_auth_logs 
WHERE action = 'login' 
AND status = 'failed' 
AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY);
```

#### 2. **Aktivitas User Terbanyak**
```sql
SELECT user_id, COUNT(*) as activity_count 
FROM tbl_user_logs 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY user_id 
ORDER BY activity_count DESC 
LIMIT 10;
```

#### 3. **Error Sistem**
```sql
SELECT log_level, COUNT(*) as error_count 
FROM tbl_system_logs 
WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
GROUP BY log_level;
```

## 🔒 KEAMANAN

### 1. **Access Control**
- Hanya admin yang bisa melihat log semua user
- User hanya bisa melihat log sendiri
- Log admin action terpisah

### 2. **Data Protection**
- Jangan log password atau data sensitif
- Enkripsi data jika diperlukan
- Backup log secara berkala

### 3. **Audit Trail**
- Semua perubahan data tercatat
- Tidak ada data yang bisa dihapus tanpa trace
- Timestamp untuk setiap aktivitas

## 📝 KESIMPULAN

Sistem logging ini memberikan kemampuan untuk:

1. **Memonitor** semua aktivitas user
2. **Mendeteksi** aktivitas mencurigakan
3. **Audit** perubahan data
4. **Troubleshoot** masalah sistem
5. **Analytics** penggunaan aplikasi

Implementasi sistem logging ini akan meningkatkan keamanan, transparansi, dan kemampuan monitoring aplikasi absensi mahasiswa magang.

---

**Catatan**: Pastikan untuk menguji sistem logging di environment development terlebih dahulu sebelum diimplementasikan di production. 