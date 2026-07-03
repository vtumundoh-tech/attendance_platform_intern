<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (isset($_POST['edit_admin'])) {
        
        //Include file koneksi, untuk koneksikan ke database
        include '../../config/database.php';
        // Integrasi logger
        include_once '../../config/logger.php';
        $logger = new Logger($kon);
        //Fungsi untuk mencegah inputan karakter yang tidak sesuai
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        //Cek apakah ada kiriman form dari method post
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            //Memulai transaksi
            mysqli_query($kon,"START TRANSACTION");
            $id_admin=input($_POST["id_admin"]);
            $safe_admin = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna']);
            $verify = mysqli_query($kon, "SELECT kode_admin FROM tbl_admin WHERE id_admin=$id_admin LIMIT 1");
            $owner = mysqli_fetch_assoc($verify);
            if (!$owner || $owner['kode_admin'] !== $safe_admin) {
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=admin&edit=gagal");
                exit;
            }
            $nama=input($_POST["nama"]);
            $nip=input($_POST["nip"]);
            $email=input($_POST["email"]);

            //Query unutk update tbl_admin
            $sql="UPDATE tbl_admin SET 
            nama='$nama', 
            nip='$nip', 
            email='$email'
            WHERE id_admin=$id_admin";
                
            //Mengeksekusi query 
            $edit_admin=mysqli_query($kon,$sql);

            //validasi jika data admin berhasil di update
            if ($edit_admin) {
                // Log edit admin berhasil
                $logger->logAdminAction($_SESSION["kode_pengguna"], 'update_mahasiswa', $id_admin, 'Edit admin/mahasiswa: ' . $nama);
                $logger->logUserActivity($_SESSION["kode_pengguna"], 'admin', 'admin_action', 'Edit admin/mahasiswa: ' . $nama);
                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=admin&edit=berhasil");
            }
            else {
            //validasi jika data admin berhasil di update
                // Log edit admin gagal
                $logger->logUserActivity($_SESSION["kode_pengguna"], 'admin', 'admin_action', 'Gagal edit admin/mahasiswa: ' . $nama);
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=admin&edit=gagal");
            }
        }
    }
?>

<?php 
    include '../../config/database.php';
    $id_admin = intval($_POST["id_admin"]);
    $kode_admin_saya = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna']);
    $sql="select * from tbl_admin where id_admin=$id_admin and kode_admin='$kode_admin_saya' limit 1";
    $hasil=mysqli_query($kon,$sql);
    $data = mysqli_fetch_array($hasil);
    if (!$data) {
        echo '<div class="alert alert-danger">Akses ditolak atau data tidak ditemukan.</div>';
        exit;
    }
?>

<form action="apps/admin/edit.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-7">
                <input type="hidden" name="id_admin" class="form-control" value="<?php echo $data['id_admin'];?>">
            <div class="form-group">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" value="<?php echo $data['nama'];?>" placeholder="Masukan Nama Lengkap" required>
            </div>
        </div>
        <div class="col-sm-5">
            <div class="form-group">
                <label>Nomor Induk Pegawai (NIP) :</label>
                <input type="text" name="nip" class="form-control"  value="<?php echo $data['nip']; ?>" placeholder="Masukan Nomor Induk Pegawai" required>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="form-group">
                <label>Email :</label>
                <input type="email" name="email" class="form-control" value="<?php echo $data['email'];?>" placeholder="Masukan Email" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_admin" id="Submit" class="btn btn-warning" ><i class="fa fa-edit"></i> Update</button>
            </div>
        </div>
    </div>
</form>

<style>
    .file {
    visibility: hidden;
    position: absolute;
    }
</style>