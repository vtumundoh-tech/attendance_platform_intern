<?php
session_start();
if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
    echo"Tidak Memiliki Hak Akses";
    exit;
}

include '../../config/database.php';
// Integrasi logger
include_once '../../config/logger.php';
$logger = new Logger($kon);

if (isset($_POST['id_absensi'])) {
    $id_absensi = $_POST['id_absensi'];
    $query = "UPDATE tbl_absensi SET status_gps = 'valid' WHERE id_absensi = '$id_absensi'";
    $result = mysqli_query($kon, $query);
    if ($result) {
        $user_id = $_SESSION["kode_pengguna"];
        $logger->logUserActivity($user_id, 'admin', 'validasi_gps', "Memvalidasi GPS mencurigakan untuk absensi ID: $id_absensi");
        echo "success";
    } else {
        echo "error";
    }
}
?>
