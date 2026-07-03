# DAFTAR LENGKAP ALGORITMA YANG DIGUNAKAN DALAM APLIKASI

## 1. ALGORITMA KEAMANAN & ENKRIPSI

### 1.1. Bcrypt Password Hashing
- **Fungsi**: `password_hash($password, PASSWORD_BCRYPT)`
- **Lokasi**: 
  - `register.php` (baris 28)
  - `apps/mahasiswa/pengguna.php` (baris 19)
  - `apps/admin/pengguna.php` (baris 24)
  - `apps/pengguna/ubah_password.php` (baris 35)
- **Kegunaan**: Meng-hash password dengan algoritma bcrypt untuk keamanan

### 1.2. Bcrypt Password Verification
- **Fungsi**: `password_verify($password_plain, $hash)`
- **Lokasi**: `login.php` (baris 36)
- **Kegunaan**: Memverifikasi password yang diinput dengan hash yang tersimpan

### 1.3. Input Sanitization
- **Fungsi**: `trim()`, `stripslashes()`, `htmlspecialchars()`
- **Lokasi**: Semua file yang menerima input user
- **Kegunaan**: Membersihkan input dari karakter berbahaya (XSS prevention)

### 1.4. Prepared Statements (SQL Injection Prevention)
- **Fungsi**: `mysqli_prepare()`, `bind_param()`
- **Lokasi**: `config/logger.php`
- **Kegunaan**: Mencegah SQL injection dengan parameterized queries

---

## 2. ALGORITMA DATABASE & QUERY

### 2.1. SQL Aggregation (COUNT, SUM, MAX, MIN)
- **Fungsi**: `COUNT()`, `MAX()`, `GROUP BY`
- **Lokasi**: 
  - `apps/beranda/index.php` (statistik)
  - `register.php` (generate kode pengguna)
- **Kegunaan**: Menghitung dan mengagregasi data

### 2.2. SQL JOIN Operations
- **Jenis**: INNER JOIN, LEFT JOIN
- **Lokasi**: 
  - `login.php` (join tbl_user dengan tbl_admin/tbl_mahasiswa)
  - `config/function.php` (query absensi dan kegiatan)
- **Kegunaan**: Menggabungkan data dari beberapa tabel

### 2.3. SQL Filtering (WHERE Clause)
- **Fungsi**: WHERE dengan berbagai kondisi
- **Lokasi**: Semua file query database
- **Kegunaan**: Memfilter data berdasarkan kondisi tertentu

### 2.4. SQL Sorting (ORDER BY)
- **Fungsi**: ORDER BY dengan ASC/DESC
- **Lokasi**: 
  - `apps/beranda/index.php`
  - `config/function.php`
- **Kegunaan**: Mengurutkan hasil query

### 2.5. SQL Pagination (LIMIT & OFFSET)
- **Fungsi**: LIMIT, OFFSET
- **Lokasi**: 
  - `config/function.php` (fungsi dengan parameter limit/offset)
  - `apps/beranda/index.php` (LIMIT 5)
- **Kegunaan**: Membatasi jumlah hasil dan implementasi pagination

### 2.6. SQL Transactions
- **Fungsi**: START TRANSACTION, COMMIT, ROLLBACK
- **Lokasi**: 
  - `register.php`
  - `apps/admin/pengguna.php`
  - `apps/mahasiswa/pengguna.php`
- **Kegunaan**: Memastikan konsistensi data (atomicity)

### 2.7. SQL CASE Statements
- **Fungsi**: CASE WHEN ... THEN ... ELSE ... END
- **Lokasi**: `config/function.php` (mapping status absensi)
- **Kegunaan**: Conditional logic dalam query SQL

### 2.8. SQL COALESCE
- **Fungsi**: COALESCE()
- **Lokasi**: `config/function.php`
- **Kegunaan**: Mengembalikan nilai pertama yang tidak NULL

### 2.9. SQL GROUP_CONCAT
- **Fungsi**: GROUP_CONCAT()
- **Lokasi**: `config/function.php` (fungsi MenampilkanKegiatan)
- **Kegunaan**: Menggabungkan nilai dari beberapa baris menjadi satu string

