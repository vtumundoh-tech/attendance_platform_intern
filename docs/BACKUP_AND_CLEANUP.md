# 🔧 BACKUP DAN CLEANUP OTOMATIS

## 📋 DAFTAR ISI
1. [Pendahuluan](#pendahuluan)
2. [Fitur Utama](#fitur-utama)
3. [Konfigurasi](#konfigurasi)
4. [Cara Penggunaan](#cara-penggunaan)
5. [Setup Otomatis](#setup-otomatis)
6. [Monitoring](#monitoring)
7. [Troubleshooting](#troubleshooting)

## 🎯 PENDAHULUAN

Fitur backup dan cleanup otomatis dirancang untuk:
- **Menghemat ruang disk** dengan menghapus data lama
- **Mempertahankan performa** database yang optimal
- **Menyimpan backup** untuk keamanan data
- **Otomatisasi maintenance** tanpa intervensi manual

## ✅ FITUR UTAMA

### 1. **Backup Database**
- Backup semua tabel penting sebelum penghapusan
- Format SQL yang bisa di-restore
- Timestamp pada nama file backup
- Kompresi otomatis jika diperlukan

### 2. **Cleanup Data Lama**
- Hapus absensi > 3 bulan
- Hapus kegiatan > 3 bulan  
- Hapus alasan izin > 3 bulan
- Hapus log > 6 bulan

### 3. **Cleanup File Foto**
- Hapus foto absen masuk > 3 bulan
- Hapus foto absen pulang > 3 bulan
- Hapus foto profil lama > 3 bulan
- Hapus dokumen BPJS/KTP lama > 3 bulan

### 4. **Cleanup Backup Lama**
- Hapus backup > 90 hari
- Batasi ukuran total backup (100MB)
- Hapus backup terlama jika melebihi batas

### 5. **Logging & Monitoring**
- Log semua aktivitas backup/cleanup
- Statistik data yang dihapus
- Monitoring ukuran database
- Alert jika ada masalah

## ⚙️ KONFIGURASI

### File: `scripts/backup_and_cleanup.php`

```php
$config = [
    'backup_retention_days' => 90,    // Backup disimpan 90 hari
    'data_retention_months' => 3,     // Data disimpan 3 bulan
    'log_retention_months' => 6,      // Log disimpan 6 bulan
    'photo_retention_months' => 3,    // Foto disimpan 3 bulan
    'backup_dir' => __DIR__ . '/../backups/',
    'max_backup_size_mb' => 100       // Maksimal ukuran backup 100MB
];
```

### Tabel yang Di-backup:
- `tbl_absensi` - Data absensi
- `tbl_kegiatan` - Data kegiatan
- `tbl_mahasiswa` - Data mahasiswa
- `tbl_user` - Data user
- `tbl_admin` - Data admin
- `tbl_alasan` - Data alasan izin
- `tbl_setting_absensi` - Pengaturan absensi
- `tbl_site` - Pengaturan aplikasi
- `tbl_user_logs` - Log aktivitas user
- `tbl_auth_logs` - Log autentikasi
- `tbl_attendance_logs` - Log absensi
- `tbl_activity_logs` - Log kegiatan
- `tbl_profile_logs` - Log profil
- `tbl_admin_logs` - Log admin
- `tbl_system_logs` - Log sistem
- `tbl_user_token` - Token notifikasi
- `tbl_pengumuman` - Data pengumuman

## 🚀 CARA PENGGUNAAN

### 1. **Manual (Web Interface)**
```
1. Login sebagai Admin
2. Buka menu "Maintenance"
3. Klik "Jalankan Backup & Cleanup"
4. Konfirmasi aksi
5. Tunggu proses selesai
```

### 2. **Manual (Command Line)**
```bash
# Dari root aplikasi
php scripts/backup_and_cleanup.php

# Atau dengan parameter
php scripts/backup_and_cleanup.php?run=1
```

### 3. **Test Script**
```bash
# Test tanpa eksekusi
php scripts/backup_and_cleanup.php

# Test dengan eksekusi
php scripts/backup_and_cleanup.php?run=1
```

## 🔄 SETUP OTOMATIS

### Linux/Mac (Cron Job)

#### Cara 1: Setup Otomatis
```bash
# Jalankan script setup
bash scripts/setup_cron.sh
```

#### Cara 2: Manual Setup
```bash
# Edit crontab
crontab -e

# Tambahkan baris ini
0 2 1 */3 * php /path/to/scripts/backup_and_cleanup.php >> /path/to/logs/cron_backup.log 2>&1
```

### Windows (Task Scheduler)

#### Cara 1: Command Line
```cmd
schtasks /create /tn "BackupAbsensi" /tr "php C:\path\to\scripts\backup_and_cleanup.php" /sc monthly /mo 3 /d 1 /st 02:00 /f
```

#### Cara 2: GUI Task Scheduler
1. Buka Task Scheduler
2. Create Basic Task
3. Nama: "Backup Absensi"
4. Trigger: Monthly, setiap 3 bulan
5. Action: Start a program
6. Program: `php`
7. Arguments: `C:\path\to\scripts\backup_and_cleanup.php`

### Penjelasan Cron Expression
```
0 2 1 */3 * php script.php
│ │ │ │  │ │
│ │ │ │  │ └─ Hari dalam seminggu (0-7, 0=minggu)
│ │ │ │  └─── Bulan (1-12, */3=setiap 3 bulan)
│ │ │ └────── Tanggal (1-31)
│ │ └──────── Jam (0-23)
│ └────────── Menit (0-59)
└──────────── Detik (0-59)
```

## 📊 MONITORING

### 1. **Halaman Maintenance**
- Statistik ukuran database
- Jumlah data lama
- Status folder backup
- Status log file

### 2. **Log File**
```
/path/to/logs/cron_backup.log
```

### 3. **Backup Folder**
```
/path/to/backups/
├── backup_2024-01-01_02-00-00.sql
├── backup_2024-04-01_02-00-00.sql
└── backup_2024-07-01_02-00-00.sql
```

### 4. **Query Monitoring**
```sql
-- Cek ukuran database
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
FROM information_schema.tables 
WHERE table_schema = 'db_magang'
GROUP BY table_schema;

-- Cek data lama
SELECT COUNT(*) FROM tbl_absensi WHERE tanggal < DATE_SUB(NOW(), INTERVAL 3 MONTH);
SELECT COUNT(*) FROM tbl_kegiatan WHERE tanggal < DATE_SUB(NOW(), INTERVAL 3 MONTH);
SELECT COUNT(*) FROM tbl_user_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);
```

## 🔧 TROUBLESHOOTING

### 1. **Error "Permission Denied"**
```bash
# Berikan permission write pada folder
chmod 755 backups/
chmod 755 logs/
chmod 755 apps/data_absensi/foto_absen_masuk/
chmod 755 apps/data_absensi/foto_absen_pulang/
```

### 2. **Error "Database Connection Failed"**
```php
// Cek konfigurasi database di config/database.php
$host = "localhost";
$user = "root";
$password = "";
$db = "db_magang";
```

### 3. **Backup File Kosong**
```bash
# Cek apakah tabel ada
mysql -u root -p db_magang -e "SHOW TABLES;"

# Cek permission MySQL user
mysql -u root -p -e "SHOW GRANTS FOR 'user'@'localhost';"
```

### 4. **Cron Job Tidak Berjalan**
```bash
# Cek cron service
sudo service cron status

# Cek cron log
sudo tail -f /var/log/cron

# Test cron job manual
php /path/to/scripts/backup_and_cleanup.php
```

### 5. **Disk Space Penuh**
```bash
# Cek penggunaan disk
df -h

# Hapus backup lama manual
find /path/to/backups/ -name "*.sql" -mtime +90 -delete

# Hapus log lama manual
find /path/to/logs/ -name "*.log" -mtime +30 -delete
```

## 📝 BEST PRACTICES

### 1. **Sebelum Setup Otomatis**
- [ ] Test script manual terlebih dahulu
- [ ] Backup database secara manual
- [ ] Cek ruang disk yang tersedia
- [ ] Verifikasi permission folder

### 2. **Monitoring Rutin**
- [ ] Cek log file setiap minggu
- [ ] Monitor ukuran database
- [ ] Verifikasi backup file
- [ ] Cek cron job status

### 3. **Maintenance**
- [ ] Review konfigurasi setiap 6 bulan
- [ ] Update retention policy jika perlu
- [ ] Backup konfigurasi script
- [ ] Test restore backup

### 4. **Keamanan**
- [ ] Backup file tidak boleh diakses publik
- [ ] Log file tidak boleh diakses publik
- [ ] Gunakan user MySQL dengan permission minimal
- [ ] Enkripsi backup jika diperlukan

## 🎯 KESIMPULAN

Fitur backup dan cleanup otomatis memberikan:

1. **Efisiensi**: Otomatisasi maintenance
2. **Keamanan**: Backup data sebelum penghapusan
3. **Performa**: Database tetap optimal
4. **Monitoring**: Tracking aktivitas maintenance
5. **Fleksibilitas**: Konfigurasi sesuai kebutuhan

Dengan setup yang benar, aplikasi akan berjalan dengan performa optimal dan data tetap aman. 