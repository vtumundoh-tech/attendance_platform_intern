<?php
session_start();
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'mahasiswa') {
    header('Location:../../index.php?page=profil');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['ajukan_banding'])) {
    header('Location:../../index.php?page=profil');
    exit;
}

include '../../config/database.php';
include '../../config/sertifikat_helper.php';

$id_mahasiswa = (int) ($_SESSION['id_mahasiswa'] ?? 0);
$kode = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna'] ?? '');

$q = mysqli_query($kon, "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa = '$kode' LIMIT 1");
if (!$q || !($mahasiswa = mysqli_fetch_assoc($q))) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Data peserta tidak ditemukan.'));
    exit;
}

$cek_dl = sertifikat_boleh_download($kon, $mahasiswa);
if ($cek_dl['boleh']) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Anda sudah memenuhi syarat sertifikat.'));
    exit;
}

if (sertifikat_get_banding_pending($kon, $id_mahasiswa)) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Pengajuan banding sebelumnya masih diproses.'));
    exit;
}

$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
if ($catatan === '') {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Catatan keluhan wajib diisi.'));
    exit;
}

if (!isset($_FILES['file_presensi']) || $_FILES['file_presensi']['error'] !== UPLOAD_ERR_OK) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('File PDF riwayat presensi wajib diunggah.'));
    exit;
}

$setting = sertifikat_get_setting($kon);
$max_bytes = (int) $setting['max_upload_banding_mb'] * 1024 * 1024;
if ($_FILES['file_presensi']['size'] > $max_bytes) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Ukuran file melebihi batas ' . $setting['max_upload_banding_mb'] . ' MB.'));
    exit;
}

$ext = strtolower(pathinfo($_FILES['file_presensi']['name'], PATHINFO_EXTENSION));
if ($ext !== 'pdf') {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Hanya file PDF yang diperbolehkan.'));
    exit;
}

$upload_dir = __DIR__ . '/banding/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$filename = 'banding_' . $id_mahasiswa . '_' . date('YmdHis') . '.pdf';
$dest = $upload_dir . $filename;
if (!move_uploaded_file($_FILES['file_presensi']['tmp_name'], $dest)) {
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Gagal menyimpan file.'));
    exit;
}

$catatan_esc = mysqli_real_escape_string($kon, $catatan);
$filename_esc = mysqli_real_escape_string($kon, $filename);
$ok = mysqli_query($kon, "INSERT INTO tbl_pengajuan_banding
    (id_mahasiswa, catatan, file_presensi, status, tanggal_ajuan)
    VALUES ($id_mahasiswa, '$catatan_esc', '$filename_esc', 'pending', NOW())");

if (!$ok) {
    @unlink($dest);
    header('Location:../../index.php?page=profil&banding=gagal&msg=' . urlencode('Gagal menyimpan pengajuan.'));
    exit;
}

header('Location:../../index.php?page=profil&banding=berhasil');
exit;
