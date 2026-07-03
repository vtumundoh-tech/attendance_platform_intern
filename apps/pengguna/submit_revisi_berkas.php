<?php
session_start();
include '../../config/database.php';

function input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Tolak akses selain POST dengan tombol submit_revisi
if (!isset($_POST['submit_revisi']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location:../../index.php?page=profil");
    exit;
}

// Validasi session
$kode_pengguna = isset($_SESSION["kode_pengguna"]) ? $_SESSION["kode_pengguna"] : '';
if (empty($kode_pengguna)) {
    header("Location:../../index.php");
    exit;
}

$kode_safe = mysqli_real_escape_string($kon, $kode_pengguna);

// Ambil daftar revisi aktif dari DB — hanya proses field yang memang diminta admin
$sql_cek    = "SELECT revisi_berkas FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_safe' LIMIT 1";
$hasil_cek  = mysqli_query($kon, $sql_cek);
$row_cek    = mysqli_fetch_assoc($hasil_cek);

$revisi_aktif = [];
if (!empty($row_cek['revisi_berkas'])) {
    $revisi_aktif = json_decode($row_cek['revisi_berkas'], true);
    if (!is_array($revisi_aktif)) $revisi_aktif = [];
}

if (empty($revisi_aktif)) {
    header("Location:../../index.php?page=profil");
    exit;
}

// Klasifikasi field
$text_fields  = ['nama', 'universitas', 'departemen_unitkerja', 'jurusan', 'nim',
                 'mulai_magang', 'akhir_magang', 'alamat', 'no_telp',
                 'tempat_lahir', 'tanggal_lahir', 'agama',
                 'no_hp_ortu', 'nama_pembimbing', 'no_hp_pembimbing'];
$image_fields = ['foto'];
$file_fields = ['scan_ktp_kk', 'scan_bpjs'];
$upload_dirs = [
    'scan_ktp_kk' => '../../apps/mahasiswa/ktp_mahasiswa/',
    'scan_bpjs'   => '../../apps/mahasiswa/bpjs_mahasiswa/',
];

$set_clauses = [];
$errors      = [];

mysqli_query($kon, "START TRANSACTION");

foreach ($revisi_aktif as $field) {

    // --- Field teks / date / textarea / dropdown ---
    if (in_array($field, $text_fields)) {
        if (isset($_POST[$field]) && $_POST[$field] !== '') {
            $val           = mysqli_real_escape_string($kon, input($_POST[$field]));
            $set_clauses[] = "`$field`='$val'";
        }

    // --- Upload foto profil ---
    } elseif (in_array($field, $image_fields)) {
        if (!empty($_FILES[$field]['name'])) {
            $ekstensi = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $allowed  = ['png', 'jpg', 'jpeg', 'gif'];

            if (!in_array($ekstensi, $allowed)) {
                $errors[] = "Format foto tidak valid (hanya PNG/JPG/JPEG/GIF).";
                continue;
            }

            // Ambil nama foto lama untuk dihapus setelah upload sukses
            $res_foto  = mysqli_query($kon, "SELECT foto FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_safe' LIMIT 1");
            $row_foto  = mysqli_fetch_assoc($res_foto);
            $foto_lama = $row_foto ? $row_foto['foto'] : '';

            $nama_baru  = 'foto_' . $kode_safe . '_' . time() . '.' . $ekstensi;
            $path_baru  = '../../apps/mahasiswa/foto/' . $nama_baru;

            if (move_uploaded_file($_FILES[$field]['tmp_name'], $path_baru)) {
                if (!empty($foto_lama) && $foto_lama !== 'foto_default.png') {
                    $path_lama = '../../apps/mahasiswa/foto/' . $foto_lama;
                    if (file_exists($path_lama)) unlink($path_lama);
                }
                $set_clauses[] = "`foto`='" . mysqli_real_escape_string($kon, $nama_baru) . "'";
            } else {
                $errors[] = "Gagal mengupload foto.";
            }
        }

    // --- Upload berkas scan (KTP/KK, BPJS) ---
    } elseif (in_array($field, $file_fields)) {
        if (!empty($_FILES[$field]['name'])) {
            $ekstensi = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
            $allowed  = ['png', 'jpg', 'jpeg', 'gif', 'pdf'];

            if (!in_array($ekstensi, $allowed)) {
                $errors[] = "Format berkas $field tidak valid (hanya PNG/JPG/JPEG/GIF/PDF).";
                continue;
            }

$upload_dir = isset($upload_dirs[$field]) ? $upload_dirs[$field] : '../../apps/mahasiswa/berkas/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $nama_baru = $field . '_' . $kode_safe . '_' . time() . '.' . $ekstensi;
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $upload_dir . $nama_baru)) {
                $set_clauses[] = "`$field`='" . mysqli_real_escape_string($kon, $nama_baru) . "'";
            } else {
                $errors[] = "Gagal mengupload berkas $field.";
            }
        }
    }
}

// Jika ada error upload, rollback dan redirect dengan pesan
if (!empty($errors)) {
    mysqli_query($kon, "ROLLBACK");
    $err_msg = urlencode(implode(' ', $errors));
    header("Location:../../index.php?page=profil&revisi=gagal&msg=$err_msg");
    exit;
}

if (empty($set_clauses)) {
    mysqli_query($kon, "ROLLBACK");
    header("Location:../../index.php?page=profil&revisi=gagal&msg=" . urlencode("Tidak ada data yang diproses."));
    exit;
}

// Hapus permintaan revisi setelah submit berhasil
$set_clauses[] = "revisi_berkas=NULL";
$set_clauses[] = "catatan_revisi=NULL";

$sql_update = "UPDATE tbl_mahasiswa SET " . implode(', ', $set_clauses) . " WHERE kode_mahasiswa='$kode_safe'";
$hasil      = mysqli_query($kon, $sql_update);

if ($hasil) {
    mysqli_query($kon, "COMMIT");
    header("Location:../../index.php?page=profil&revisi=berhasil");
} else {
    mysqli_query($kon, "ROLLBACK");
    header("Location:../../index.php?page=profil&revisi=gagal&msg=" . urlencode("Gagal menyimpan ke database."));
}
exit;