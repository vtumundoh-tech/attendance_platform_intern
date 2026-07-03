<?php
session_start();
if (!isset($_SESSION['level']) || (strtolower($_SESSION['level']) !== 'admin')) {
    header('Location:../../index.php?page=pengaturan');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['simpan_sertifikat'])) {
    header('Location:../../index.php?page=pengaturan');
    exit;
}

include '../../config/database.php';

$min_persen = isset($_POST['min_persentase_kehadiran']) ? (float) $_POST['min_persentase_kehadiran'] : 90;
$max_mb = isset($_POST['max_upload_banding_mb']) ? (int) $_POST['max_upload_banding_mb'] : 5;

if ($min_persen < 1 || $min_persen > 100) {
    header('Location:../../index.php?page=pengaturan&sertifikat=gagal');
    exit;
}
if ($max_mb < 1 || $max_mb > 50) {
    header('Location:../../index.php?page=pengaturan&sertifikat=gagal');
    exit;
}

$admin = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna'] ?? '');
$cek = mysqli_query($kon, 'SELECT id_setting FROM tbl_setting_sertifikat LIMIT 1');
if ($cek && mysqli_num_rows($cek) > 0) {
    $row = mysqli_fetch_assoc($cek);
    $id = (int) $row['id_setting'];
    $ok = mysqli_query($kon, "UPDATE tbl_setting_sertifikat SET
        min_persentase_kehadiran = $min_persen,
        max_upload_banding_mb = $max_mb,
        updated_at = NOW(),
        updated_by = '$admin'
        WHERE id_setting = $id");
} else {
    $ok = mysqli_query($kon, "INSERT INTO tbl_setting_sertifikat
        (min_persentase_kehadiran, max_upload_banding_mb, updated_at, updated_by)
        VALUES ($min_persen, $max_mb, NOW(), '$admin')");
}

header('Location:../../index.php?page=pengaturan&sertifikat=' . ($ok ? 'berhasil' : 'gagal'));
exit;
