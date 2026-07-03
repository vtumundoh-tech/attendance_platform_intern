<?php 
    //memulai session
    session_start();
    if (!isset($_SESSION['register_draft'])) {
        $_SESSION['register_draft'] = [];
    }
    function register_field($name, $default = '') {
        if (isset($_POST[$name])) {
            return htmlspecialchars((string) $_POST[$name], ENT_QUOTES, 'UTF-8');
        }
        if (!empty($_SESSION['register_draft'][$name])) {
            return htmlspecialchars((string) $_SESSION['register_draft'][$name], ENT_QUOTES, 'UTF-8');
        }
        return $default;
    }
    function register_save_draft() {
        $_SESSION['register_draft'] = $_POST;
        unset($_SESSION['register_draft']['password']);
    }
    //Jika terdetesi ada variabel id_pengguna dalam session maka langsung arahkan ke halaman dashboard
    if  (isset($_SESSION["id_pengguna"])){
        session_unset();
        session_destroy();
    }
    //Variable pesan untuk menampilkan validasi register
    $pesan="";
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
        // Integrasi logger
        include_once "config/logger.php";
        $logger = new Logger($kon);
        //Mengambil input username dan password dari form register
        $username = input($_POST["username"]);
        $password_plain = $_POST["password"];
        $password = password_hash($password_plain, PASSWORD_BCRYPT); //hash yang dipakai md5
        $nama = input($_POST["nama"]);
        $universitas = input($_POST["universitas"]);
        $departemen_unitkerja = input($_POST["departemen_unitkerja"]);
        $jurusan = input($_POST["jurusan"]);
        $nim = input($_POST["nim"]);
        // Tanggal registrasi diset otomatis menggunakan tanggal dan jam server saat ini
        $tanggal_registrasi = date('Y-m-d H:i:s');
        $mulai_magang = input($_POST["mulai_magang"]);
        $akhir_magang = input($_POST["akhir_magang"]);
        $alamat = input($_POST["alamat"]);
        // Ambil input no hp tanpa +62, lalu gabungkan +62
        $no_telp_input = input($_POST["no_telp"]);
        $no_telp = "+62" . $no_telp_input;
        $tempat_lahir = input($_POST["tempat_lahir"]);
        $tanggal_lahir = input($_POST["tanggal_lahir"]);
        $agama = input($_POST["agama"]);
        $no_hp_ortu_input = input($_POST["no_hp_ortu"]);
        $no_hp_ortu = "+62" . $no_hp_ortu_input;
        $nama_pembimbing = input($_POST["nama_pembimbing"]);
        $no_hp_pembimbing_input = input($_POST["no_hp_pembimbing"]);
        $no_hp_pembimbing = "+62" . $no_hp_pembimbing_input;
        $foto = 'foto_default.png';

        // --- Validasi Regex dan Title Case ---
        $regex_errors = [];
        // Username: 6-20 karakter, huruf/angka/underscore
        if (!preg_match('/^[A-Za-z0-9_]{6,20}$/', $username)) {
            $regex_errors[] = "Username hanya boleh huruf, angka, underscore, 6-20 karakter.";
        }
        // Password: min 8 karakter, ada huruf besar, kecil, angka
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d).{8,}$/', $password_plain)) {
            $regex_errors[] = "Password minimal 8 karakter, harus ada huruf besar, huruf kecil, dan angka.";
        }
        // NIM: 8–15 digit (sama dengan batas form)
        if (!preg_match('/^\d{8,15}$/', $nim)) {
            $regex_errors[] = "NIM wajib 8–15 digit angka saja (tanpa spasi/huruf).";
        }
        // No HP: +62 diikuti 9-12 digit
        foreach ([['no_telp', $no_telp], ['no_hp_ortu', $no_hp_ortu], ['no_hp_pembimbing', $no_hp_pembimbing]] as $hp) {
            if (!preg_match('/^\\+62\\d{9,12}$/', $hp[1])) {
                $regex_errors[] = ucfirst(str_replace('_',' ',$hp[0])) . " harus diawali +62 dan diikuti 9-12 digit angka.";
            }
        }
        // Nama: huruf dan spasi, min 3 karakter
        if (!preg_match('/^[A-Za-z ]{3,}$/u', $nama)) {
            $regex_errors[] = "Nama hanya boleh huruf dan spasi, minimal 3 karakter.";
        }
        // Jurusan: huruf/angka/spasi/dash, min 2 karakter
        if (!preg_match('/^[A-Za-z0-9 \-]{2,}$/u', $jurusan)) {
            $regex_errors[] = "Jurusan hanya boleh huruf, angka, spasi, dan tanda strip, minimal 2 karakter.";
        }
        // Alamat: min 5 karakter
        if (mb_strlen($alamat) < 5) {
            $regex_errors[] = "Alamat minimal 5 karakter.";
        }
        // Tempat Lahir: huruf dan spasi, min 2 karakter
        if (!preg_match('/^[A-Za-z ]{2,}$/u', $tempat_lahir)) {
            $regex_errors[] = "Tempat Lahir hanya boleh huruf dan spasi, minimal 2 karakter.";
        }
        // Nama Pembimbing: huruf dan spasi, min 3 karakter
        if (!preg_match('/^[A-Za-z ]{3,}$/u', $nama_pembimbing)) {
            $regex_errors[] = "Nama Pembimbing hanya boleh huruf dan spasi, minimal 3 karakter.";
        }
        // Title Case otomatis untuk input teks (kecuali username, password, nim, no hp, alamat)
        function to_title_case($str) {
            return mb_convert_case($str, MB_CASE_TITLE, "UTF-8");
        }
        $nama = to_title_case($nama);
        $jurusan = to_title_case($jurusan);
        $tempat_lahir = to_title_case($tempat_lahir);
        $nama_pembimbing = to_title_case($nama_pembimbing);
        $departemen_unitkerja = to_title_case($departemen_unitkerja);
        // Jika ada error regex, tampilkan pesan dan hentikan proses
        if (count($regex_errors) > 0) {
            register_save_draft();
            $pesan = "<div class='alert alert-danger'><strong>Error!</strong><ul><li>".implode("</li><li>", $regex_errors)."</li></ul></div>";
        } else {
            // Upload foto
            if(isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $ekstensi_diperbolehkan = array('png','jpg','jpeg','gif');
                $nama_foto = $_FILES['foto']['name'];
                $x = explode('.', $nama_foto);
                $ekstensi = strtolower(end($x));
                $ukuran = $_FILES['foto']['size'];
                $file_tmp = $_FILES['foto']['tmp_name'];
                if(in_array($ekstensi, $ekstensi_diperbolehkan) === true && $ukuran < 2*1024*1024) {
                    $nama_file_baru = uniqid().'.'.$ekstensi;
                    if(move_uploaded_file($file_tmp, 'apps/mahasiswa/foto/'.$nama_file_baru)) {
                        $foto = $nama_file_baru;
                    }
                }
            }
            // Upload scan KTP/KK
            $scan_ktp_kk = '';
            if(isset($_FILES['scan_ktp_kk']) && $_FILES['scan_ktp_kk']['error'] == 0) {
                $ekstensi_diperbolehkan = array('png','jpg','jpeg','gif','pdf');
                $nama_scan_ktp_kk = $_FILES['scan_ktp_kk']['name'];
                $x = explode('.', $nama_scan_ktp_kk);
                $ekstensi = strtolower(end($x));
                $ukuran = $_FILES['scan_ktp_kk']['size'];
                $file_tmp = $_FILES['scan_ktp_kk']['tmp_name'];
                if(in_array($ekstensi, $ekstensi_diperbolehkan) === true && $ukuran < 2*1024*1024) {
                    $nama_file_baru = 'ktp_kk_' . uniqid().'.'.$ekstensi;
                    if(move_uploaded_file($file_tmp, 'apps/mahasiswa/ktp_mahasiswa/'.$nama_file_baru)) {
                        $scan_ktp_kk = $nama_file_baru;
                    }
                }
            }
            // Upload scan BPJS/KIS/Asuransi
            $scan_bpjs = '';
            if(isset($_FILES['scan_bpjs']) && $_FILES['scan_bpjs']['error'] == 0) {
                $ekstensi_diperbolehkan = array('png','jpg','jpeg','gif','pdf');
                $nama_scan_bpjs = $_FILES['scan_bpjs']['name'];
                $x = explode('.', $nama_scan_bpjs);
                $ekstensi = strtolower(end($x));
                $ukuran = $_FILES['scan_bpjs']['size'];
                $file_tmp = $_FILES['scan_bpjs']['tmp_name'];
                if(in_array($ekstensi, $ekstensi_diperbolehkan) === true && $ukuran < 2*1024*1024) {
                    $nama_file_baru = 'bpjs_' . uniqid().'.'.$ekstensi;
                    if(move_uploaded_file($file_tmp, 'apps/mahasiswa/bpjs_mahasiswa/'.$nama_file_baru)) {
                        $scan_bpjs = $nama_file_baru;
                    }
                }
            }
            
            // Cek apakah username sudah ada
            $cek_username = mysqli_query($kon, "SELECT * FROM tbl_user WHERE username='$username'");
            if(mysqli_num_rows($cek_username) > 0) {
                register_save_draft();
                $pesan = "<div class='alert alert-danger'><strong>Error!</strong> Tidak dapat menggunakan username ini. Silakan gunakan username yang lain.</div>";
                // Log registrasi gagal (username sudah ada)
                $logger->logUserActivity($username, 'mahasiswa', 'register_failed', 'Registrasi gagal: username sudah digunakan');
            } else {
                // Generate kode_pengguna baru
                $get_last = mysqli_query($kon, "SELECT MAX(id_user) as last_id FROM tbl_user WHERE level='mahasiswa'");
                $data_last = mysqli_fetch_assoc($get_last);
                $last_id = $data_last['last_id'] ? $data_last['last_id'] : 0;
                $kode_pengguna = 'M' . str_pad($last_id + 1, 3, '0', STR_PAD_LEFT);
                $tanggal_reg_sql = mysqli_real_escape_string($kon, $tanggal_registrasi);
                // Insert data ke database dengan status_approval = 'pending'
                $query = "INSERT INTO tbl_user (kode_pengguna, username, password, level, status_approval, tanggal_registrasi) VALUES ('$kode_pengguna', '$username', '$password', 'mahasiswa', 'pending', '$tanggal_reg_sql')";
                if(mysqli_query($kon, $query)) {
                    unset($_SESSION['register_draft']);
                    // Insert ke tbl_mahasiswa
                    $query_mhs = "INSERT INTO tbl_mahasiswa (kode_mahasiswa, nama, tempat_lahir, tanggal_lahir, agama, universitas, departemen_unitkerja, jurusan, nim, mulai_magang, akhir_magang, alamat, no_telp, no_hp_ortu, nama_pembimbing, no_hp_pembimbing, foto, scan_ktp_kk, scan_bpjs) VALUES ('$kode_pengguna', '$nama', '$tempat_lahir', '$tanggal_lahir', '$agama', '$universitas', '$departemen_unitkerja', '$jurusan', '$nim', '$mulai_magang', '$akhir_magang', '$alamat', '$no_telp', '$no_hp_ortu', '$nama_pembimbing', '$no_hp_pembimbing', '$foto', '$scan_ktp_kk', '$scan_bpjs')";
                    mysqli_query($kon, $query_mhs);
                    // Log registrasi berhasil
                    $logger->logUserActivity($kode_pengguna, 'mahasiswa', 'register', 'Registrasi berhasil untuk username: ' . $username);
                    // Redirect ke halaman login dengan pesan menunggu approval
                    header("Location: login.php?pesan=register_pending");
                    exit();
                } else {
                    register_save_draft();
                    $pesan = "<div class='alert alert-danger'><strong>Error!</strong> Gagal melakukan registrasi.</div>";
                    // Log registrasi gagal (insert gagal)
                    $logger->logUserActivity($username, 'mahasiswa', 'register_failed', 'Registrasi gagal: error database');
                }
            }
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
    // Query daftar universitas
    $universitas_list = [];
    $result_univ = mysqli_query($kon, "SELECT * FROM tbl_universitas ORDER BY nama_universitas ASC");
    while ($row_univ = mysqli_fetch_assoc($result_univ)) {
        $universitas_list[] = $row_univ;
    }
    // Query daftar departemen
    $departemen_list = [];
    $result_dep = mysqli_query($kon, "SELECT * FROM tbl_departemen ORDER BY nama_departemen ASC");
    while ($row_dep = mysqli_fetch_assoc($result_dep)) {
        $departemen_list[] = $row_dep;
    }
    $sel_univ = isset($_POST['universitas']) ? $_POST['universitas'] : ($_SESSION['register_draft']['universitas'] ?? '');
    $sel_dep = isset($_POST['departemen_unitkerja']) ? $_POST['departemen_unitkerja'] : ($_SESSION['register_draft']['departemen_unitkerja'] ?? '');
    $sel_agama = isset($_POST['agama']) ? $_POST['agama'] : ($_SESSION['register_draft']['agama'] ?? '');
?>
<!-- Mengambil Profil Aplikasi -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="apps/pengaturan/logo/logoAP.png">
    <title>Register | Attendance Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
    <style>
        body {
            background: #f7f7f7;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            max-width: 500px;
            width: 100%;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(60,60,60,0.12);
            padding: 2.5rem 2rem 2rem 2rem;
            background: #fff;
            animation: fadeIn 0.7s;
        }
        .register-logo {
            width: 100px;
            max-width: 100%;
            margin-bottom: 1rem;
        }
        .input-group-text {
            background: #f3f3f3;
            border-right: 0;
        }
        .form-control, .form-select, textarea {
            border-radius: 12px !important;
            background: #fff !important;
            border: 1.5px solid #e0e0e0 !important;
            min-height: 44px;
            font-size: 1rem;
            box-shadow: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus, textarea:focus {
            border-color: #007bff !important;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.08) !important;
            background: #fff !important;
        }
        .form-label {
            font-weight: 600;
            font-size: 1.05rem;
            margin-bottom: 6px;
        }
        .mb-3 {
            margin-bottom: 1.1rem !important;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: none;}
        }
        @media (max-width: 600px) {
            .register-card { padding: 1.5rem 0.5rem; }
            .register-logo { width: 70px; }
        }
        .input-group .form-control {
            border-radius: 0 !important;
            border-left: 0 !important;
            border-right: 0 !important;
        }
        .input-group .input-group-text {
            border-radius: 0 !important;
            background: #f3f3f3;
            border: none !important;
        }
        .input-group .input-group-text:first-child {
            border-top-left-radius: 12px !important;
            border-bottom-left-radius: 12px !important;
        }
        .input-group .input-group-text:last-child {
            border-top-right-radius: 12px !important;
            border-bottom-right-radius: 12px !important;
        }
        .input-group .form-control:focus {
            box-shadow: none;
            border-color: #007bff !important;
        }
    </style>
</head>
<body>
    <div class="register-card mx-auto">
        <div class="text-center mb-3">
            <div class="d-flex justify-content-center align-items-center gap-3 mb-2" style="gap: 18px;">
                <img src="apps/pengaturan/logo/logoapii.png" alt="Logo API" style="height:130px;max-width:250px;">
                <img src="apps/pengaturan/logo/logoairport.webp" alt="InJourney Logo" style="height:100px;max-width:200px;">
                </div>
                    <h2 class="mb-2 fw-bold" style="font-weight:400;font-size:1.5rem;">Create your account to get started.</h2>
                </div>
        <form action="<?php echo $_SERVER["PHP_SELF"];?>" method="post" enctype="multipart/form-data">
            <label for="info" class="form-label" style="font-weight:bold;">Silahkan Daftar Akun Baru Dan Isi Data Diri</label>
            <div class="form-group mb-3 input-group">
                <span class="input-group-text"><i class="fa fa-user"></i></span>
                <input type="text" class="form-control" name="username" id="username" placeholder="Username" maxlength="20" required value="<?php echo register_field('username'); ?>"/>
            </div>
            <div class="form-group mb-3 input-group">
                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" maxlength="32" required/>
                <span class="input-group-text" style="cursor:pointer;" onclick="togglePassword('password', this)"><i class="fa fa-eye-slash"></i></span>
            </div>
            <div class="form-group mb-3">
                <small class="text-muted d-block mb-1">Kekuatan password</small>
                <div class="progress" style="height:8px;">
                    <div id="pwd-meter" class="progress-bar" role="progressbar" style="width:0%;"></div>
                </div>
                <small id="pwd-hint" class="text-muted">Minimal 8 karakter, huruf besar, huruf kecil, dan angka.</small>
            </div>
            <div class="form-group mb-3">
                <label for="tanggal_registrasi" class="form-label">Tanggal Registrasi</label>
                <input type="text" class="form-control" name="tanggal_registrasi" id="tanggal_registrasi" readonly value="<?php echo date('d-m-Y'); ?>" style="background-color: #e9ecef; cursor: not-allowed;"/>
            </div>
            <div class="form-group mb-3">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="nama" id="nama" maxlength="50" required value="<?php echo register_field('nama'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="universitas" class="form-label">Universitas</label>
                <select class="form-select" name="universitas" id="universitas" required>
                    <option value="">-- Pilih Universitas/Sekolah --</option>
                    <?php foreach ($universitas_list as $univ): ?>
                        <option value="<?php echo htmlspecialchars($univ['nama_universitas']); ?>" <?php echo ($sel_univ === $univ['nama_universitas']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($univ['nama_universitas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="departemen_unitkerja" class="form-label">Departemen/Unit Kerja</label>
                <select class="form-select" name="departemen_unitkerja" id="departemen_unitkerja" required>
                    <option value="">-- Pilih Departemen/Unit Kerja --</option>
                    <?php foreach ($departemen_list as $dep): ?>
                        <option value="<?php echo htmlspecialchars($dep['nama_departemen']); ?>" <?php echo ($sel_dep === $dep['nama_departemen']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dep['nama_departemen']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="jurusan" class="form-label">Jurusan</label>
                <input type="text" class="form-control" name="jurusan" id="jurusan" maxlength="50" required value="<?php echo register_field('jurusan'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="nim" class="form-label">NIM/NIS</label>
                <input type="text" class="form-control" name="nim" id="nim" maxlength="15" pattern="\d{8,15}" inputmode="numeric" required value="<?php echo register_field('nim'); ?>"/>
                <small class="text-muted">Wajib 8–15 digit angka (tanpa huruf/spasi).</small>
            </div>
            <div class="form-group mb-3">
                <label for="mulai_magang" class="form-label">Tanggal Mulai Magang</label>
                <input type="date" class="form-control" name="mulai_magang" id="mulai_magang" required value="<?php echo register_field('mulai_magang'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="akhir_magang" class="form-label">Tanggal Selesai Magang</label>
                <input type="date" class="form-control" name="akhir_magang" id="akhir_magang" required value="<?php echo register_field('akhir_magang'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <input type="text" class="form-control" name="alamat" id="alamat" maxlength="100" required value="<?php echo register_field('alamat'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="no_telp" class="form-label">No. Telepon</label>
                <div class="input-group">
                    <span class="input-group-text">+62</span>
                    <input type="text" class="form-control" name="no_telp" id="no_telp" maxlength="12" pattern="\d{9,12}" inputmode="numeric" required value="<?php echo register_field('no_telp'); ?>"/>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control" name="tempat_lahir" id="tempat_lahir" maxlength="30" required value="<?php echo register_field('tempat_lahir'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tanggal_lahir" id="tanggal_lahir" required value="<?php echo register_field('tanggal_lahir'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="agama" class="form-label">Agama</label>
                <select class="form-select" name="agama" id="agama" required>
                    <option value="">-- Pilih Agama --</option>
                    <option value="Islam" <?php echo ($sel_agama === 'Islam') ? 'selected' : ''; ?>>Islam</option>
                    <option value="Kristen" <?php echo ($sel_agama === 'Kristen') ? 'selected' : ''; ?>>Kristen</option>
                    <option value="Katolik" <?php echo ($sel_agama === 'Katolik') ? 'selected' : ''; ?>>Katolik</option>
                    <option value="Hindu" <?php echo ($sel_agama === 'Hindu') ? 'selected' : ''; ?>>Hindu</option>
                    <option value="Buddha" <?php echo ($sel_agama === 'Buddha') ? 'selected' : ''; ?>>Buddha</option>
                    <option value="Konghucu" <?php echo ($sel_agama === 'Konghucu') ? 'selected' : ''; ?>>Konghucu</option>
                </select>
            </div>
            <div class="form-group mb-3">
                <label for="no_hp_ortu" class="form-label">No HP Orang Tua Peserta</label>
                <div class="input-group">
                    <span class="input-group-text">+62</span>
                    <input type="text" class="form-control" name="no_hp_ortu" id="no_hp_ortu" maxlength="12" pattern="\d{9,12}" inputmode="numeric" required value="<?php echo register_field('no_hp_ortu'); ?>"/>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="nama_pembimbing" class="form-label">Nama Guru/Dosen Pembimbing</label>
                <input type="text" class="form-control" name="nama_pembimbing" id="nama_pembimbing" maxlength="50" required value="<?php echo register_field('nama_pembimbing'); ?>"/>
            </div>
            <div class="form-group mb-3">
                <label for="no_hp_pembimbing" class="form-label">No HP Guru/Dosen Pembimbing</label>
                <div class="input-group">
                    <span class="input-group-text">+62</span>
                    <input type="text" class="form-control" name="no_hp_pembimbing" id="no_hp_pembimbing" maxlength="12" pattern="\d{9,12}" inputmode="numeric" required value="<?php echo register_field('no_hp_pembimbing'); ?>"/>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="foto" class="form-label">Foto Profil</label>
                <input type="file" class="form-control" name="foto" id="foto" accept="image/*"/>
                <small class="form-text text-muted">Format: jpg, jpeg, png, gif. Maksimal 2MB.</small>
            </div>
            <div class="form-group mb-3">
                <label for="scan_ktp_kk" class="form-label">Scan KTP/KK</label>
                <input type="file" class="form-control" name="scan_ktp_kk" id="scan_ktp_kk" accept="image/*,.pdf" required/>
                <small class="form-text text-muted">Format: jpg, jpeg, png, gif, pdf. Maksimal 2MB.</small>
            </div>
            <div class="form-group mb-3">
                <label for="scan_bpjs" class="form-label">Scan BPJS Kesehatan/KIS/Kartu Asuransi</label>
                <input type="file" class="form-control" name="scan_bpjs" id="scan_bpjs" accept="image/*,.pdf" required/>
                <small class="form-text text-muted">Format: jpg, jpeg, png, gif, pdf. Maksimal 2MB.</small>
            </div>
            <p class="small text-muted">Jika ada error validasi, isian teks di atas dipertahankan; unggah ulang foto/scan jika perlu.</p>
            <?php if ($_SERVER["REQUEST_METHOD"] == "POST") echo $pesan; ?>
            <div class="d-grid gap-2 mt-4">
                <button type="submit" name="submit" class="btn btn-primary btn-lg mx-auto" style="width: 200px;">Daftar</button>
            </div>
            <div class="text-center mt-3">
                <a href="login.php" class="text-decoration-none">Kembali ke Login</a>
            </div>
        </form>
        <div class="text-center mt-4 text-muted" style="font-size:0.95rem;">
            Attendance Platform: Intern (API)<br>
            Copyright &copy;<?php echo date('Y'); ?> Valendy Franklin Tumundoh<br>
            All Rights Reserved
        </div>
    </div>
    <script>
    function togglePassword(id, el) {
        const input = document.getElementById(id);
        const icon = el.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
    (function() {
        var pwd = document.getElementById('password');
        var meter = document.getElementById('pwd-meter');
        var hint = document.getElementById('pwd-hint');
        if (!pwd || !meter) return;
        function updatePwdMeter() {
            var v = pwd.value || '';
            var score = 0;
            if (v.length >= 8) score++;
            if (/[a-z]/.test(v)) score++;
            if (/[A-Z]/.test(v)) score++;
            if (/\d/.test(v)) score++;
            var pct = Math.min(100, score * 25);
            meter.style.width = pct + '%';
            meter.className = 'progress-bar' + (score <= 2 ? ' bg-danger' : (score === 3 ? ' bg-warning' : ' bg-success'));
            if (hint) {
                hint.textContent = score === 4 && v.length >= 8 ? 'Memenuhi syarat minimum.' : 'Minimal 8 karakter, huruf besar, huruf kecil, dan angka.';
            }
        }
        pwd.addEventListener('input', updatePwdMeter);
        updatePwdMeter();
    })();
    </script>
</body>
</html>