### 2.10. SQL Date Functions
- **Fungsi**: CURDATE(), DATE_FORMAT(), DAYNAME(), DATE()
- **Lokasi**: 
  - `config/function.php`
  - `apps/beranda/index.php`
- **Kegunaan**: Manipulasi dan format tanggal

---

## 3. ALGORITMA STRING PROCESSING

### 3.1. String Concatenation
- **Fungsi**: Operator `.` (concatenation)
- **Lokasi**: Semua file PHP
- **Kegunaan**: Menggabungkan string untuk membangun HTML/query

### 3.2. String Escaping
- **Fungsi**: `htmlspecialchars()`, `addslashes()`, `mysqli_real_escape_string()`
- **Lokasi**: Semua file yang menampilkan output
- **Kegunaan**: Mencegah XSS dan SQL injection

### 3.3. String Formatting
- **Fungsi**: `nl2br()`, `str_pad()`, `mb_convert_case()`
- **Lokasi**: 
  - `register.php` (title case conversion)
  - `register.php` (str_pad untuk kode pengguna)
- **Kegunaan**: Format teks untuk tampilan

### 3.4. String Parsing
- **Fungsi**: `explode()`, `preg_split()`, `substring()`
- **Lokasi**: 
  - `register.php` (parse nama file)
  - `config/function.php` (parse kegiatan)
- **Kegunaan**: Memecah string menjadi array atau substring

