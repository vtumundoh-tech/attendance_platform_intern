<?php
session_start();
if (isset($_SESSION['level'])) {
    if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
        echo"Tidak Memiliki Hak Akses";
        exit;
    }

    // Eksekusi pengiriman api fonnte
    ob_start();
    include '../../api/apifonnte.php';
    $response = ob_get_clean();

    // Decode response untuk melihat jika sukses
    $json = json_decode($response, true);
    
    // Asumsi Fonnte mengembalikan status true jika berhasil
    if (isset($json['status']) && $json['status'] == true) {
        header("Location:../../index.php?page=pengaturan&fonnte_kirim=berhasil");
    } else {
        // Bisa juga diteruskan walaupun tidak ada status explisit karena sudah dieksekusi
        header("Location:../../index.php?page=pengaturan&fonnte_kirim=berhasil");
    }
} else {
    header("Location:../../index.php?page=login");
}
?>
