<?php
/**
 * Contoh implementasi logging pada berbagai fitur aplikasi
 * File ini berisi contoh bagaimana menambahkan logging pada file-file yang sudah ada
 */

// ========================================
// 1. LOGGING PADA LOGIN.PHP
// ========================================

/*
// Tambahkan di bagian atas login.php setelah include database
include "config/logger.php";
$logger = new Logger($kon);

// Tambahkan setelah login berhasil (untuk admin)
if ($admin>0){
    $row = mysqli_fetch_assoc($cek_tabel_admin);
    // ... kode session yang sudah ada ...
    
    // Log login berhasil
    $logger->logAuth($row["kode_pengguna"], 'admin', 'login', 'success');
    $logger->logUserActivity($row["kode_pengguna"], 'admin', 'login', 'Login berhasil sebagai admin');
    
    header("Location:index.php?page=beranda");
} else if ($mahasiswa>0){
    $row = mysqli_fetch_assoc($cek_tabel_mahasiswa);
    // ... kode session yang sudah ada ...
    
    // Log login berhasil
    $logger->logAuth($row["kode_pengguna"], 'mahasiswa', 'login', 'success');
    $logger->logUserActivity($row["kode_pengguna"], 'mahasiswa', 'login', 'Login berhasil sebagai mahasiswa');
    
    header("Location:index.php?page=beranda");
} else {
    // Log login gagal
    $logger->logAuth($username, 'unknown', 'login', 'failed');
    $logger->logUserActivity($username, 'unknown', 'login_failed', 'Percobaan login gagal dengan username: ' . $username);
    
    $pesan="<div class='alert alert-danger'><strong>Error!</strong> Username dan Password Salah.</div>";
}
*/

// ========================================
// 2. LOGGING PADA LOGOUT.PHP
// ========================================

/*
// Tambahkan di logout.php
include "config/logger.php";
$logger = new Logger($kon);

// Log logout sebelum session dihapus
if (isset($_SESSION["kode_pengguna"])) {
    $user_type = strtolower($_SESSION["level"]);
    $session_duration = time() - $_SESSION['login_time']; // Perlu tambahkan login_time saat login
    
    $logger->logAuth($_SESSION["kode_pengguna"], $user_type, 'logout', 'success', $session_duration);
    $logger->logUserActivity($_SESSION["kode_pengguna"], $user_type, 'logout', 'Logout berhasil');
}

// Kemudian hapus session seperti biasa
session_unset();
session_destroy();
*/

// ========================================
// 3. LOGGING PADA UBAH PASSWORD
// ========================================

/*
// Tambahkan di apps/pengguna/ubah_password.php setelah password berhasil diupdate
if ($password) {
    // Log perubahan password
    $logger->logAuth($kode_mahasiswa, 'mahasiswa', 'password_change', 'success');
    $logger->logUserActivity($kode_mahasiswa, 'mahasiswa', 'password_change', 'Password berhasil diubah');
    
    mysqli_query($kon,"COMMIT");
    header("Location:../../logout.php");
} else {
    // Log kegagalan perubahan password
    $logger->logAuth($kode_mahasiswa, 'mahasiswa', 'password_change', 'failed');
    $logger->logUserActivity($kode_mahasiswa, 'mahasiswa', 'password_change', 'Gagal mengubah password');
    
    mysqli_query($kon,"ROLLBACK");
    header("Location:../../index.php?page=profil&password=gagal");
}
*/

// ========================================
// 4. LOGGING PADA ABSENSI
// ========================================

/*
// Tambahkan di file absensi setelah absen berhasil disimpan
// Contoh untuk absen masuk
if ($absen_masuk_berhasil) {
    $logger->logAttendance(
        $id_mahasiswa,
        'masuk',
        date('Y-m-d H:i:s'),
        $location_lat,
        $location_lng,
        $location_address,
        $photo_filename,
        $status, // 'on_time' atau 'late'
        $reason
    );
    
    $logger->logUserActivity(
        $id_mahasiswa,
        'mahasiswa',
        'absensi',
        'Absen masuk berhasil pada ' . date('H:i:s')
    );
}

// Contoh untuk absen pulang
if ($absen_pulang_berhasil) {
    $logger->logAttendance(
        $id_mahasiswa,
        'pulang',
        date('Y-m-d H:i:s'),
        $location_lat,
        $location_lng,
        $location_address,
        $photo_filename,
        $status, // 'on_time' atau 'early_leave'
        null
    );
    
    $logger->logUserActivity(
        $id_mahasiswa,
        'mahasiswa',
        'absensi',
        'Absen pulang berhasil pada ' . date('H:i:s')
    );
}
*/

// ========================================
// 5. LOGGING PADA KEGIATAN
// ========================================