### 3.5. String Validation (Regex)
- **Fungsi**: `preg_match()`
- **Lokasi**: `register.php` (validasi username, password, NIM, dll)
- **Pattern yang digunakan**:
  - Username: `/^[A-Za-z0-9_]{6,20}$/`
  - Password: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{6,}$/`
  - NIM: `/^\\d{8,12}$/`
  - No HP: `/^\\+62\\d{9,12}$/`
  - Nama: `/^[A-Za-z ]{3,}$/u`

### 3.6. String Length Validation
- **Fungsi**: `mb_strlen()`, `strlen()`
- **Lokasi**: `register.php`
- **Kegunaan**: Validasi panjang string

---

## 4. ALGORITMA DATE & TIME PROCESSING

### 4.1. Date Formatting
- **Fungsi**: `date()`, `strtotime()`, `DateTime`
- **Lokasi**: Semua file yang menangani tanggal
- **Kegunaan**: Konversi format tanggal (d/m/Y, Y-m-d, d F Y)

### 4.2. Time Formatting
- **Fungsi**: `date('H:i')`, `date('H:i:s')`
- **Lokasi**: 
  - `apps/beranda/index.php`
  - `apps/pengguna/absen.php`
- **Kegunaan**: Format waktu ke jam:menit

### 4.3. Date Comparison
- **Fungsi**: Perbandingan string tanggal, `strtotime()`
- **Lokasi**: 
  - `apps/beranda/index.php` (akhir_magang >= CURDATE())
  - `apps/pengguna/absen.php` (validasi periode)
- **Kegunaan**: Membandingkan tanggal untuk validasi

### 4.4. Day of Week Detection
- **Fungsi**: `date('N')`, `getDay()`, `DAYNAME()`
- **Lokasi**: 
  - `apps/pengguna/absen.php` (blokir weekend)
  - `config/function.php` (filter hari kerja)
- **Kegunaan**: Mendeteksi hari dalam seminggu

### 4.5. Timezone Setting
- **Fungsi**: `date_default_timezone_set()`
- **Lokasi**: 
  - `apps/pengguna/absen.php`
  - `scripts/backup_and_cleanup.php`
- **Kegunaan**: Set timezone ke Asia/Makassar

### 4.6. Date Arithmetic
- **Fungsi**: `strtotime("-3 months")`, `strtotime("-90 days")`
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Kegunaan**: Menghitung tanggal relatif untuk cleanup

---

## 5. ALGORITMA ITERATION & LOOPING

### 5.1. While Loop
- **Lokasi**: 
  - `apps/beranda/index.php` (iterasi hasil query)
  - `register.php` (iterasi universitas/departemen)
- **Kegunaan**: Iterasi data dari database

### 5.2. Foreach Loop
- **Lokasi**: 
  - `register.php` (validasi no HP)
  - `scripts/backup_and_cleanup.php` (iterasi tabel/file)
- **Kegunaan**: Iterasi array/collection

### 5.3. For Loop
- **Lokasi**: JavaScript di `apps/pengguna/absen.php` (retry GPS)
- **Kegunaan**: Iterasi dengan counter

---

## 6. ALGORITMA CONDITIONAL LOGIC

### 6.1. If-Else Statements
- **Lokasi**: Semua file PHP
- **Kegunaan**: Percabangan berdasarkan kondisi

### 6.2. Switch-Case Statements
- **Lokasi**: 
  - `config/function.php` (MendapatkanBulan, MendapatkanHari, StatusAbsensi)
- **Kegunaan**: Mapping nilai ke label

### 6.3. Ternary Operators
- **Lokasi**: 
  - `apps/beranda/index.php` (status mapping)
  - JavaScript di berbagai file
- **Kegunaan**: Conditional assignment singkat

### 6.4. Logical Operators
- **Fungsi**: `&&`, `||`, `!`, `AND`, `OR`
- **Lokasi**: Semua file
- **Kegunaan**: Kombinasi kondisi boolean

---

## 7. ALGORITMA GEOLOCATION & DISTANCE CALCULATION

### 7.1. Haversine Formula
- **Lokasi**: `apps/pengguna/mulai_absensi.php` (baris 56-64)
- **Implementasi**:
  ```php
  $earthRadius = 6371000; // Radius bumi dalam meter
  $latDiff = deg2rad($userLat - $kantorLat);
  $lngDiff = deg2rad($userLng - $kantorLng);
  $a = sin($latDiff / 2) * sin($latDiff / 2) +
       cos(deg2rad($kantorLat)) * cos(deg2rad($userLat)) *
       sin($lngDiff / 2) * sin($lngDiff / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
  $distance = $earthRadius * $c;
  ```
- **Kegunaan**: Menghitung jarak antara dua koordinat geografis (GPS)

### 7.2. Leaflet Distance Calculation
- **Lokasi**: JavaScript di `apps/pengguna/absen.php` (baris 642)
- **Fungsi**: `L.latLng().distanceTo()`
- **Kegunaan**: Menghitung jarak menggunakan library Leaflet

### 7.3. Geolocation API
- **Lokasi**: JavaScript di `apps/pengguna/absen.php`
- **Fungsi**: `navigator.geolocation.getCurrentPosition()`, `watchPosition()`
- **Kegunaan**: Mendapatkan koordinat GPS dari browser

### 7.4. GPS Accuracy Optimization
- **Lokasi**: JavaScript di `apps/pengguna/absen.php` (fungsi cobaLokasiAkurat)
- **Algoritma**: Retry dengan multiple attempts untuk mendapatkan akurasi terbaik
- **Kegunaan**: Meningkatkan akurasi GPS dengan multiple sampling

### 7.5. Radius Validation
- **Lokasi**: 
  - `apps/pengguna/mulai_absensi.php` (backend)
  - JavaScript di `apps/pengguna/absen.php` (frontend)
- **Kegunaan**: Validasi apakah user berada dalam radius 600 meter dari kantor

---

## 8. ALGORITMA FILE PROCESSING

### 8.1. File Upload Handling
- **Fungsi**: `move_uploaded_file()`, `$_FILES`
- **Lokasi**: 
  - `register.php`
  - `apps/mahasiswa/tambah.php`, `edit.php`
  - `apps/pengaturan/edit.php`
- **Kegunaan**: Upload file foto dan dokumen

### 8.2. File Extension Validation
- **Fungsi**: `explode()`, `end()`, `strtolower()`, `in_array()`
- **Lokasi**: Semua file upload
- **Kegunaan**: Validasi ekstensi file yang diizinkan

### 8.3. File Size Validation
- **Fungsi**: `filesize()`, `$_FILES['size']`
- **Lokasi**: Semua file upload
- **Kegunaan**: Validasi ukuran file (maks 2MB)

### 8.4. Base64 Encoding/Decoding
- **Fungsi**: `base64_decode()`, `toDataURL()`
- **Lokasi**: 
  - `apps/pengguna/mulai_absensi.php` (decode foto dari canvas)
  - JavaScript di `apps/pengguna/absen.php` (encode foto ke base64)
- **Kegunaan**: Konversi gambar ke/dari base64 untuk transfer

### 8.5. Unique File Naming
- **Fungsi**: `uniqid()`, `time()`
- **Lokasi**: 
  - `register.php`
  - `apps/pengguna/mulai_absensi.php`
- **Kegunaan**: Generate nama file unik untuk menghindari konflik

### 8.6. File Existence Check
- **Fungsi**: `file_exists()`, `is_file()`, `is_dir()`
- **Lokasi**: 
  - `apps/pengguna/mulai_absensi.php`
  - `scripts/backup_and_cleanup.php`
- **Kegunaan**: Cek keberadaan file/direktori

### 8.7. File Deletion
- **Fungsi**: `unlink()`
- **Lokasi**: 
  - `scripts/backup_and_cleanup.php`
  - `apps/pengaturan/edit.php` (hapus logo lama)
- **Kegunaan**: Menghapus file

### 8.8. Directory Creation
- **Fungsi**: `mkdir()`
- **Lokasi**: 
  - `apps/pengguna/mulai_absensi.php`
  - `scripts/backup_and_cleanup.php`
- **Kegunaan**: Membuat direktori jika belum ada

### 8.9. File Listing (Glob)
- **Fungsi**: `glob()`
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Kegunaan**: Mendapatkan daftar file berdasarkan pattern

### 8.10. File Modification Time
- **Fungsi**: `filemtime()`
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Kegunaan**: Mendapatkan waktu modifikasi file untuk cleanup

---

## 9. ALGORITMA IMAGE PROCESSING

### 9.1. Canvas Image Capture
- **Lokasi**: JavaScript di `apps/pengguna/absen.php`
- **Fungsi**: `canvas.getContext('2d').drawImage()`, `canvas.toDataURL()`
- **Kegunaan**: Capture gambar dari video stream kamera

### 9.2. Video Stream Handling
- **Lokasi**: JavaScript di `apps/pengguna/absen.php`
- **Fungsi**: `navigator.mediaDevices.getUserMedia()`
- **Kegunaan**: Mengakses kamera untuk foto absensi

---

## 10. ALGORITMA DATA STRUCTURE & ARRAY

### 10.1. Array Operations
- **Fungsi**: `array_map()`, `array_key_exists()`, `in_array()`, `implode()`
- **Lokasi**: 
  - `scripts/backup_and_cleanup.php`
  - `config/logger.php`
- **Kegunaan**: Manipulasi array

### 10.2. Array Sorting
- **Fungsi**: `usort()`
- **Lokasi**: `scripts/backup_and_cleanup.php` (sort backup files by date)
- **Kegunaan**: Mengurutkan array berdasarkan kriteria custom

### 10.3. Array Filtering
- **Fungsi**: `array_filter()`, `array_map()`
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Kegunaan**: Filter elemen array

---

## 11. ALGORITMA SESSION MANAGEMENT

### 11.1. Session Start/End
- **Fungsi**: `session_start()`, `session_unset()`, `session_destroy()`
- **Lokasi**: 
  - `login.php`
  - `logout.php`
- **Kegunaan**: Manajemen session user

### 11.2. Session Variable Access
- **Fungsi**: `$_SESSION[]`
- **Lokasi**: Semua file yang memerlukan autentikasi
- **Kegunaan**: Menyimpan dan mengakses data session

---

## 12. ALGORITMA AJAX & ASYNCHRONOUS

### 12.1. AJAX Requests
- **Lokasi**: JavaScript di berbagai file
- **Fungsi**: `$.ajax()`, `$.post()`, `fetch()`
- **Kegunaan**: Komunikasi asynchronous dengan server

### 12.2. JSON Encoding/Decoding
- **Fungsi**: `json_encode()`, `json_decode()`, `JSON.parse()`
- **Lokasi**: 
  - `apps/pengguna/mulai_absensi.php`
  - `api/simpan_token.php`
- **Kegunaan**: Serialisasi data untuk transfer

### 12.3. Promise/Async Handling
- **Lokasi**: JavaScript di `apps/pengguna/absen.php`
- **Fungsi**: `Promise`, `async/await`
- **Kegunaan**: Handle asynchronous operations

---

## 13. ALGORITMA BACKUP & CLEANUP

### 13.1. Database Backup Algorithm
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Algoritma**:
  1. Iterasi semua tabel
  2. Ambil struktur tabel (SHOW CREATE TABLE)
  3. Ambil data tabel (SELECT *)
  4. Generate SQL INSERT statements
  5. Tulis ke file SQL
- **Kegunaan**: Backup database sebelum cleanup

### 13.2. Data Retention Policy
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Algoritma**: 
  - Hapus data > 3 bulan
  - Hapus foto > 3 bulan
  - Hapus log > 6 bulan
  - Hapus backup > 90 hari
- **Kegunaan**: Manajemen storage dengan retention policy

### 13.3. File Cleanup by Date
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Algoritma**:
  1. Get semua file dengan glob()
  2. Cek filemtime() setiap file
  3. Bandingkan dengan cutoff date
  4. Hapus jika lebih lama dari retention period
- **Kegunaan**: Menghapus file lama otomatis

### 13.4. Size-based Cleanup
- **Lokasi**: `scripts/backup_and_cleanup.php`
- **Algoritma**:
  1. Hitung total ukuran semua backup
  2. Jika melebihi batas (100MB)
  3. Sort backup by date (oldest first)
  4. Hapus backup terlama sampai ukuran < batas
- **Kegunaan**: Kontrol ukuran total backup

---

## 14. ALGORITMA LOGGING & AUDIT

### 14.1. Structured Logging
- **Lokasi**: `config/logger.php`
- **Algoritma**: 
  - Sanitize input
  - Get IP address dan user agent
  - Insert ke tabel log dengan prepared statement
- **Kegunaan**: Audit trail untuk aktivitas user

### 14.2. IP Address Detection
- **Lokasi**: `config/logger.php` (getClientIP)
- **Algoritma**:
  1. Cek HTTP_CLIENT_IP
  2. Cek HTTP_X_FORWARDED_FOR
  3. Cek REMOTE_ADDR
  4. Validasi IP dengan filter_var()
- **Kegunaan**: Mendapatkan IP asli client (handle proxy)

### 14.3. Log Statistics Aggregation
- **Lokasi**: `config/logger.php` (getLogStatistics)
- **Algoritma**: 
  - GROUP BY activity_type dan DATE(created_at)
  - COUNT total activities
  - ORDER BY date dan count
- **Kegunaan**: Statistik aktivitas user

---

## 15. ALGORITMA VALIDATION & ERROR HANDLING

### 15.1. Input Validation
- **Lokasi**: `register.php`, semua form
- **Algoritma**: 
  - Validasi format (regex)
  - Validasi panjang
  - Validasi tipe data
  - Kumpulkan semua error
  - Tampilkan semua error sekaligus
- **Kegunaan**: Memastikan data input valid

### 15.2. Error Handling (Try-Catch)
- **Lokasi**: 
  - `login.php` (try-catch untuk logging)
  - `scripts/backup_and_cleanup.php`
- **Kegunaan**: Handle exception dengan graceful degradation

### 15.3. Duplicate Check
- **Lokasi**: 
  - `register.php` (cek username sudah ada)
  - `apps/pengguna/mulai_absensi.php` (cek sudah absen)
- **Kegunaan**: Mencegah duplikasi data

---

## 16. ALGORITMA CODE GENERATION

### 16.1. Auto-increment Code Generation
- **Lokasi**: `register.php` (baris 159-162)
- **Algoritma**:
  1. Ambil MAX(id_user) dari tabel
  2. Tambah 1
  3. Format dengan prefix 'M' dan padding 0 (str_pad)
  4. Contoh: M001, M002, M003
- **Kegunaan**: Generate kode pengguna otomatis

---

## 17. ALGORITMA UI/UX (JavaScript)

### 17.1. DOM Manipulation
- **Fungsi**: `document.getElementById()`, `querySelector()`, `innerHTML`
- **Lokasi**: JavaScript di semua file
- **Kegunaan**: Manipulasi elemen HTML

### 17.2. Event Handling
- **Fungsi**: `addEventListener()`, `onclick`, `onchange`
- **Lokasi**: JavaScript di semua file
- **Kegunaan**: Handle user interactions

### 17.3. Animation (CSS Keyframes)
- **Lokasi**: CSS di `apps/beranda/index.php`
- **Fungsi**: `@keyframes`, `animation`
- **Kegunaan**: Animasi fade-in, slide, dll

### 17.4. Timer/Interval
- **Fungsi**: `setInterval()`, `setTimeout()`
- **Lokasi**: JavaScript di `apps/pengguna/absen.php`
- **Kegunaan**: Periodic tasks, delayed execution

---

## 18. ALGORITMA SEARCH & FILTER

### 18.1. SQL LIKE Search
- **Fungsi**: `LIKE '%keyword%'`
- **Lokasi**: `config/function.php` (PencarianAbsensi, CariKegiatan)
- **Kegunaan**: Pencarian partial match

### 18.2. Date Range Filtering
- **Lokasi**: `config/function.php`
- **Fungsi**: `BETWEEN`, `>=`, `<=`
- **Kegunaan**: Filter data berdasarkan rentang tanggal

### 18.3. Dynamic Sort
- **Lokasi**: `config/function.php` (AmbilSemuaAbsensi, PencarianAbsensi)
- **Algoritma**:
  1. Validasi kolom sort (whitelist)
  2. Validasi order (asc/desc)
  3. Build ORDER BY clause dinamis
- **Kegunaan**: Sorting yang dapat dikonfigurasi user

---

## 19. ALGORITMA NOTIFICATION & TOKEN

### 19.1. Token Management
- **Lokasi**: `api/simpan_token.php`
- **Algoritma**:
  1. Cek token sudah ada
  2. Jika ada: UPDATE
  3. Jika tidak: INSERT
- **Kegunaan**: Simpan FCM token untuk push notification

---

## 20. ALGORITMA MATHEMATICAL

### 20.1. Trigonometric Functions
- **Fungsi**: `sin()`, `cos()`, `atan2()`, `sqrt()`
- **Lokasi**: `apps/pengguna/mulai_absensi.php` (Haversine formula)
- **Kegunaan**: Perhitungan jarak geografis

### 20.2. Degree to Radian Conversion
- **Fungsi**: `deg2rad()`
- **Lokasi**: `apps/pengguna/mulai_absensi.php`
- **Kegunaan**: Konversi derajat ke radian untuk perhitungan trigonometri

### 20.3. Number Formatting
- **Fungsi**: `number_format()`, `toFixed()`
- **Lokasi**: 
  - `scripts/backup_and_cleanup.php`
  - JavaScript di `apps/pengguna/absen.php`
- **Kegunaan**: Format angka untuk tampilan

---

## RINGKASAN

**Total Algoritma yang Teridentifikasi: 60+ algoritma**

Dibagi menjadi 20 kategori utama:
1. Keamanan & Enkripsi (4 algoritma)
2. Database & Query (10 algoritma)
3. String Processing (6 algoritma)
4. Date & Time Processing (6 algoritma)
5. Iteration & Looping (3 algoritma)
6. Conditional Logic (4 algoritma)
7. Geolocation & Distance (5 algoritma)
8. File Processing (10 algoritma)
9. Image Processing (2 algoritma)
10. Data Structure & Array (3 algoritma)
11. Session Management (2 algoritma)
12. AJAX & Asynchronous (3 algoritma)
13. Backup & Cleanup (4 algoritma)
14. Logging & Audit (3 algoritma)
15. Validation & Error Handling (3 algoritma)
16. Code Generation (1 algoritma)
17. UI/UX JavaScript (4 algoritma)
18. Search & Filter (3 algoritma)
19. Notification & Token (1 algoritma)
20. Mathematical (3 algoritma)

---

**Catatan Penting:**
- Algoritma **bcrypt** digunakan untuk keamanan password (hashing & verification)
- Algoritma **Haversine** digunakan untuk perhitungan jarak GPS
- Algoritma **regex** digunakan untuk validasi input
- Algoritma **backup & cleanup** digunakan untuk manajemen storage
- Algoritma **logging** digunakan untuk audit trail
