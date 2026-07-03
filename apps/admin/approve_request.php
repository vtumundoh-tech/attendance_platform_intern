<?php
// Session sudah dimulai di index.php, tidak perlu session_start() lagi
// File ini harus dijalankan SEBELUM output HTML dikirim
if (!isset($_SESSION["level"]) || ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin')){
    // Jika tidak ada akses, redirect ke halaman request
    // Karena file di-include dari index.php di root, gunakan path relatif dari root
    header("Location: index.php?page=admin&subpage=request&error=access_denied");
    exit;
}

// Path relatif dari root aplikasi
include __DIR__ . '/../../config/database.php';
if (file_exists(__DIR__ . '/../../config/logger.php')) {
    include_once __DIR__ . '/../../config/logger.php';
    $logger = new Logger($kon);
} else {
    $logger = null;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$kode_pengguna = isset($_GET['id']) ? $_GET['id'] : '';
$admin_kode = $_SESSION['kode_pengguna'];

if (empty($action) || empty($kode_pengguna)) {
    header("Location: index.php?page=admin&subpage=request&error=invalid");
    exit;
}

if ($action != 'approve' && $action != 'reject') {
    header("Location: index.php?page=admin&subpage=request&error=invalid_action");
    exit;
}

// Mulai transaksi
mysqli_query($kon, "START TRANSACTION");

// Update status approval
$status = ($action == 'approve') ? 'approved' : 'rejected';
$tanggal_approval = date('Y-m-d H:i:s');

$sql = "UPDATE tbl_user 
        SET status_approval = '$status', 
            tanggal_approval = '$tanggal_approval',
            approved_by = '$admin_kode'
        WHERE kode_pengguna = '$kode_pengguna' AND level = 'mahasiswa'";

$result = mysqli_query($kon, $sql);

if ($result) {
    // Commit transaksi
    mysqli_query($kon, "COMMIT");
    
    // Ambil data user untuk logging
    $query_user = mysqli_query($kon, "SELECT username FROM tbl_user WHERE kode_pengguna = '$kode_pengguna'");
    $user_data = mysqli_fetch_assoc($query_user);
    $username = $user_data['username'];
    
    // Log aktivitas
    if ($logger) {
        try {
            if ($action == 'approve') {
                $logger->logAdminAction($admin_kode, 'approve_registration', $kode_pengguna, "Menyetujui registrasi user: $username");
                $logger->logUserActivity($kode_pengguna, 'mahasiswa', 'registration_approved', "Registrasi disetujui oleh admin: " . $_SESSION['nama_admin']);
            } else {
                $logger->logAdminAction($admin_kode, 'reject_registration', $kode_pengguna, "Menolak registrasi user: $username");
                $logger->logUserActivity($kode_pengguna, 'mahasiswa', 'registration_rejected', "Registrasi ditolak oleh admin: " . $_SESSION['nama_admin']);
            }
        } catch (Exception $e) {
            // Jika error karena ENUM belum diupdate, gunakan log system sebagai fallback
            error_log("Log admin action error: " . $e->getMessage());
            $logger->logSystem('info', 'admin_action', "Admin $admin_kode melakukan $action untuk user $kode_pengguna: $username", null, $admin_kode);
        }
    }
    
    if ($action == 'approve') {
        header("Location: index.php?page=admin&subpage=request&approve=berhasil");
    } else {
        header("Location: index.php?page=admin&subpage=request&reject=berhasil");
    }
} else {
    // Rollback transaksi
    mysqli_query($kon, "ROLLBACK");
    
    // Log error
    if ($logger) {
        $logger->logSystem('error', 'approval', "Gagal $action registrasi untuk kode: $kode_pengguna", null, $admin_kode);
    }
    
    if ($action == 'approve') {
        header("Location: index.php?page=admin&subpage=request&approve=gagal");
    } else {
        header("Location: index.php?page=admin&subpage=request&reject=gagal");
    }
}
exit; // Pastikan tidak ada output setelah redirect
