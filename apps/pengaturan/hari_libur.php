<?php
session_start();
if (!isset($_SESSION['level']) || (strtolower($_SESSION['level']) !== 'admin')) {
    header('Location:../../index.php?page=pengaturan');
    exit;
}

include '../../config/database.php';

$admin = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_libur'])) {
    $tanggal = isset($_POST['tanggal']) ? trim($_POST['tanggal']) : '';
    $keterangan = isset($_POST['keterangan']) ? mysqli_real_escape_string($kon, trim($_POST['keterangan'])) : '';

    if ($tanggal !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
        $ok = mysqli_query($kon, "INSERT INTO tbl_hari_libur (tanggal, keterangan, created_by)
            VALUES ('$tanggal', '$keterangan', '$admin')
            ON DUPLICATE KEY UPDATE keterangan = VALUES(keterangan)");
        header('Location:../../index.php?page=pengaturan&libur=' . ($ok ? 'berhasil' : 'gagal'));
        exit;
    }
    header('Location:../../index.php?page=pengaturan&libur=gagal');
    exit;
}

if (isset($_GET['hapus']) && ctype_digit((string) $_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $ok = mysqli_query($kon, "DELETE FROM tbl_hari_libur WHERE id_libur = $id");
    header('Location:../../index.php?page=pengaturan&libur=' . ($ok ? 'hapus_berhasil' : 'gagal'));
    exit;
}

header('Location:../../index.php?page=pengaturan');
exit;
