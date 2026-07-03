<?php 
    //memulai session
    session_start();
    //Jika terdetesi ada variabel id_pengguna dalam session maka langsung arahkan ke halaman dashboard
    if  (isset($_SESSION["id_pengguna"])){
        session_unset();
        session_destroy();
    }
    //Variable pesan untuk menampilkan validasi login
    $pesan="";
    // Cek jika ada pesan sukses dari halaman register
    if(isset($_GET['pesan']) && $_GET['pesan'] == 'register_success') {
        $pesan = "<div class='alert alert-success'><strong>Success!</strong> You have registered successfully. Please log in.</div>";
    }
    // Cek jika ada pesan pending approval
    if(isset($_GET['pesan']) && $_GET['pesan'] == 'register_pending') {
        $pesan = "<div class='alert alert-info'><strong>Info!</strong> Registrasi berhasil. Akun Anda sedang menunggu persetujuan admin. Silakan login setelah akun Anda disetujui.</div>";
    }
    if(isset($_GET['pesan']) && $_GET['pesan'] == 'akun_dinonaktifkan') {
        $pesan = "<div class='alert alert-danger'><strong>Akun dinonaktifkan.</strong> Akun magang Anda telah dinonaktifkan oleh admin. Silakan hubungi administrator.</div>";
    }
    //Fungsi untuk mencegah inputan karakter yang tidak sesuai
    function input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
    }
    //Cek apakah ada kiriman form dari method post
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        //Menghubungkan database
        include "config/database.php";
        include_once "config/mahasiswa_status.php";
        // Integrasi logger
        include_once "config/logger.php";
        $logger = new Logger($kon);
        //Mengambil input username dan password dari form login
        $username = input($_POST["username"]);
        $password_plain = $_POST["password"];
        // Ambil data user berdasarkan username
        $query_user = mysqli_query($kon, "SELECT * FROM tbl_user WHERE username='".$username."' LIMIT 1");
        if ($row_user = mysqli_fetch_assoc($query_user)) {
            $hash = $row_user['password'];
            if (password_verify($password_plain, $hash)) {
                // Cek status approval untuk user mahasiswa sebelum login
                if (($row_user['level'] == 'mahasiswa' || $row_user['level'] == 'Mahasiswa') && $row_user['status_approval'] != 'approved') {
                    if ($row_user['status_approval'] == 'pending') {
                        $pesan = "<div class='alert alert-warning'><strong>Peringatan!</strong> Akun Anda masih menunggu persetujuan admin. Silakan tunggu hingga akun Anda disetujui.</div>";
                    } elseif ($row_user['status_approval'] == 'rejected') {
                        $pesan = "<div class='alert alert-danger'><strong>Ditolak!</strong> Akun Anda telah ditolak oleh admin. Silakan hubungi administrator untuk informasi lebih lanjut.</div>";
                    } else {
                        $pesan = "<div class='alert alert-warning'><strong>Peringatan!</strong> Akun Anda belum disetujui. Silakan tunggu persetujuan admin.</div>";
                    }
                } else {
                    // Status approved atau bukan mahasiswa, lanjutkan proses login
                    // Query untuk cek tbl_user yang dijoinkan dengan table tbl_admin
                $tabel_admin= "SELECT * FROM tbl_user p
                INNER JOIN tbl_admin k ON k.kode_admin=p.kode_pengguna
                WHERE username='".$username."' LIMIT 1";
                $cek_tabel_admin = mysqli_query ($kon,$tabel_admin);
                $admin = mysqli_num_rows($cek_tabel_admin);
                //Query untuk cek pada tbl_user yang dijoinkan dengan table tbl_mahasiswa
                $tabel_mahasiswa= "SELECT * FROM tbl_user p
                INNER JOIN tbl_mahasiswa m ON m.kode_mahasiswa=p.kode_pengguna
                WHERE username='".$username."' LIMIT 1";
                $cek_tabel_mahasiswa = mysqli_query ($kon,$tabel_mahasiswa);
                $mahasiswa = mysqli_num_rows($cek_tabel_mahasiswa);
                // Query untuk user biasa (hasil register)
                $tabel_user = "SELECT * FROM tbl_user WHERE username='".$username."' LIMIT 1";
                $cek_tabel_user = mysqli_query($kon, $tabel_user);
                $user_biasa = mysqli_num_rows($cek_tabel_user);
                // Cek apakah username ditemukan di admin
                $cek_username_admin = mysqli_query($kon, "SELECT * FROM tbl_user p INNER JOIN tbl_admin k ON k.kode_admin=p.kode_pengguna WHERE username='".$username."' LIMIT 1");
                $is_admin_username = mysqli_num_rows($cek_username_admin) > 0;
                // Cek apakah username ditemukan di mahasiswa
                $cek_username_mahasiswa = mysqli_query($kon, "SELECT * FROM tbl_user p INNER JOIN tbl_mahasiswa m ON m.kode_mahasiswa=p.kode_pengguna WHERE username='".$username."' LIMIT 1");
                $is_mahasiswa_username = mysqli_num_rows($cek_username_mahasiswa) > 0;
                // Kondisi jika pengguna merupakan admin
                if ($admin>0){
                    $row = mysqli_fetch_assoc($cek_tabel_admin);
                    $_SESSION["id_pengguna"]=$row["id_user"];
                    $_SESSION["kode_pengguna"]=$row["kode_pengguna"];
                    $_SESSION["nama_admin"]=$row["nama"];
                    $_SESSION["username"]=$row["username"];
                    $_SESSION["level"]=$row["level"];
                    $_SESSION["nip"]=$row["nip"];
                    // Log login admin
                    $logger->logAuth($row["kode_pengguna"], 'admin', 'login', 'success');
                    $logger->logUserActivity($row["kode_pengguna"], 'admin', 'login', 'Login berhasil sebagai admin');
                    //mengalihkan halaman ke page beranda
                    header("Location:index.php?page=beranda");
                } else if ($mahasiswa>0){
                    $row = mysqli_fetch_assoc($cek_tabel_mahasiswa);
                    // Hanya blokir login jika admin menonaktifkan (tidak_aktif). Masa habis = login tetap, fitur terbatas.
                    if (!mahasiswa_boleh_login($row)) {
                        $pesan = "<div class='alert alert-danger'><strong>Akun dinonaktifkan.</strong> Akun magang Anda telah dinonaktifkan oleh admin. Silakan hubungi administrator.</div>";
                    } else {
                        $_SESSION["id_pengguna"]=$row["id_user"];
                        $_SESSION["kode_pengguna"]=$row["kode_pengguna"];
                        $_SESSION["id_mahasiswa"]=$row["id_mahasiswa"];
                        $_SESSION["nama_mahasiswa"]=$row["nama"];
                        $_SESSION["username"]=$row["username"];
                        $_SESSION["universitas"]=$row["universitas"];
                        $_SESSION["level"]=$row["level"];
                        $_SESSION["foto"]=$row["foto"];
                        $_SESSION["nim"]=$row["nim"];
                        $_SESSION["mahasiswa_fitur_penuh"] = mahasiswa_boleh_fitur_magang_penuh($row) ? '1' : '0';
                        // Log login mahasiswa
                        $logger->logAuth($row["kode_pengguna"], 'mahasiswa', 'login', 'success');
                        $logger->logUserActivity($row["kode_pengguna"], 'mahasiswa', 'login', 'Login berhasil sebagai peserta magang');
                        //mengalihkan halaman ke page beranda
                        header("Location:index.php?page=beranda");
                    }
                } else if ($user_biasa>0){
                    $row = mysqli_fetch_assoc($cek_tabel_user);
                    $_SESSION["id_pengguna"]=$row["id_user"];
                    $_SESSION["kode_pengguna"]=$row["kode_pengguna"];
                    $_SESSION["username"]=$row["username"];
                    $_SESSION["level"]=$row["level"];
                    // Log login user biasa
                    $logger->logAuth($row["kode_pengguna"], 'mahasiswa', 'login', 'success');
                    $logger->logUserActivity($row["kode_pengguna"], 'mahasiswa', 'login', 'Login berhasil sebagai user biasa');
                    //mengalihkan halaman ke page beranda
                    header("Location:index.php?page=beranda");
                } else {
                    // Tentukan user_type yang valid untuk log
                    $log_user_type = null;
                    if ($is_admin_username) {
                        $log_user_type = 'admin';
                    } else if ($is_mahasiswa_username) {
                        $log_user_type = 'mahasiswa';
                    }
                    try {
                        $logger->logAuth($username, $log_user_type, 'login', 'failed');
                        $logger->logUserActivity($username, $log_user_type, 'login_failed', 'Percobaan login gagal dengan username: ' . $username);
                    } catch (Exception $e) {
                        error_log('LogAuth error: ' . $e->getMessage());
                    }
                    $pesan="<div class='alert alert-danger'><strong>Error!</strong> Username dan Password Salah.</div>";
                }
                }
            } else {
                // Password salah
                $pesan="<div class='alert alert-danger'><strong>Error!</strong> Username dan Password Salah.</div>";
            }
        } else {
            // Username tidak ditemukan
            $pesan="<div class='alert alert-danger'><strong>Error!</strong> Username dan Password Salah.</div>";
        }
	}