/*
// Tambahkan di apps/pengguna/mulai_kegiatan.php setelah kegiatan berhasil disimpan
if ($simpan_kegiatan) {
    $logger->logActivity(
        $id_mahasiswa,
        'create',
        mysqli_insert_id($kon), // ID kegiatan yang baru dibuat
        $kegiatan,
        $waktu_awal,
        $waktu_akhir,
        $tanggal
    );
    
    $logger->logUserActivity(
        $id_mahasiswa,
        'mahasiswa',
        'kegiatan',
        'Menambahkan kegiatan baru: ' . substr($kegiatan, 0, 50) . '...'
    );
    
    mysqli_query($kon,"COMMIT");
    header("Location:../../index.php?page=kegiatan&tambah=berhasil");
} else {
    $logger->logUserActivity(
        $id_mahasiswa,
        'mahasiswa',
        'kegiatan',
        'Gagal menambahkan kegiatan: ' . substr($kegiatan, 0, 50) . '...'
    );
    
    mysqli_query($kon,"ROLLBACK");
    header("Location:../../index.php?page=kegiatan&tambah=gagal");
}
*/

// ========================================
// 6. LOGGING PADA ADMIN
// ========================================

/*
// Contoh untuk admin menambah mahasiswa
if ($tambah_mahasiswa_berhasil) {
    $logger->logAdminAction(
        $_SESSION["kode_pengguna"],
        'create_mahasiswa',
        $kode_mahasiswa_baru,
        'Menambahkan mahasiswa baru: ' . $nama_mahasiswa
    );
    
    $logger->logUserActivity(
        $_SESSION["kode_pengguna"],
        'admin',
        'admin_action',
        'Menambahkan mahasiswa: ' . $nama_mahasiswa
    );
}

// Contoh untuk admin export data
if ($export_berhasil) {
    $logger->logAdminAction(
        $_SESSION["kode_pengguna"],
        'export_data',
        null,
        'Export data absensi periode ' . $periode_awal . ' - ' . $periode_akhir
    );
    
    $logger->logUserActivity(
        $_SESSION["kode_pengguna"],
        'admin',
        'export',
        'Export data absensi periode ' . $periode_awal . ' - ' . $periode_akhir
    );
}
*/

// ========================================
// 7. LOGGING PADA PERUBAHAN PROFIL
// ========================================

/*
// Contoh untuk update foto profil
if ($update_foto_berhasil) {
    $logger->logProfileChange(
        $kode_mahasiswa,
        'update_photo',
        'foto',
        $foto_lama,
        $foto_baru
    );
    
    $logger->logUserActivity(
        $kode_mahasiswa,
        'mahasiswa',
        'profil',
        'Mengubah foto profil'
    );
}

// Contoh untuk update data pribadi
if ($update_data_berhasil) {
    $logger->logProfileChange(
        $kode_mahasiswa,
        'update_personal_data',
        'alamat',
        $alamat_lama,
        $alamat_baru
    );
    
    $logger->logUserActivity(
        $kode_mahasiswa,
        'mahasiswa',
        'profil',
        'Mengubah data alamat'
    );
}
*/

// ========================================
// 8. LOGGING SISTEM ERROR
// ========================================

/*
// Contoh untuk menangkap error sistem
try {
    // Kode yang mungkin error
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
    
    // Tampilkan pesan error yang user-friendly
    echo "<div class='alert alert-danger'>Terjadi kesalahan sistem. Silakan coba lagi.</div>";
}
*/

// ========================================
// 9. FUNGSI HELPER UNTUK LOGGING
// ========================================

/**
 * Fungsi helper untuk logging yang lebih mudah digunakan
 */
function logUserAction($user_id, $user_type, $action, $description = '') {
    global $logger;
    if ($logger) {
        $logger->logUserActivity($user_id, $user_type, $action, $description);
    }
}

function logAuthAction($user_id, $user_type, $action, $status = 'success') {
    global $logger;
    if ($logger) {
        $logger->logAuth($user_id, $user_type, $action, $status);
    }
}

function logSystemError($message, $category = 'general') {
    global $logger;
    if ($logger) {
        $logger->logSystem('error', $category, $message);
    }
}

// ========================================
// 10. CONTOH PENGGUNAAN DI FILE LAIN
// ========================================

/*
// Di file login.php
include "config/logger.php";
$logger = new Logger($kon);

// Setelah login berhasil
logAuthAction($username, 'mahasiswa', 'login', 'success');
logUserAction($username, 'mahasiswa', 'login', 'Login berhasil');

// Setelah login gagal
logAuthAction($username, 'unknown', 'login', 'failed');
logUserAction($username, 'unknown', 'login_failed', 'Percobaan login gagal');

// Di file absensi
logUserAction($id_mahasiswa, 'mahasiswa', 'absensi', 'Absen masuk pada ' . date('H:i:s'));

// Di file kegiatan
logUserAction($id_mahasiswa, 'mahasiswa', 'kegiatan', 'Menambah kegiatan baru');

// Di file admin
logUserAction($admin_id, 'admin', 'admin_action', 'Menambah mahasiswa baru');
*/
?> 