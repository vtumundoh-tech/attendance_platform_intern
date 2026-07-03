<?php
session_start();
include __DIR__ . '/../config/database.php';

// Ambil data JSON dari frontend
$data = json_decode(file_get_contents('php://input'), true);
$token = $data['token'];
$id_user = isset($data['id_user']) ? $data['id_user'] : null;

// Jika id_user tidak dikirim dari frontend, ambil dari session (jika user sudah login)
if (!$id_user && isset($_SESSION['kode_pengguna'])) {
    $id_user = $_SESSION['kode_pengguna'];
}

if ($token && $id_user) {
    // Cek jika token sudah ada, update id_user jika perlu
    $cek = mysqli_query($kon, "SELECT * FROM tbl_user_token WHERE token='$token'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($kon, "UPDATE tbl_user_token SET id_user='$id_user', date_created=NOW() WHERE token='$token'");
    } else {
        mysqli_query($kon, "INSERT INTO tbl_user_token (id_user, token, date_created) VALUES ('$id_user', '$token', NOW())");
    }
    echo 'OK';
} else {
    echo 'ERROR';
} 