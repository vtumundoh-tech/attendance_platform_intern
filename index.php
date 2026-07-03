<?php
//Memulai sesi
session_start();
// Session timeout (5 menit)
$timeout = 300; // 300 detik = 5 menit
if (isset($_SESSION['id_pengguna'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
        session_unset();
        session_destroy();
        header('Location: login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}
//Jika kode pengguna di session kosong maka kembali ke login
if (!$_SESSION["kode_pengguna"]) {
    header("Location:login.php");
    exit();
    //Jika kode pengguna ada maka akan di proses masuk ke halaman utama
} else {
    //Menghubungkan database
    include 'config/database.php';
    //Mengambil variable dari session 
    $kode_pengguna = $_SESSION["kode_pengguna"];
    $username = $_SESSION["username"];
    //Query untuk menampilkan nama ke halaman utama
    $hasil = mysqli_query($kon, "select username from tbl_user where kode_pengguna='$kode_pengguna'");
    //Menyimpan data query ke variable data
    $data = mysqli_fetch_array($hasil);
    //Menyimpan data username ke variable username
    $username_db = $data['username'];
    //Jika username kosong maka session akan di hapus
    if ($username != $username_db) {
        //Menghapus session
        session_unset();
        session_destroy();
        //Mengalihkan page ke halaman login
        header("Location:login.php");
        exit();
    }
    if (isset($_SESSION['level']) && strtolower($_SESSION['level']) === 'mahasiswa' && !empty($_SESSION['id_mahasiswa'])) {
        include_once __DIR__ . '/config/mahasiswa_status.php';
        $idm = (int) $_SESSION['id_mahasiswa'];
        $qr = mysqli_query($kon, "SELECT mulai_magang, akhir_magang, status_aktif FROM tbl_mahasiswa WHERE id_mahasiswa=$idm LIMIT 1");
        if ($qr && ($rm = mysqli_fetch_assoc($qr))) {
            if (!mahasiswa_boleh_login($rm)) {
                session_unset();
                session_destroy();
                header('Location: login.php?pesan=akun_dinonaktifkan');
                exit();
            }
            $_SESSION['mahasiswa_fitur_penuh'] = mahasiswa_boleh_fitur_magang_penuh($rm) ? '1' : '0';
        }
    }
}

// Handle approve_request SEBELUM output HTML (karena perlu redirect)
// Ini harus dilakukan SETELAH session check tapi SEBELUM output HTML
if (isset($_GET['page']) && $_GET['page'] == 'admin' && isset($_GET['subpage']) && $_GET['subpage'] == 'approve_request') {
    include "apps/admin/approve_request.php";
    // approve_request.php akan redirect dan exit, jadi tidak perlu lanjut
    exit; // Pastikan tidak ada output setelah ini
}
?>

<?php
//Menghubungkan database
include 'config/database.php';
//Query untuk menampilkan table tbl_site
$query = mysqli_query($kon, "select * from tbl_site limit 1");
//Menyimpan hasil query ke variable
$row = mysqli_fetch_array($query);
//Menyimpan nama instansi dari tbl_site
$nama_instansi = $row['nama_instansi'];
//Menyimpan logo dari tbl_site
$logo = $row['logo'];
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Favicon -->
    <link rel="shortcut icon" href="apps/pengaturan/logo/<?php echo $logo; ?>">
    <!-- Title Website -->
    <title><?php echo $nama_instansi; ?></title>
    <!-- Bootstrap -->
    <link href="template/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="template/css/font-awesome.min.css" rel="stylesheet">
    <!-- Date Picker 3 -->
    <link href="template/css/datepicker3.css" rel="stylesheet">
    <!-- Local CSS -->
    <link href="template/css/styles.css" rel="stylesheet">
    <!-- jQuery -->
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <script src="template/js/jquery-2.2.3.min.js"></script>
    <script src="template/js/jquery-1.11.1.min.js"></script>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Custom Font -->
    <link href="src/font/font.css" rel="stylesheet" type="text/css">
    <!-- Custom CSS -->
    <style>
        .no-js #loader {
            display: none;
        }

        .js #loader {
            display: block;
            position: absolute;
            left: 100px;
            top: 0;
        }

        .se-pre-con {
            position: fixed;
            left: 0px;
            top: 0px;
            width: 100%;
            height: 100%;
            z-index: 9999;
            background: url('loading.gif') center no-repeat #fff;
        }

        .navbar-brand {
            font-family: "Poppins", "Helvetica Neue", Helvetica, Arial, sans-serif !important;
        }

        @media (max-width: 767px) {
            .navbar-header img {
                height: 32px !important;
                margin-right: 8px !important;
            }

            .navbar-brand {
                font-size: 15px !important;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 120px;
                display: inline-block;
                vertical-align: middle;
                font-family: "Poppins", "Helvetica Neue", Helvetica, Arial, sans-serif !important;
            }

            .navbar-header {
                flex-wrap: nowrap !important;
            }

            /* Custom Off-canvas Mobile Sidebar */
            #sidebar-collapse {
                display: block !important;
                position: fixed !important;
                top: 60px !important;
                left: -280px !important;
                width: 260px !important;
                height: calc(100vh - 60px) !important;
                z-index: 9999 !important;
                transition: left 0.3s ease-in-out !important;
                background: #fff !important;
                box-shadow: 2px 0px 10px rgba(0,0,0,0.2) !important;
                overflow-y: auto !important;
                margin: 0 !important;
            }

            #sidebar-collapse.in {
                left: 0 !important;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-custom navbar-fixed-top bg-info" role="navigation">
        <div class="container-fluid"><!-- container-fluid -->
            <div class="navbar-header" style="display: flex; align-items: center;">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#sidebar-collapse"><span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span></button>
                <img src="apps/pengaturan/logo/logoapii.png" alt="Logo" style="height:60px; margin-right:12px;">
                <a class="navbar-brand" href="#">Attendance Platform: Intern</a>
            </div>
        </div><!-- /.container-fluid -->
    </nav>
    <div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
        <!-- Menampilkan info nama dan level admin di navbar -->
        <?php if ($_SESSION['level'] == 'Admin' or $_SESSION['level'] == 'admin'): ?>
            <div class="profile-sidebar">
                <div class="profile-userpic">
                    <img src="source/img/profile.png" class="img-responsive" alt="">
                </div>
                <div class="profile-usertitle">
                    <div class="sidebar-nama"><?php echo $_SESSION['nama_admin']; ?></div>
                    <div class="profile-usertitle-name"><?php echo "Administrator"; ?></div>
                    <div></div>
                </div>
                <div class="clear"></div>
            </div>
        <?php endif; ?>
        <!-- Menampilkan info nama dan level admin di navbar -->

        <!-- Menampilkan info nama dan level mahasiswa di navbar -->
        <?php if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
            <div class="profile-sidebar">
                <div class="profile-userpic">
                    <img src="/valendy_presensi/apps/mahasiswa/foto/<?php echo $_SESSION['foto']; ?>"
                        class="img-responsive" alt="Foto Profil" id="sidebar-profile-pic" style="cursor:pointer;">
                </div>
                <div class="profile-usertitle">
                    <div class="sidebar-nama"><?php echo $_SESSION['nama_mahasiswa']; ?></div>
                    <div class="profile-usertitle-name"><?php echo "Peserta Magang"; ?></div>
                    <div></div>
                </div>
                <div class="clear"></div>
            </div>
            <!-- Modal Foto Profil -->
            <div class="modal fade" id="modalFotoProfil" tabindex="-1" role="dialog" aria-labelledby="modalFotoProfilLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalFotoProfilLabel">Foto Profil</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <img src="/valendy_presensi/apps/mahasiswa/foto/<?php echo $_SESSION['foto']; ?>"
                                alt="Foto Profil" style="max-width:100%;max-height:400px;">
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var img = document.getElementById('sidebar-profile-pic');
                    if (img) {
                        img.addEventListener('click', function () {
                            $('#modalFotoProfil').modal('show');
                        });
                    }
                });
            </script>
        <?php endif; ?>
        <!-- Menampilkan info nama dan level mahasiswa di navbar -->

        <!-- Side Bar Navigation -->
        <div class="divider"></div>
        <!-- Menu Beranda -->
        <ul class="nav menu">
            <li><a href='index.php?page=beranda'><em class='fa fa-home'>&nbsp;</em> Dashboard</a></li>
            <!-- Menu Beranda -->
            <!-- Menu Admin -->
            <?php if ($_SESSION["level"] == "Admin" or $_SESSION['level'] == 'admin'): ?>
                <li><a href="index.php?page=admin&subpage=request" id="request"><em class="fa fa-bell">&nbsp;</em> Request
                        Akun
                        <?php
                        // Hitung jumlah request pending
                        include 'config/database.php';
                        $count_query = "SELECT COUNT(*) as total FROM tbl_user WHERE level = 'mahasiswa' AND status_approval = 'pending'";
                        $count_result = mysqli_query($kon, $count_query);
                        $count_data = mysqli_fetch_assoc($count_result);
                        $pending_count = $count_data['total'];
                        if ($pending_count > 0) {
                            echo "<span class='badge badge-warning pull-right'>$pending_count</span>";
                        }
                        ?>
                    </a></li>
                <li><a href="index.php?page=mahasiswa" id="mahasiswa"><em class="fa fa-users">&nbsp;</em> Data Peserta Magang</a>
                </li>
                <li><a href="index.php?page=data_absensi" id="data_absensi"><em class="fa fa-calendar">&nbsp;</em> Data
                        Presensi</a></li>
                <li><a href="index.php?page=data_kegiatan" id="kegiatan"><em class="fa fa-book">&nbsp;</em> Data
                        Kegiatan</a></li>
                <li><a href="index.php?page=admin" id="admin"><em class="fa fa-user">&nbsp;</em> Administrator</a></li>
                <li><a href="index.php?page=pengaturan" id="pengaturan"><em class="fa fa-gear">&nbsp;</em> Pengaturan</a>
                </li>
                <li><a href="index.php?page=pengajuan_banding" id="pengajuan_banding"><em class="fa fa-gavel">&nbsp;</em> Pengajuan Banding
                        <?php
                        $banding_pending_sidebar = 0;
                        if (mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'tbl_pengajuan_banding'")) > 0) {
                            include_once __DIR__ . '/config/sertifikat_helper.php';
                            $banding_pending_sidebar = sertifikat_count_banding_pending($kon);
                        }
                        if ($banding_pending_sidebar > 0) {
                            echo "<span class='badge badge-warning pull-right'>$banding_pending_sidebar</span>";
                        }
                        ?>
                    </a></li>
                <li><a href="index.php?page=maintenance" id="maintenance"><em class="fa fa-database">&nbsp;</em>
                        Maintenance</a></li>
                <li><a href="index.php?page=riwayat_log_admin"><em class="fa fa-history">&nbsp;</em> Riwayat Aktivitas
                        User</a></li>
            <?php endif; ?>
            <!-- Menu Admin -->
            <!-- Menu Mahasiswa -->
            <?php if ($_SESSION["level"] == "Mahasiswa" or $_SESSION["level"] == "mahasiswa"): ?>
                <li><a href="index.php?page=absen"><em class="fa fa-calendar-check-o">&nbsp;</em> Presensi</a></li>
                <li><a href="index.php?page=riwayat"><em class="fa fa-history">&nbsp;</em> Riwayat Presensi</a></li>
                <li><a href="index.php?page=kegiatan"><em class="fa fa-book">&nbsp;</em> Kegiatan Harian</a></li>
                <li><a href="index.php?page=profil"><em class="fa fa-user-circle-o">&nbsp;</em> Profil</a></li>
                <li><a href="index.php?page=riwayat_log_pengguna"><em class="fa fa-history">&nbsp;</em> Riwayat
                        Aktivitas</a></li>
            <?php endif; ?>
            <!-- Menu Mahasiswa -->
            <!-- Menu Keluar -->
            <li><a href="logout.php" id="keluar"><em class="fa fa-sign-out">&nbsp;</em> Keluar</a></li>
        </ul>
        <!-- Menu Keluar -->
    </div>
    <!-- Side Bar Navigation -->

    <!-- Page Penghubung -->
    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
        <?php
        if (isset($_GET['page'])) {
            $page = $_GET['page'];
            switch ($page) {
                case 'beranda':
                    include "apps/beranda/index.php";
                    break;
                case 'admin':
                    // Handle subpage untuk admin
                    if (isset($_GET['subpage'])) {
                        $subpage = $_GET['subpage'];
                        switch ($subpage) {
                            case 'request':
                                include "apps/admin/request.php";
                                break;
                            case 'detail_request':
                                include "apps/admin/detail_request.php";
                                break;
                            default:
                                include "apps/admin/index.php";
                                break;
                        }
                    } else {
                        include "apps/admin/index.php";
                    }
                    break;
                case 'mahasiswa':
                    include "apps/mahasiswa/index.php";
                    break;
                case 'data_absensi':
                    include "apps/data_absensi/index.php";
                    break;
                case 'data_kegiatan':
                    include "apps/data_kegiatan/index.php";
                    break;
                case 'pengaturan':
                    include "apps/pengaturan/index.php";
                    break;
                case 'pengajuan_banding':
                    include "apps/sertifikat/index.php";
                    break;
                case 'maintenance':
                    include "apps/admin/maintenance.php";
                    break;
                case 'absen':
                    include "apps/pengguna/absen.php";
                    break;
                case 'riwayat':
                    include "apps/data_absensi/riwayat.php";
                    break;
                case 'kegiatan':
                    include "apps/data_kegiatan/kegiatan.php";
                    break;
                case 'profil':
                    include "apps/pengguna/profil.php";
                    break;
                case 'riwayat_log_admin':
                    include "apps/admin/riwayat_log.php";
                    break;
                case 'riwayat_log_pengguna':
                    include "apps/pengguna/riwayat_log.php";
                    break;
                default:
                    echo "<center><h3>Maaf. Halaman Tidak Di Temukan !</h3></center>";
                    break;
            }
        }
        ?>
        <!-- Function Page Penghubung -->

        <!--/.row-->
    </div>
    <!--/.main-->

    <!-- Java Script -->
    <script src="template/js/bootstrap.min.js"></script>
    <script src="template/js/chart.min.js"></script>
    <script src="template/js/chart-data.js"></script>
    <script src="template/js/easypiechart.js"></script>
    <script src="template/js/easypiechart-data.js"></script>
    <script src="template/js/bootstrap-datepicker.js"></script>
    <script src="template/js/custom.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.6.2/css/buttons.dataTables.min.css">
    <script src="/assets/chart/chart.js"></script>
    <!-- Java Script -->

    <script>
        // konfirmasi sebelum keluar aplikasi
        $('#keluar').on('click', function () {
            konfirmasi = confirm("Apakah Anda Yakin Ingin Keluar?")
            if (konfirmasi) {
                return true;
            } else {
                return false;
            }
        });
    </script>
    <script type="module">
        import { initializeApp } from "https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js";
        import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging.js";

        // Konfigurasi Firebase (ISI DENGAN DATA DARI FIREBASE CONSOLE)
        const firebaseConfig = {
            apiKey: "AIzaSyDU-UTAEuY76ks3R1TaCeba9YPDySuAAcg",
            authDomain: "pushnotif-ee3d5.firebaseapp.com",
            projectId: "pushnotif-ee3d5",
            storageBucket: "pushnotif-ee3d5.firebasestorage.app",
            messagingSenderId: "1046476887331",
            appId: "1:1046476887331:web:3a5b4912baf3f0cc596896",
            measurementId: "G-6L1S3805VN"
        };

        // Inisialisasi Firebase
        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        // Minta izin notifikasi dan ambil token
        Notification.requestPermission().then((permission) => {
            if (permission === "granted") {
                console.log('Mendaftarkan service worker...');
                navigator.serviceWorker.register('firebase-messaging-sw.js')
                    .then(function (registration) {
                        console.log('Service worker terdaftar:', registration);
                        return getToken(messaging, {
                            vapidKey: "BA50TH4MqW40OIoawrS_G85nfDRL1tFU4HP8mtmwKBh5aiWHK6TIFbljFWNZNvAtAzZ4OEALI5pSQJS1gwJHc5Q",
                            serviceWorkerRegistration: registration
                        });
                    })
                    .then((currentToken) => {
                        console.log('Token FCM didapat:', currentToken);
                        if (currentToken) {
                            // Kirim token ke server PHP
                            fetch('api/simpan_token.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    token: currentToken,
                                    id_user: "<?php echo $_SESSION['kode_pengguna']; ?>"
                                })
                            });
                        } else {
                            console.log('No registration token available.');
                        }
                    })
                    .catch((err) => {
                        console.log('Tidak dapat mengambil token:', err);
                    });
            } else {
                console.log('Notification permission denied');
            }
        });

        // Tangkap notifikasi saat web dibuka
        onMessage(messaging, (payload) => {
            //untuk alertnya saya buat komen dulu agar pop up notifnya langsung dari browser dan bukan dari dalam web localhost(bisa di uncomment kapan saja)
            //alert("Notifikasi: " + payload.notification.title + " - " + payload.notification.body);
        });
    </script>
</body>

</html>