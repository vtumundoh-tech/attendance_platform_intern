# Dokumentasi Sistem Absensi Baru dengan Batas Keterlambatan dan Ijin Pulang Cepat

## Overview Perubahan
Sistem absensi telah diperbarui dengan fitur baru:
- **Batas Keterlambatan Pagi**: Pembedaan status antara Hadir, Terlambat, dan Tidak Hadir berdasarkan waktu
- **Absensi Pulang**: Jadwal khusus untuk absensi pulang dengan batas maksimal
- **Ijin Pulang Cepat**: Admin dapat memberikan akses khusus untuk mahasiswa absen pulang lebih awal

---

## 1. MIGRASI DATABASE

Jalankan script SQL berikut di phpMyAdmin atau CLI:
```bash
mysql -u root db_magang < database/migration_absensi_system.sql
```

**Atau secara manual di phpMyAdmin:**
1. Buka `database/migration_absensi_system.sql` di text editor
2. Copy-paste ke tab SQL di phpMyAdmin
3. Klik Execute

**Kolom yang ditambahkan:**
- `tbl_setting_absensi.jam_mulai_pulang` (TIME) - Jam mulai bisa absen pulang
- `tbl_setting_absensi.batas_pulang` (TIME) - Batas akhir absen pulang
- `tbl_absensi.jenis_absensi` (ENUM: 'masuk', 'pulang') - Tipe absensi
- `tbl_absensi.dengan_ijin_cepat` (BOOLEAN) - Apakah dengan ijin pulang cepat
- Table baru: `tbl_ijin_pulang_cepat` - Tracking ijin pulang cepat

---

## 2. LOGIKA ABSENSI PAGI

**Input Pengaturan Admin:**
- Jam Mulai Absensi Pagi: `07:00` (jam mulai absen)
- Batas Absensi Pagi: `08:00` (batas jam tanpa denda)

**Logika Status:**
| Waktu Absen | Status | Keterangan |
|-------------|--------|------------|
| 07:00 - 08:00 | ✅ Hadir | Absen tepat waktu |
| 08:01 - 08:10 | ⏱️ Terlambat | Terlambat ≤ 10 menit |
| 08:11 - 08:20 | ⏱️ Terlambat | Terlambat ≤ 20 menit |
| > 08:20 | ❌ Tidak Hadir | Terlambat > 20 menit = Tidak Hadir |

---

## 3. LOGIKA ABSENSI PULANG

**Input Pengaturan Admin:**
- Jam Mulai Absensi Pulang: `17:00` (jam mulai bisa absen pulang)
- Batas Absensi Pulang: `18:00` (batas akhir absen pulang)

**Logika Status Button:**
- **Sebelum 17:00**: Button Absen Pulang **DISABLED** (tidak bisa diklik)
- **17:00 - 18:00**: Button Absen Pulang **ENABLED** (bisa diklik)
- **Setelah 18:00**: Button Absen Pulang **DISABLED** (waktu sudah lewat)

**Kasus Khusus - Ijin Pulang Cepat:**
Jika mahasiswa ingin pulang sebelum 17:00 (misal pukul 15:00):
1. Mahasiswa tidak bisa langsung absen pulang (button disabled)
2. Mahasiswa harus mendatangi admin untuk minta ijin pulang cepat
3. Admin approve ijin di halaman **Data Absensi**
4. Setelah diapprove, button Absen Pulang akan **ENABLED** untuk mahasiswa tersebut pada hari itu
5. Mahasiswa bisa absen pulang lebih awal
6. Setelah absen, status dalam database akan mencatat `dengan_ijin_cepat = true`

---

## 4. IMPLEMENTASI DI HALAMAN ABSENSI MAHASISWA

### File yang perlu dimodifikasi:
- `apps/pengguna/mulai_absensi.php` (untuk absensi pagi)
- `apps/pengguna/mulai_kegiatan.php` atau buat baru `mulai_absensi_pulang.php` (untuk absensi pulang)

### Pseudocode Logika Absensi Pagi:
```php
$jam_mulai = "07:00"; // dari setting
$batas_pagi = "08:00"; // dari setting
$waktu_absen = date("H:i"); // waktu sekarang

$diff_minutes = (strtotime($waktu_absen) - strtotime($batas_pagi)) / 60;

if ($diff_minutes <= 0) {
    $status = "Hadir"; // absen tepat waktu
} elseif ($diff_minutes <= 10) {
    $status = "Terlambat"; // terlambat ≤ 10 menit
} elseif ($diff_minutes <= 20) {
    $status = "Terlambat"; // terlambat ≤ 20 menit
} else {
    $status = "Tidak Hadir"; // terlambat > 20 menit
}
```

