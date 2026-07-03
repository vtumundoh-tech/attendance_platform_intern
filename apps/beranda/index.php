<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Beranda</li>
    </ol>
</div>
<!--/.row-->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Beranda</div>
            <div class="panel-body">

                <!--Menampilkan Nama Pengguna Sesuai Level -->
                <?php if ($_SESSION['level'] == 'Admin' or $_SESSION['level'] == 'Admin'): ?>
                    <h3>Selamat Datang, <?php echo $_SESSION["nama_admin"]; ?>.</h3>
                <?php endif; ?>
                <?php if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
                    <h3>Selamat Datang, <?php echo $_SESSION["nama_mahasiswa"]; ?>.</h3>
                <?php endif; ?>
                <!-- Menampilkan Nama Pengguna Sesuai Level -->

                <!-- Mengambil data table tbl_site -->
                <?php
                //Mengambil profil aplikasi
                //Mengubungkan database
                include 'config/database.php';
                $query = mysqli_query($kon, "select * from tbl_site limit 1");
                $row = mysqli_fetch_array($query);
                ?>
                <!-- Menhambil data table tbl_site -->

                <!-- Info Aplikasi -->
                <p>Selamat Datang di Aplikasi Attendance Platform: Intern. Sebuah sistem yang memungkinkan para Peserta
                    OJT/PKL/Magang di <?php echo $row['nama_instansi']; ?> untuk melalukan presensi dan mencatat kegiatan
                    harian dari website. Sistem ini diharapkan dapat memberi kemudahan setiap Peserta OJT/PKL/Magang
                    untuk melakukan presensi dan mencatat kegiatan harian.</p>
                <!-- Info Aplikasi -->

                <!-- statistik admin dan tabel absensi terakhir khusus admin -->
                <?php if ($_SESSION['level'] == 'Admin' || $_SESSION['level'] == 'admin'): ?>
                    <div class="row" style="margin-bottom:20px;">
                        <div class="col-md-3">
                            <div class="stat-card bg-danger">
                                <div class="stat-icon"><i class="fa fa-users"></i></div>
                                <div class="stat-value">
                                    <?php
                                    // Mahasiswa aktif: masih dalam periode magang, status_aktif = 'aktif', dan akun disetujui
                                    $q1 = mysqli_query($kon, "
                                    SELECT COUNT(*) as jml
                                    FROM tbl_mahasiswa m
                                    INNER JOIN tbl_user u ON u.kode_pengguna = m.kode_mahasiswa
                                    WHERE m.mulai_magang <= CURDATE()
                                      AND m.akhir_magang >= CURDATE()
                                      AND m.status_aktif = 'aktif'
                                      AND NOT (u.status_approval <=> 'rejected')
                                ");
                                    $d1 = mysqli_fetch_assoc($q1);
                                    echo $d1['jml'];
                                    ?>
                                </div>
                                <div class="stat-label">Peserta Magang Aktif</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-success">
                                <div class="stat-icon"><i class="fa fa-calendar-check-o"></i></div>
                                <div class="stat-value">
                                    <?php
                                    $today = date('Y-m-d');
                                    $q2 = mysqli_query($kon, "SELECT COUNT(*) as jml FROM tbl_absensi WHERE tanggal='$today'");
                                    $d2 = mysqli_fetch_assoc($q2);
                                    echo $d2['jml'];
                                    ?>
                                </div>
                                <div class="stat-label">Presensi Hari Ini</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-info">
                                <div class="stat-icon"><i class="fa fa-book"></i></div>
                                <div class="stat-value">
                                    <?php
                                    $q3 = mysqli_query($kon, "SELECT COUNT(*) as jml FROM tbl_kegiatan WHERE tanggal='$today'");
                                    $d3 = mysqli_fetch_assoc($q3);
                                    echo $d3['jml'];
                                    ?>
                                </div>
                                <div class="stat-label">Kegiatan Hari Ini</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card bg-warning">
                                <div class="stat-icon"><i class="fa fa-exclamation-circle"></i></div>
                                <div class="stat-value">
                                    <?php
                                    $q4 = mysqli_query($kon, "SELECT COUNT(*) as jml FROM tbl_absensi WHERE tanggal='$today' AND status=2");
                                    $d4 = mysqli_fetch_assoc($q4);
                                    echo $d4['jml'];
                                    ?>
                                </div>
                                <div class="stat-label">Peserta Magang Izin Hari Ini</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Absensi Terakhir untuk admin -->
                    <div class="panel panel-default" style="margin-top:20px;">
                        <div class="panel-heading">
                            <i class="fa fa-calendar-check-o"></i> <b>Presensi Terakhir</b>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Tanggal</th>
                                            <th>Jam Masuk</th>
                                            <th>Jam Pulang</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Absensi terakhir hanya untuk mahasiswa aktif & akun disetujui
                                        $qabs = mysqli_query($kon, "
                                    SELECT a.*, m.nama
                                    FROM tbl_absensi a
                                    JOIN tbl_mahasiswa m ON a.id_mahasiswa = m.id_mahasiswa
                                    JOIN tbl_user u ON u.kode_pengguna = m.kode_mahasiswa
                                    WHERE m.mulai_magang <= CURDATE()
                                      AND m.akhir_magang >= CURDATE()
                                      AND m.status_aktif = 'aktif'
                                      AND NOT (u.status_approval <=> 'rejected')
                                    ORDER BY a.tanggal DESC, a.waktu_masuk DESC
                                    LIMIT 5
                                ");
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($qabs)) {
                                            $tgl = date('d/m/Y', strtotime($row['tanggal']));
                                            $masuk = $row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-';
                                            $pulang = $row['waktu_pulang'] ? date('H:i', strtotime($row['waktu_pulang'])) : '-';
                                            // Status dengan icon
                                            $status_icon = '';
                                            if ($row['status'] == 1) {
                                                $status_icon = '<i class="fa fa-check-circle text-success"></i> Hadir';
                                            } elseif ($row['status'] == 2) {
                                                $status_icon = '<i class="fa fa-info-circle text-warning"></i> Izin';
                                            } elseif ($row['status'] == 3) {
                                                $status_icon = '<i class="fa fa-times-circle text-danger"></i> Tidak Hadir';
                                            } else {
                                                $status_icon = '-';
                                            }
                                            echo "<tr>";
                                            echo "<td align='center'>" . $no . "</td>";
                                            echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                            echo "<td>" . $tgl . "</td>";
                                            echo "<td>" . $masuk . "</td>";
                                            echo "<td>" . $pulang . "</td>";
                                            echo "<td>" . $status_icon . "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Panel Pengumuman untuk semua user -->
                <?php
                $q_pengumuman = mysqli_query($kon, "SELECT * FROM tbl_pengumuman ORDER BY tanggal DESC LIMIT 1");
                $pengumuman = mysqli_fetch_assoc($q_pengumuman);
                ?>
                <div class="panel panel-info" style="margin-top:20px; background: #eaf6fb;">
                    <div class="panel-heading">
                        <i class="fa fa-bullhorn"></i> <b>Pengumuman</b>
                    </div>
                    <div class="panel-body">
                        <?php if ($pengumuman && !empty($pengumuman['isi'])): ?>
                            <div style="font-size:16px; margin-bottom:10px;">
                                <?php echo nl2br(htmlspecialchars($pengumuman['isi'])); ?><br>
                                <small class="text-muted">Dikirim: <?php echo $pengumuman['tanggal']; ?></small>
                            </div>
                        <?php endif; ?>
                        <ul style="font-size:16px; margin-bottom:0;">
                            <li>Pengumpulan laporan magang maksimal 1 bulan setelah periode magang berakhir.</li>
                            <li>Pastikan data presensi dan kegiatan harian sudah lengkap sebelum akhir periode magang.
                            </li>
                            <li>Jika ada kendala atau pertanyaan, silakan hubungi pembimbing magang atau admin.</li>
                            <li>Jaga kesehatan dan tetap semangat menjalani magang!</li>
                        </ul>
                    </div>
                </div>
                <!-- Akhir Panel Pengumuman -->

            </div>
        </div>
    </div>
</div>

<?php if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
    <div class="panel panel-default" style="margin-top:20px;">
        <div class="panel-heading">
            <i class="fa fa-calendar-check-o"></i> <b>Presensi Terakhir Anda</b>
        </div>
        <div class="panel-body">
            <div class="row" style="margin-bottom: 20px;">
                <?php
                include 'config/database.php';
                $id_mahasiswa = $_SESSION['id_mahasiswa'];
                $qabs = mysqli_query($kon, "SELECT * FROM tbl_absensi WHERE id_mahasiswa='$id_mahasiswa' ORDER BY tanggal DESC, waktu_masuk DESC LIMIT 5");
                while ($row = mysqli_fetch_assoc($qabs)) {
                    $tgl = date('d/m/Y', strtotime($row['tanggal']));
                    $masuk = $row['waktu_masuk'] ? date('H:i', strtotime($row['waktu_masuk'])) : '-';
                    $pulang = $row['waktu_pulang'] ? date('H:i', strtotime($row['waktu_pulang'])) : '-';
                    $status = $row['status'];
                    if ($status == 1) {
                        $badge = '<span class="badge badge-success"><i class="fa fa-check-circle"></i> Hadir</span>';
                    } elseif ($status == 2) {
                        $badge = '<span class="badge badge-warning"><i class="fa fa-info-circle"></i> Izin</span>';
                    } elseif ($status == 3) {
                        $badge = '<span class="badge badge-danger"><i class="fa fa-times-circle"></i> Tidak Hadir</span>';
                    } else {
                        $badge = '<span class="badge badge-secondary">-</span>';
                    }
                    echo '
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="card absensi-terakhir-card shadow-sm mb-3 fade-in" style="border-radius:18px; margin-bottom:24px; position:relative; overflow:hidden;">
                    <span class="absensi-status-icon" title="Status: ' . ($status == 1 ? 'Hadir' : ($status == 2 ? 'Izin' : ($status == 3 ? 'Tidak Hadir' : '-'))) . '">
                        ' . ($status == 1 ? '✔️' : ($status == 2 ? '❗' : ($status == 3 ? '❌' : '–'))) . '
                    </span>
                    <div class="absensi-tanggal">' . $tgl . '</div>
                    <div class="absensi-waktu">
                        <span><i class="fa fa-sign-in"></i> Masuk: <b>' . $masuk . '</b></span>
                        <span><i class="fa fa-sign-out"></i> Pulang: <b>' . $pulang . '</b></span>
                    </div>
                    <div>' . $badge . '</div>
                </div>
            </div>
            ';
                }
                ?>
            </div>
        </div>
    </div>
    <style>
        .card {
            background: #f8f9fa;
            border: 1px solid #e3e6f0;
            transition: box-shadow 0.2s;
            min-height: 120px;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .badge {
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .badge-success {
            background: #28a745;
            color: #fff;
        }

        .badge-warning {
            background: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background: #dc3545;
            color: #fff;
        }

        .badge-secondary {
            background: #6c757d;
            color: #fff;
        }

        @media (max-width: 767px) {
            .card-body {
                padding: 12px 8px;
            }

            .card {
                min-height: 100px;
            }
        }

        .absensi-terakhir-card {
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            border: 2px solid #00bcd4;
            border-radius: 18px;
            box-shadow: 0 2px 12px rgba(0, 188, 212, 0.10);
            padding: 24px 20px 20px 20px;
            margin-bottom: 24px;
            transition: box-shadow 0.3s, transform 0.2s;
            position: relative;
            overflow: hidden;
            min-height: 150px;
            animation: fadeInCard 0.7s;
        }

        .absensi-terakhir-card:hover {
            box-shadow: 0 8px 32px rgba(0, 188, 212, 0.25);
            transform: scale(1.03);
        }

        .absensi-status-icon {
            position: absolute;
            top: 18px;
            left: 18px;
            font-size: 32px;
            opacity: 0.85;
            z-index: 2;
        }

        .absensi-tanggal {
            font-size: 22px;
            font-weight: bold;
            margin-left: 48px;
            margin-bottom: 8px;
            margin-top: 2px;
        }

        .absensi-waktu {
            display: flex;
            gap: 24px;
            margin: 18px 0 10px 0;
            font-size: 16px;
            align-items: center;
        }

        .absensi-badge {
            font-size: 16px;
            padding: 8px 18px;
            border-radius: 10px;
            font-weight: bold;
        }

        .fade-in {
            opacity: 0;
            animation: fadeInCard 0.7s forwards;
        }

        @keyframes fadeInCard {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        @media (max-width: 767px) {
            .absensi-terakhir-card {
                padding: 14px 6px 12px 6px;
                min-height: 110px;
            }

            .absensi-tanggal {
                font-size: 16px;
                margin-left: 38px;
            }

            .absensi-status-icon {
                font-size: 22px;
                top: 10px;
                left: 10px;
            }

            .absensi-waktu {
                gap: 10px;
                font-size: 13px;
            }
        }
    </style>
<?php endif; ?>

<style>
    .stat-card {
        color: #fff;
        border-radius: 12px;
        padding: 24px 16px 16px 16px;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        text-align: center;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        transition: box-shadow 0.2s;
    }

    .stat-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.16);
    }

    .stat-icon {
        font-size: 38px;
        opacity: 0.25;
        position: absolute;
        top: 12px;
        right: 16px;
    }

    .stat-value {
        font-size: 38px;
        font-weight: bold;
        margin-bottom: 8px;
        margin-top: 8px;
        z-index: 2;
        position: relative;
    }

    .stat-label {
        font-size: 16px;
        font-weight: 500;
        z-index: 2;
        position: relative;
    }

    .bg-teal {
        background: #009688;
    }

    .panel {
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .panel-heading {
        font-size: 18px;
        padding: 14px 18px;
        border-radius: 10px 10px 0 0;
        background: #f5faff;
        border-bottom: 1px solid #e0e0e0;
    }

    .panel-body {
        padding: 18px;
    }

    .table-hover tbody tr:hover {
        background-color: #f1f7ff;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }
</style>

<?php if ($_SESSION['level'] == 'Mahasiswa' or $_SESSION['level'] == 'mahasiswa'): ?>
    <?php if (!empty($_SESSION['mahasiswa_fitur_penuh'])): ?>
        <!-- Shortcut Tombol Ambil Absen -->
        <a href="index.php?page=absen" class="shortcut-absen-btn" title="Ambil Absen">
            <div class="shortcut-camera-icon">
                <i class="fa fa-camera"></i>
            </div>
            <span class="shortcut-label">Ambil Absen</span>
        </a>

        <style>
            .shortcut-absen-btn {
                position: fixed;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                text-decoration: none;
                z-index: 1000;
                cursor: pointer;
                transition: all 0.3s ease;
                animation: fadeInUp 0.5s ease;
            }

            .shortcut-camera-icon {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                background: linear-gradient(135deg, #17a2b8 0%, rgb(10, 68, 78) 100%);
                border: 4px solid #fff;
                box-shadow: 0 4px 16px rgba(23, 162, 184, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .shortcut-camera-icon i {
                color: #fff;
                font-size: 28px;
            }

            .shortcut-label {
                margin-top: 8px;
                color: #17a2b8;
                font-weight: 600;
                font-size: 14px;
                text-align: center;
                white-space: nowrap;
                transition: all 0.3s ease;
            }

            .shortcut-absen-btn:hover {
                text-decoration: none;
                transform: translateX(-50%) translateY(-5px);
            }

            .shortcut-absen-btn:hover .shortcut-camera-icon {
                box-shadow: 0 6px 24px rgba(23, 162, 184, 0.6);
                transform: scale(1.1);
            }

            .shortcut-absen-btn:hover .shortcut-label {
                color: #138496;
            }

            .shortcut-absen-btn:active .shortcut-camera-icon {
                transform: scale(0.95);
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateX(-50%) translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateX(-50%) translateY(0);
                }
            }

            /* Responsive untuk mobile */
            @media (max-width: 767px) {
                .shortcut-absen-btn {
                    bottom: 20px;
                }

                .shortcut-camera-icon {
                    width: 56px;
                    height: 56px;
                    border-width: 3px;
                }

                .shortcut-camera-icon i {
                    font-size: 24px;
                }

                .shortcut-label {
                    font-size: 12px;
                    margin-top: 6px;
                }
            }

            /* Pastikan tidak tertutup oleh elemen lain */
            @media (max-width: 1024px) {
                .shortcut-absen-btn {
                    z-index: 9999;
                }
            }
        </style>
    <?php endif; ?>
<?php endif; ?>