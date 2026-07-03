<?php
// Session sudah dimulai di index.php, tidak perlu session_start() lagi
if (!isset($_SESSION["level"]) || ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin')) {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}

// Path relatif dari root aplikasi
include __DIR__ . '/../../config/database.php';
if (file_exists(__DIR__ . '/../../config/logger.php')) {
    include_once __DIR__ . '/../../config/logger.php';
    $logger = new Logger($kon);
} else {
    $logger = null;
}

// Ambil semua request pending
$query = "SELECT u.id_user, u.kode_pengguna, u.username, u.tanggal_registrasi, u.status_approval,
          m.nama, m.universitas, m.jurusan, m.nim, m.mulai_magang, m.akhir_magang
          FROM tbl_user u
          LEFT JOIN tbl_mahasiswa m ON m.kode_mahasiswa = u.kode_pengguna
          WHERE u.level = 'mahasiswa' AND u.status_approval = 'pending'
          ORDER BY u.tanggal_registrasi DESC";
$result = mysqli_query($kon, $query);
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Request Pendaftaran</li>
    </ol>
</div>
<!--/.row-->

<div class="row" style="margin-top: 10px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bell"></i> <b>Request Pendaftaran Baru</b>
                <span class="badge badge-warning pull-right"
                    id="countPending"><?php echo mysqli_num_rows($result); ?></span>
            </div>
            <div class="panel-body" style="padding: 20px;">
                <?php
                if (isset($_GET['approve'])) {
                    if ($_GET['approve'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Request telah disetujui.</div>";
                    } elseif ($_GET['approve'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Gagal menyetujui request.</div>";
                    }
                }
                if (isset($_GET['reject'])) {
                    if ($_GET['reject'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Request telah ditolak.</div>";
                    } elseif ($_GET['reject'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Gagal menolak request.</div>";
                    }
                }
                ?>

                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="table-responsive" style="margin-top: 15px;">
                        <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Username</th>
                                    <th>Universitas</th>
                                    <th>Jurusan</th>
                                    <th>Tanggal Registrasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $no = 1;
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $tanggal_registrasi = date('d/m/Y H:i', strtotime($row['tanggal_registrasi']));
                                    echo "<tr>";
                                    echo "<td align='center'>" . $no . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama'] ? $row['nama'] : '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['universitas'] ? $row['universitas'] : '-') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['jurusan'] ? $row['jurusan'] : '-') . "</td>";
                                    echo "<td>" . $tanggal_registrasi . "</td>";
                                    echo "<td align='center' style='white-space: nowrap;'>";
                                    echo "<a href='index.php?page=admin&subpage=detail_request&id=" . $row['kode_pengguna'] . "' class='btn btn-info btn-sm' title='Lihat Detail' style='margin: 2px;'><i class='fa fa-eye'></i> Detail</a> ";
                                    echo "<a href='index.php?page=admin&subpage=approve_request&action=approve&id=" . $row['kode_pengguna'] . "' class='btn btn-success btn-sm' title='Setujui' onclick=\"return confirm('Apakah Anda yakin ingin menyetujui request ini?')\" style='margin: 2px;'><i class='fa fa-check'></i> Accept</a> ";
                                    echo "<a href='index.php?page=admin&subpage=approve_request&action=reject&id=" . $row['kode_pengguna'] . "' class='btn btn-danger btn-sm' title='Tolak' onclick=\"return confirm('Apakah Anda yakin ingin menolak request ini?')\" style='margin: 2px;'><i class='fa fa-times'></i> Reject</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                    $no++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle"></i> <strong>Tidak ada request pending</strong><br>
                        Semua request pendaftaran telah diproses.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .badge {
        padding: 5px 10px;
        border-radius: 12px;
        font-size: 12px;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .panel-body {
        min-height: 400px;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
    }

    .table td {
        padding: 10px 8px;
        vertical-align: middle;
    }

    .btn-sm {
        padding: 5px 10px;
        font-size: 12px;
    }
</style>