### Pseudocode Logika Absensi Pulang:
```php
$jam_mulai_pulang = "17:00"; // dari setting
$batas_pulang = "18:00"; // dari setting
$waktu_sekarang = date("H:i");

// Cek apakah ada ijin pulang cepat untuk hari ini
$ijin_query = "SELECT * FROM tbl_ijin_pulang_cepat 
    WHERE id_mahasiswa = $id_mahasiswa 
    AND tanggal_ijin = CURDATE() 
    AND status = 'disetujui'";

$ada_ijin = mysqli_num_rows(mysqli_query($kon, $ijin_query)) > 0;

// Tentukan status button
if ($ada_ijin) {
    // Jika ada ijin, bisa absen kapan saja (selama belum absen hari ini)
    $button_disabled = false;
} else {
    // Jika tidak ada ijin, hanya bisa absen antara jam_mulai_pulang dan batas_pulang
    $button_disabled = !($waktu_sekarang >= $jam_mulai_pulang && $waktu_sekarang <= $batas_pulang);
}
```

---

## 5. IMPLEMENTASI DI HALAMAN DATA ABSENSI

### Button "Izinkan Pulang Cepat" di tabel absensi
Tambahkan button di kolom Aksi untuk setiap baris mahasiswa:

```html
<button class="btn btn-info btn-izin-pulang" data-id="<?php echo $id_mahasiswa; ?>">
    <i class="fa fa-clock-o"></i> Izin Pulang Cepat
</button>
```

### Modal untuk Approve Ijin Pulang Cepat
Modal akan menampilkan:
- Nama mahasiswa
- Waktu request (jika dari request mahasiswa)
- Field untuk input alasan/catatan (opsional)
- Button Approve / Tolak

### Query untuk Save Ijin:
```sql
INSERT INTO tbl_ijin_pulang_cepat 
(id_mahasiswa, tanggal_ijin, kode_admin_pemberi_ijin, status, alasan)
VALUES ($id_mahasiswa, CURDATE(), '$kode_admin_login', 'disetujui', '$alasan');
```

---

## 6. TRACKING ABSENSI PULANG DENGAN IJIN

Saat mahasiswa melakukan absensi pulang dengan ijin:
```sql
INSERT INTO tbl_absensi 
(id_mahasiswa, status, waktu, tanggal, jenis_absensi, dengan_ijin_cepat)
VALUES ($id_mahasiswa, 1, '$waktu_sekarang', CURDATE(), 'pulang', true);
```

---

## 7. INFORMASI UNTUK HALAMAN CETAK

Di halaman cetak absensi, tambahkan kolom:
- **Jenis Absensi**: Masuk / Pulang
- **Dengan Ijin Cepat**: Ya / Tidak
- **Catatan**: Jika ada ijin cepat, tampilkan nama admin yang approve

---

## 8. PERUBAHAN UI/UX

### Halaman Pengaturan Absensi (Sudah dilakukan)
✅ Update label dan deskripsi field
✅ Tambah field Jam Mulai Absensi Pulang
✅ Tambah field Batas Absensi Pulang

### Halaman Absensi Mahasiswa (Perlu dikerjakan)
- [ ] Pisahkan button Absensi Pagi dan Absensi Pulang
- [ ] Implementasi logic disable/enable button Absensi Pulang
- [ ] Tampilkan pesan jika button disabled (Belum jam absen pulang / Sudah lewat batas)
- [ ] Tampilkan info ada ijin pulang cepat jika ada

### Halaman Data Absensi (Perlu dikerjakan)
- [ ] Tambah button "Izin Pulang Cepat" di kolom Aksi
- [ ] Buat modal untuk approve ijin
- [ ] Tampilkan status ijin pulang cepat di tabel atau tooltip

---

## CATATAN PENTING

1. **Backward Compatibility**: Setting lama (hanya jam mulai dan akhir) masih akan berfungsi normal
2. **Default Values**: Jika belum ada setting baru, sistem menggunakan default:
   - Jam Mulai Pulang: 17:00
   - Batas Pulang: 18:00
3. **Timestamp**: Ijin pulang cepat disimpan dengan `CURRENT_TIMESTAMP` untuk audit trail
4. **Unique Constraint**: Satu mahasiswa hanya bisa punya 1 ijin per hari
5. **Permission**: Hanya admin yang bisa approve ijin pulang cepat
