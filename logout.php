<?php
    //Memulai session
    session_start();
    //Set session
    $id_pengguna=$_SESSION['id_pengguna'];
    // Integrasi logger
    include_once "config/database.php";
    include_once "config/logger.php";
    $logger = new Logger($kon);
    if (isset($_SESSION['kode_pengguna']) && isset($_SESSION['level'])) {
        $user_type = strtolower($_SESSION['level']);
        $logger->logAuth($_SESSION['kode_pengguna'], $user_type, 'logout', 'success');
        $logger->logUserActivity($_SESSION['kode_pengguna'], $user_type, 'logout', 'Logout berhasil');
    }
    $_SESSION['id_pengguna']='';
    $_SESSION['kode_pengguna']='';
    $_SESSION['nama_pengguna']='';
    $_SESSION['username']='';
    $_SESSION['level']='';
    $_SESSION['foto']='';
   
    //Hapus session
    unset($_SESSION['id_pengguna']);
    unset($_SESSION['kode_pengguna']);
    unset($_SESSION['nama_pengguna']);
    unset($_SESSION['username']);
    unset($_SESSION['level']);
    unset($_SESSION['foto']);

    session_unset();
    session_destroy();

    header('Location:login.php');
?>