?>

<!-- Mengambil Profil Aplikasi -->
<?php
    //Menghubungkan database
    include 'config/database.php';
    //Melakukan query untuk menampilkan table tbl_site
    $query = mysqli_query($kon, "select * from tbl_site limit 1");
    //Menyimpan hasil query    
    $row = mysqli_fetch_array($query);
    //Menyimpan nama instansi dari tbl_site
    $nama_instansi=$row['nama_instansi'];
    //Menyimpan nama logo dari tbl_site
    $logo=$row['logo'];
?>
<!-- Mengambil Profil Aplikasi -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="apps/pengaturan/logo/logoAP.png">
    <title>Login | Attendance Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
        }
        body {
            min-height: 100vh;
            background: #f7f7f7;
        }
        .login-container {
            min-height: 100vh;
        }
        .carousel, .carousel-inner, .carousel-item, .carousel-item img {
            height: 100vh;
            min-height: 400px;
            object-fit: cover;
        }
        .carousel-item img {
            width: 100%;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,60,0.12);
            padding: 2.5rem 2rem 2rem 2rem;
            background: #fff;
            animation: fadeIn 0.7s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
        }
        .slider-fade {
            position: absolute;
            top: 0; right: 0; bottom: 0;
            width: 320px;
            z-index: 2;
            pointer-events: none;
            background: linear-gradient(
                to right,
                rgba(255,255,255,0) 0%,
                rgba(247,247,247,0.7) 60%,
                #f7f7f7 100%
            );
        }
        @media (max-width: 767.98px) {
            .carousel, .carousel-inner, .carousel-item, .carousel-item img {
                display: none;
            }
            .slider-fade { display: none; }
            .login-card {
                margin: 0 auto;
                box-shadow: 0 4px 16px rgba(60,60,60,0.10);
            }
        }
        .col-md-6.d-none.d-md-block.p-0 {
            position: relative;
        }
    </style>
