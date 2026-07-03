<?php
    session_start();
    if (isset($_POST['simpan_kegiatan'])) {
        
        include '../../config/database.php';
        include_once '../../config/mahasiswa_status.php';
        // Integrasi logger
        include_once '../../config/logger.php';
        $logger = new Logger($kon);
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $id_mahasiswa=$_SESSION["id_mahasiswa"];
        date_default_timezone_set("Asia/Jakarta");
        $cek_m = mysqli_query($kon, "SELECT mulai_magang, akhir_magang, status_aktif FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa' LIMIT 1");
        $row_m = mysqli_fetch_assoc($cek_m);
        if (!$row_m || !mahasiswa_boleh_fitur_magang_penuh($row_m)) {
            header("Location:../../index.php?page=kegiatan&tambah=gagal");
            exit;
        }
        $kegiatan = $_POST["kegiatan"];
        $waktu_awal = $_POST["waktu_awal"];
        $waktu_akhir = $_POST["waktu_akhir"];
        $tanggal= date("Y-m-d");
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $sql = "INSERT INTO tbl_kegiatan (id_mahasiswa,kegiatan,waktu_awal,waktu_akhir,tanggal) 
        VALUES ('$id_mahasiswa','$kegiatan','$waktu_awal','$waktu_akhir','$tanggal')";

        $simpan_kegiatan=mysqli_query($kon,$sql);
        

        // validasi data
        if ($simpan_kegiatan) {
            // Logging aktivitas kegiatan
            $user_id = $_SESSION["kode_pengguna"];
            $user_type = 'mahasiswa';
            $activity_type = 'kegiatan';
            $description = "Menambahkan kegiatan baru: " . substr($kegiatan, 0, 50);
            $logger->logUserActivity($user_id, $user_type, $activity_type, $description);
            // Log tambah kegiatan berhasil
            $logger->logActivity($id_mahasiswa, 'create', mysqli_insert_id($kon), $kegiatan, $waktu_awal, $waktu_akhir, $tanggal);
            mysqli_query($kon,"COMMIT");
            header("Location:../../index.php?page=kegiatan&tambah=berhasil");
        }
        else {
            // Log tambah kegiatan gagal
            $logger->logUserActivity($id_mahasiswa, 'mahasiswa', 'kegiatan', 'Gagal menambahkan kegiatan: ' . substr($kegiatan, 0, 50));
            mysqli_query($kon,"ROlLBACK");
            header("Location:../../index.php?page=kegiatan&tambah=gagal");
        }
        }
    }
    include_once '../../config/database.php';
    include_once '../../config/mahasiswa_status.php';
    $id_mahasiswa_form = isset($_SESSION["id_mahasiswa"]) ? $_SESSION["id_mahasiswa"] : 0;
    $cek_form = mysqli_query($kon, "SELECT mulai_magang, akhir_magang, status_aktif FROM tbl_mahasiswa WHERE id_mahasiswa='" . mysqli_real_escape_string($kon, (string) $id_mahasiswa_form) . "' LIMIT 1");
    $row_form = mysqli_fetch_assoc($cek_form);
    $boleh_form_kegiatan = $row_form && mahasiswa_boleh_fitur_magang_penuh($row_form);
?>

<?php if (!$boleh_form_kegiatan): ?>
<div class="alert alert-warning"><strong>Tidak dapat menambah kegiatan.</strong> Masa magang belum berlangsung, sudah berakhir, atau akun dinonaktifkan. Anda tetap dapat melihat dan mencetak data kegiatan yang sudah ada.</div>
<?php else: ?>
<form action="apps/pengguna/mulai_kegiatan.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" class="form-control"  value="" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan :</label>
                <input type="time" name="waktu_akhir" class="form-control"  value="" required>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Kegiatan :</label>
                <textarea name="kegiatan" class="form-control" rows="3" placeholder="Masukkan Kegiatan Anda? Contoh: Senam Pagi *enter* Makan snack *enter* Ibadah *enter*. Begitu seterusnya" required></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_kegiatan" id="simpan_kegiatan" class="simpan_kegiatan btn btn-success" ><i class="fa fa-plus"></i> Simpan</button>
                <button type="reset" class="btn btn-warning" ><i class="fa fa-trash"></i> Hapus</button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

<script>
    $('#simpan_kegiatan').on('click',function(){
        konfirmasi=confirm("Yakin ingin menyimpan kegiatan ini?")
        if (konfirmasi){
            return true;
        }else {
            return false;
        }
    });
</script>