<?php
    //Memulai Session
    session_start();

    //Koneksi database
    include '../../config/database.php';
    // Integrasi logger
    include_once '../../config/logger.php';
    $logger = new Logger($kon);
    //Memulai admin
    mysqli_query($kon,"START TRANSACTION");

    $id_admin = intval($_GET['id_admin']);
    $kode_admin = $_GET['kode_admin'];
    if ($kode_admin === $_SESSION['kode_pengguna']) {
        header("Location:../../index.php?page=admin&hapus=gagal");
        exit;
    }

    $safe_kode = mysqli_real_escape_string($kon, $kode_admin);
    $verify = mysqli_query($kon, "SELECT kode_admin FROM tbl_admin WHERE id_admin=$id_admin LIMIT 1");
    $row = mysqli_fetch_assoc($verify);
    if (!$row || $row['kode_admin'] !== $safe_kode) {
        header("Location:../../index.php?page=admin&hapus=gagal");
        exit;
    }

    //Menghapus data dalam tabel admin
    $hapus_admin=mysqli_query($kon,"DELETE FROM tbl_admin WHERE id_admin='$id_admin'");
    //Menghapus data dalam tabel pengguna
    $hapus_pengguna=mysqli_query($kon,"DELETE FROM tbl_user WHERE kode_pengguna='$safe_kode'");

    //Kondisi apakah berhasil atau tidak dalam mengeksekusi query diatas
    if ($hapus_admin and $hapus_pengguna) {
        // Log hapus admin berhasil
        $logger->logAdminAction($_SESSION["kode_pengguna"], 'delete_mahasiswa', $id_admin, 'Hapus admin/mahasiswa: ' . $kode_admin);
        $logger->logUserActivity($_SESSION["kode_pengguna"], 'admin', 'admin_action', 'Hapus admin/mahasiswa: ' . $kode_admin);
        mysqli_query($kon,"COMMIT");
        header("Location:../../index.php?page=admin&hapus=berhasil");
    }
    else {
        // Log hapus admin gagal
        $logger->logUserActivity($_SESSION["kode_pengguna"], 'admin', 'admin_action', 'Gagal hapus admin/mahasiswa: ' . $kode_admin);
        mysqli_query($kon,"ROLLBACK");
        header("Location:../../index.php?page=admin&hapus=gagal");

    }

?>