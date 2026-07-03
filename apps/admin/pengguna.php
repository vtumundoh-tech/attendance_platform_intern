<?php
session_start();
    if (isset($_POST['submit'])) {
        
        //Include file koneksi, untuk koneksikan ke database
        include '../../config/database.php';
        
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

            $kode_admin = input($_POST["kode_admin"]);
            if ($kode_admin !== $_SESSION['kode_pengguna']) {
                header("Location:../../index.php?page=admin&pengguna=gagal");
                exit;
            }
            $username = input($_POST["username"]);
            $password_plain = input($_POST["password"]);

            if (!preg_match('/^[A-Za-z0-9_]{6,20}$/', $username)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=admin&pengguna=gagal");
                exit;
            }
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}$/', $password_plain)) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=admin&pengguna=gagal");
                exit;
            }

            $username_esc = mysqli_real_escape_string($kon, $username);
            $cek_username = mysqli_query($kon, "SELECT kode_pengguna FROM tbl_user WHERE username='$username_esc' AND kode_pengguna <> '$kode_admin'");
            if (mysqli_num_rows($cek_username) > 0) {
                mysqli_query($kon, "ROLLBACK");
                header("Location:../../index.php?page=admin&pengguna=gagal");
                exit;
            }

            $password = password_hash($password_plain, PASSWORD_BCRYPT);
            $level = "Admin";

            $sql = "UPDATE tbl_user SET 
            username='$username_esc',
            password='$password',
            level='$level'
            WHERE kode_pengguna='$kode_admin'";

            //Menyimpan ke tabel pengguna
            $setting_pengguna=mysqli_query($kon,$sql);

            if ($setting_pengguna) {
                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=admin&pengguna=berhasil");
            }
            else {
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=admin&pengguna=gagal");
            }
        }  
    }
?>

<form action="apps/admin/pengguna.php" method="post">
<?php
    include '../../config/database.php';
    $kode_pengguna = isset($_POST['kode_admin']) ? $_POST['kode_admin'] : '';
    if ($kode_pengguna !== $_SESSION['kode_pengguna']) {
        $kode_pengguna = $_SESSION['kode_pengguna'];
    }
    $kode_pengguna = mysqli_real_escape_string($kon, $kode_pengguna);
    $query = mysqli_query($kon, "SELECT * FROM tbl_user where kode_pengguna='$kode_pengguna'");
    $data = mysqli_fetch_array($query);
    if (!$data) {
        echo '<div class="alert alert-danger">Akses ditolak atau data tidak ditemukan.</div>';
        exit;
    }
    $username=$data['username'];
    $password=$data['password'];
?>

    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <input name="kode_admin" type="hidden" id="kode_admin" class="form-control" value="<?php echo htmlspecialchars($kode_pengguna); ?>"/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Username :</label>
                <input name="username" type="text" id="username" class="form-control" value="<?php echo htmlspecialchars($username); ?>"
                    placeholder="Username (6-20 huruf/angka/underscore)" pattern="^[A-Za-z0-9_]{6,20}$" title="Username harus 6-20 karakter, huruf/angka/underscore" required>
                <div id="info_username"> </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <input name="password" type="password" class="form-control" value=""
                    placeholder="Password baru" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}" title="Minimal 8 karakter, harus ada huruf besar, huruf kecil, dan angka" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="submit" id="submit" class="btn-setting btn btn-success"><i class="fa fa-edit"></i> Simpan</button>
        </div>
    </div>
</form>

<script>
    //Event pada field username, untuk mengecek ketersediaan username
    $("#username").bind('keyup', function () {

        var username = $('#username').val();

        $.ajax({
            url: 'apps/pengguna/cek_username.php',
            method: 'POST',
            data:{username:username},
            success:function(data){
                $('#info_username').show();
                $('#info_username').html(data);
            }
        }); 
    });
</script>

<script>
    // fungsi mengubah password
   $('.btn-setting').on('click',function(){
        konfirmasi=confirm("Konfirmasi Menyimpan Username dan Password?")
        if (konfirmasi){
            return true;
        }else {
            return false;
        }
    });
</script>