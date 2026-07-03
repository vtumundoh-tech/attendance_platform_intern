<?php
session_start();
if (isset($_POST['ubah_absen'])) {
    include '../../config/database.php';

    function input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        mysqli_query($kon,"START TRANSACTION");
        $id_waktu=$_POST["id_waktu"];
        $mulai_absen=input($_POST["mulai_absen"]);
        $batas_absensi_pagi=input($_POST["batas_absensi_pagi"]);
        $jam_mulai_pulang=input($_POST["jam_mulai_pulang"]);
        $batas_pulang=input($_POST["batas_pulang"]);
        $kantor_latitude = isset($_POST['kantor_latitude']) ? str_replace(',', '.', trim($_POST['kantor_latitude'])) : '1.54545';
        $kantor_longitude = isset($_POST['kantor_longitude']) ? str_replace(',', '.', trim($_POST['kantor_longitude'])) : '124.92220';
        $radius_meter = isset($_POST['radius_meter']) ? max(1, (int) $_POST['radius_meter']) : 600;
        if (!is_numeric($kantor_latitude) || !is_numeric($kantor_longitude)) {
            header("Location:../../index.php?page=pengaturan&absen=gagal");
            exit;
        }

        $has_lokasi = mysqli_num_rows(mysqli_query($kon, "SHOW COLUMNS FROM tbl_setting_absensi LIKE 'kantor_latitude'")) > 0;
        $has_pulang = mysqli_num_rows(mysqli_query($kon, "SHOW COLUMNS FROM tbl_setting_absensi LIKE 'jam_mulai_pulang'")) > 0;
        
        if ($has_lokasi && $has_pulang) {
            $sql="update tbl_setting_absensi set
                mulai_absen='$mulai_absen',
                akhir_absen='$batas_absensi_pagi',
                jam_mulai_pulang='$jam_mulai_pulang',
                batas_pulang='$batas_pulang',
                kantor_latitude='$kantor_latitude',
                kantor_longitude='$kantor_longitude',
                radius_meter=$radius_meter
                where id_waktu=$id_waktu";
        } else if ($has_lokasi) {
            $sql="update tbl_setting_absensi set
                mulai_absen='$mulai_absen',
                akhir_absen='$batas_absensi_pagi',
                kantor_latitude='$kantor_latitude',
                kantor_longitude='$kantor_longitude',
                radius_meter=$radius_meter
                where id_waktu=$id_waktu";
        } else {
            $sql="update tbl_setting_absensi set
                mulai_absen='$mulai_absen',
                akhir_absen='$batas_absensi_pagi'
                where id_waktu=$id_waktu";
        }

        //Mengeksekusi query 
        $update_profil_aplikasi=mysqli_query($kon,$sql);

        //Kondisi apakah berhasil atau tidak dalam mengeksekusi query diatas
        if ($update_profil_aplikasi) {
            mysqli_query($kon,"COMMIT");
            header("Location:../../index.php?page=pengaturan&absen=berhasil");
        }
        else {
            mysqli_query($kon,"ROLLBACK");
            header("Location:../../index.php?page=pengaturan&absen=gagal");
        }
    }
}
?>