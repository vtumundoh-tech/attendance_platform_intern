<?php
session_start();
if (isset($_POST['ubah_fonnte'])) {
    include '../../config/database.php';
    
    // Check if level is admin
    if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
        echo"Tidak Memiliki Hak Akses";
        exit;
    }

    $target_grup = $_POST['target_grup'];
    $jadwal_tanggal = $_POST['jadwal_tanggal'];
    $jadwal_jam = $_POST['jadwal_jam'];
    $pesan_grup = $_POST['pesan_grup'];

    // Update into tbl_fonnte_setting
    // Since there's only one record, we update where id_setting = 1
    $query = "UPDATE tbl_fonnte_setting SET 
                target_grup = '$target_grup',
                jadwal_tanggal = '$jadwal_tanggal',
                jadwal_jam = '$jadwal_jam',
                pesan_grup = '$pesan_grup'
              WHERE id_setting = 1";

    $result = mysqli_query($kon, $query);

    if ($result) {
        header("Location:../../index.php?page=pengaturan&fonnte=berhasil");
    } else {
        header("Location:../../index.php?page=pengaturan&fonnte=gagal");
    }
} else {
    header("Location:../../index.php?page=pengaturan");
}
?>