</head>
<body>
<div class="container-fluid login-container d-flex align-items-center justify-content-center p-0">
    <div class="row w-100 g-0">
        <!-- Kiri: Slider -->
        <div class="col-md-6 d-none d-md-block p-0" style="position:relative;">
            <div id="loginCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel" data-bs-interval="3500">
                <div class="carousel-inner h-100">
                    <div class="carousel-item active h-100">
                        <img src="source/img/slide1.jpg" class="d-block w-100 h-100" alt="Slide 1">
                    </div>
                    <div class="carousel-item h-100">
                        <img src="source/img/slide2.jpg" class="d-block w-100 h-100" alt="Slide 2">
                    </div>
                    <div class="carousel-item h-100">
                        <img src="source/img/slide3.jpg" class="d-block w-100 h-100" alt="Slide 3">
                    </div>
                </div>
            </div>
            <div class="slider-fade"></div>
        </div>
        <!-- Kanan: Form Login -->
        <div class="col-md-6 d-flex align-items-center justify-content-center min-vh-100">
            <div class="login-card mx-auto">
                <div class="text-center mb-3">
                    <div class="d-flex justify-content-center align-items-center gap-3 mb-2" style="gap: 18px;">
                        <img src="apps/pengaturan/logo/logoapii.png" alt="Logo API" style="height:115px;max-width:220px;">
                        <img src="apps/pengaturan/logo/logoairport.webp" alt="InJourney Logo" style="height:100px;max-width:200px;">
                    </div>
                    <h2 class="mb-2 fw-bold" style="font-weight:400;font-size:1.5rem;">Log in to start your session.</h2>
                </div>
                <form method="post" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <!-- Pesan error/sukses tampil di sini -->
                    <?php if (!empty($pesan)) echo $pesan; ?>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">Log In</button>
                        <a href="register.php" class="btn btn-outline-primary">Register</a>
                    </div>
                </form>
                <div class="text-center mt-4 text-muted" style="font-size:0.95rem;">
                    Attendance Platform: Intern (API)<br>
                    Copyright &copy;<?php echo date('Y'); ?> Valendy Franklin Tumundoh<br>
                    All Rights Reserved
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>