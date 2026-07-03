<?php
session_start();
    if (isset($_POST['submit'])) {
        
        //Menghubungkan ke database
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
            // Validasi regex password
            $password_plain = $_POST["password"];
            $regex_error = '';
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}$/', $password_plain)) {
                $regex_error = "<div class='alert alert-danger'><strong>Error!</strong> Password minimal 8 karakter, harus ada huruf besar, kecil, dan angka.</div>";
            }
            if ($regex_error != '') {
                header("Location:../../index.php?page=profil&password_error=" . urlencode("Password minimal 8 karakter, harus ada huruf besar, kecil, dan angka."));
                exit();
            } else {
                //Memulai transaksi
                mysqli_query($kon,"START TRANSACTION");
                //Mendapatkan kode_mahasiswa dari AJAX
                $kode_mahasiswa=input($_POST["kode_mahasiswa"]);
                //Mendapatkan input password dari form lalu di-hash dengan password_hash
                $password = password_hash(input($_POST["password"]), PASSWORD_BCRYPT);
                //Query untuk update password dari tbl_user
                $sql="UPDATE tbl_user SET password='$password' WHERE kode_pengguna='$kode_mahasiswa'";
                //Menyimpan password ke tbl_user
                $password=mysqli_query($kon,$sql);
                //Jika password berhasil di update maka halaman beralih logout
                if ($password) {
                    // Log perubahan password berhasil
                    $logger->logAuth($kode_mahasiswa, 'mahasiswa', 'password_change', 'success');
                    $logger->logUserActivity($kode_mahasiswa, 'mahasiswa', 'password_change', 'Password berhasil diubah');
                    mysqli_query($kon,"COMMIT");
                    header("Location:../../logout.php");
                }
                //Jika password gagal di update maka halaman beralih ke profil
                else {
                    // Log perubahan password gagal
                    $logger->logAuth($kode_mahasiswa, 'mahasiswa', 'password_change', 'failed');
                    $logger->logUserActivity($kode_mahasiswa, 'mahasiswa', 'password_change', 'Gagal mengubah password');
                    mysqli_query($kon,"ROLLBACK");
                    header("Location:../../index.php?page=profil&password=gagal");
                }
            }
        }
    }
?>

    <form action="apps/pengguna/ubah_password.php" method="post">

    <div class="row">
        <div class="col-sm-7">
            <div class="form-group">
                <!-- Menyimpan kode_mahasiswa dari AJAX -->
                <input name="kode_mahasiswa" type="hidden" id="kode_mahasiswa" class="form-control" value="<?php echo $_POST['kode_mahasiswa'];?>"/>
                <!-- Menyimpan kode_mahasiswa dari AJAX -->
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Password :</label>
                <div style="position:relative;">
                    <input name="password" type="password" class="form-control" id="password-input" value="" placeholder="Ganti Password?" required>
                    <span id="toggle-password" style="position:absolute;top:50%;right:12px;transform:translateY(-50%);cursor:pointer;">
                        <i class="fa fa-eye-slash"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-4">
            <button type="submit" name="submit" id="submit" class="btn-password btn btn-primary"><i class="fa fa-key"></i> Simpan</button>
        </div>
    </div>
</form>

<script>
    // fungsi mengubah password
   $('.btn-password').on('click',function(){
        konfirmasi=confirm("Konfirmasi mengubah Password?")
        if (konfirmasi){
            return true;
        }else {
            return false;
        }
    });

    // Toggle show/hide password (jQuery, agar tetap berfungsi di modal/AJAX)
    $(document).on('click', '#toggle-password', function() {
        var input = $('#password-input');
        var icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
</script>