<?php
if (isset($_POST['ubah_aplikasi'])) {
    include '../../config/database.php';

    function input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        mysqli_query($kon, "START TRANSACTION");

        $id_site          = (int) $_POST["id"];
        $nama_instansi    = input($_POST["nama_instansi"]);
        $pimpinan         = input($_POST["pimpinan"]);
        $pembimbing       = input($_POST["pembimbing"]);
        $no_telp          = input($_POST["no_telp"]);
        $alamat           = input($_POST["alamat"]);
        $website          = input($_POST["website"]);
        $logo_sebelumnya  = input($_POST["logo_sebelumnya"]);

        // ── Logo ──────────────────────────────────────────────────────────
        $logo_baru = mysqli_real_escape_string($kon, $logo_sebelumnya); // default: tetap lama

        $logo     = $_FILES['logo']['name'];
        $ekstensi_diperbolehkan = ['png', 'jpg', 'jpeg'];
        $x        = explode('.', $logo);
        $ekstensi = strtolower(end($x));
        $ukuran   = $_FILES['logo']['size'];
        $file_tmp = $_FILES['logo']['tmp_name'];

        if (!empty($logo) && $_FILES['logo']['error'] == 0) {
            if (!in_array($ekstensi, $ekstensi_diperbolehkan)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=pengaturan&edit=gagal");
                exit;
            }
            if ($ukuran > 2097152) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=pengaturan&edit=gagal");
                exit;
            }
            $nama_file_baru = 'logo_' . time() . '.' . $ekstensi;
            if (move_uploaded_file($file_tmp, 'logo/' . $nama_file_baru)) {
                if (!empty($logo_sebelumnya) && file_exists("logo/" . $logo_sebelumnya) && $logo_sebelumnya != 'logo.png') {
                    unlink("logo/" . $logo_sebelumnya);
                }
                $logo_baru = mysqli_real_escape_string($kon, $nama_file_baru);
            } else {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=pengaturan&edit=gagal");
                exit;
            }
        }

        // ── Tanda tangan pimpinan ─────────────────────────────────────────
        // HARUS di dalam blok ini agar ikut transaksi yang sama
        $ttd_pimpinan = mysqli_real_escape_string($kon, $_POST['ttd_sebelumnya'] ?? '');

        if (!empty($_FILES['ttd_pimpinan']['name']) && $_FILES['ttd_pimpinan']['error'] == 0) {
            $allowed_ttd = ['png', 'jpg', 'jpeg'];
            $ext_ttd     = strtolower(pathinfo($_FILES['ttd_pimpinan']['name'], PATHINFO_EXTENSION));
            $size_ttd    = $_FILES['ttd_pimpinan']['size'];

            if (in_array($ext_ttd, $allowed_ttd) && $size_ttd <= 2 * 1024 * 1024) {
                $folder_ttd = 'ttd/'; // relatif dari apps/pengaturan/ — sama seperti logo/

                if (!is_dir($folder_ttd)) {
                    mkdir($folder_ttd, 0755, true);
                }

                $nama_ttd = 'ttd_pimpinan_' . time() . '.' . $ext_ttd;

                if (move_uploaded_file($_FILES['ttd_pimpinan']['tmp_name'], $folder_ttd . $nama_ttd)) {
                    // Hapus file TTD lama
                    if (!empty($_POST['ttd_sebelumnya'])) {
                        $lama = $folder_ttd . $_POST['ttd_sebelumnya'];
                        if (file_exists($lama)) unlink($lama);
                    }
                    $ttd_pimpinan = mysqli_real_escape_string($kon, $nama_ttd);
                }
                // Jika move_uploaded_file gagal, $ttd_pimpinan tetap yang lama — tidak error
            }
        }

        // ── Query UPDATE — sertakan ttd_pimpinan ─────────────────────────
        $sql = "UPDATE tbl_site SET
            nama_instansi   = '$nama_instansi',
            pimpinan        = '$pimpinan',
            pembimbing      = '$pembimbing',
            no_telp         = '$no_telp',
            alamat          = '$alamat',
            website         = '$website',
            logo            = '$logo_baru',
            ttd_pimpinan    = '$ttd_pimpinan'
            WHERE id_site   = $id_site";

        if (mysqli_query($kon, $sql)) {
            mysqli_query($kon, "COMMIT");
            header("Location:../../index.php?page=pengaturan&edit=berhasil");
        } else {
            mysqli_query($kon, "ROLLBACK");
            header("Location:../../index.php?page=pengaturan&edit=gagal");
        }
        exit;
    }
}
?>