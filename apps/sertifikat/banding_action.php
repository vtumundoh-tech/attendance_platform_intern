<?php
session_start();
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') {
    header('Location:../../index.php?page=pengajuan_banding');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['aksi_banding'])) {
    header('Location:../../index.php?page=pengajuan_banding');
    exit;
}

include '../../config/database.php';
include '../../config/sertifikat_helper.php';

$id_banding = isset($_POST['id_banding']) ? (int) $_POST['id_banding'] : 0;
$aksi = $_POST['aksi_banding'];
$catatan_admin = isset($_POST['catatan_admin']) ? mysqli_real_escape_string($kon, trim($_POST['catatan_admin'])) : '';
$admin = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna'] ?? '');

$q = mysqli_query($kon, "SELECT * FROM tbl_pengajuan_banding WHERE id_banding = $id_banding LIMIT 1");
if (!$q || !($banding = mysqli_fetch_assoc($q))) {
    header('Location:../../index.php?page=pengajuan_banding&aksi=gagal');
    exit;
}

if ($banding['status'] !== 'pending') {
    header('Location:../../index.php?page=pengajuan_banding&aksi=sudah_diproses');
    exit;
}

$id_mahasiswa = (int) $banding['id_mahasiswa'];

if ($aksi === 'setujui') {
    mysqli_query($kon, 'START TRANSACTION');
    $u1 = mysqli_query($kon, "UPDATE tbl_pengajuan_banding SET
        status = 'disetujui',
        tanggal_tinjau = NOW(),
        ditinjau_oleh = '$admin',
        catatan_admin = '$catatan_admin'
        WHERE id_banding = $id_banding");
    $ket = 'Disetujui via pengajuan banding #' . $id_banding;
    $u2 = sertifikat_set_bypass($kon, $id_mahasiswa, $ket, $admin);
    if ($u1 && $u2) {
        mysqli_query($kon, 'COMMIT');
        header('Location:../../index.php?page=pengajuan_banding&aksi=berhasil');
    } else {
        mysqli_query($kon, 'ROLLBACK');
        header('Location:../../index.php?page=pengajuan_banding&aksi=gagal');
    }
    exit;
}

if ($aksi === 'tolak') {
    $ok = mysqli_query($kon, "UPDATE tbl_pengajuan_banding SET
        status = 'ditolak',
        tanggal_tinjau = NOW(),
        ditinjau_oleh = '$admin',
        catatan_admin = '$catatan_admin'
        WHERE id_banding = $id_banding");
    header('Location:../../index.php?page=pengajuan_banding&aksi=' . ($ok ? 'tolak_berhasil' : 'gagal'));
    exit;
}

if ($aksi === 'bypass_manual') {
    $ket = $catatan_admin !== '' ? $catatan_admin : 'Bypass manual oleh admin';
    $ok = sertifikat_set_bypass($kon, $id_mahasiswa, $ket, $admin);
    if ($ok && $banding['status'] === 'pending') {
        mysqli_query($kon, "UPDATE tbl_pengajuan_banding SET
            status = 'disetujui',
            tanggal_tinjau = NOW(),
            ditinjau_oleh = '$admin',
            catatan_admin = '$catatan_admin'
            WHERE id_banding = $id_banding");
    }
    header('Location:../../index.php?page=pengajuan_banding&aksi=' . ($ok ? 'bypass_berhasil' : 'gagal'));
    exit;
}

header('Location:../../index.php?page=pengajuan_banding');
exit;
