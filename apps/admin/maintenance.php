<?php
// Handler backup/redirect HARUS di paling atas sebelum output HTML apapun!
if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
    echo"<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}

include 'config/database.php';
include 'config/logger.php';

$logger = new Logger($kon);

// Handler backup action (PASTIKAN sebelum HTML apapun!)
if (isset($_POST['run_backup'])) {
    $mode = isset($_POST['mode']) ? $_POST['mode'] : 'full';
    $logger->logAdminAction($_SESSION["kode_pengguna"], 'system_maintenance', null, 'Manual backup dijalankan (mode: ' . $mode . ')');
    echo "<script>window.location.href='scripts/backup_and_cleanup.php?run=1&mode=$mode';</script>";
    exit;
}

// Get system statistics
$stats = array();

// Database size
$result = mysqli_query($kon, "SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size_MB'
    FROM information_schema.tables 
    WHERE table_schema = 'db_magang'
    GROUP BY table_schema");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['db_size'] = isset($row['Size_MB']) ? $row['Size_MB'] : 0;
}

// Count records
$tables = array('tbl_absensi', 'tbl_kegiatan', 'tbl_mahasiswa', 'tbl_user_logs', 'tbl_auth_logs');
foreach ($tables as $table) {
    $result = mysqli_query($kon, "SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $stats[$table] = $row['count'];
    }
}

// Backup folder size
$backup_dir = 'backups/';
$backup_size = 0;
if (is_dir($backup_dir)) {
    $files = glob($backup_dir . '*.sql');
    foreach ($files as $file) {
        $backup_size += filesize($file);
    }
}
$stats['backup_size'] = round($backup_size / 1024 / 1024, 2);

// Photo folder size
$photo_dirs = array(
    'apps/data_absensi/foto_absen_masuk/',
    'apps/data_absensi/foto_absen_pulang/',
    'apps/mahasiswa/foto/',
    'foto/'
);
$photo_size = 0;
foreach ($photo_dirs as $dir) {
    if (is_dir($dir)) {
        $files = glob($dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                $photo_size += filesize($file);
            }
        }
    }
}
$stats['photo_size'] = round($photo_size / 1024 / 1024, 2);

// Old data count (data > 3 months)
$cutoff_date = date('Y-m-d', strtotime('-3 months'));
$result = mysqli_query($kon, "SELECT COUNT(*) as count FROM tbl_absensi WHERE tanggal < '$cutoff_date'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['old_absensi'] = $row['count'];
}

$result = mysqli_query($kon, "SELECT COUNT(*) as count FROM tbl_kegiatan WHERE tanggal < '$cutoff_date'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['old_kegiatan'] = $row['count'];
}

// Old logs count (logs > 6 months)
$cutoff_date_logs = date('Y-m-d H:i:s', strtotime('-6 months'));
$result = mysqli_query($kon, "SELECT COUNT(*) as count FROM tbl_user_logs WHERE created_at < '$cutoff_date_logs'");
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['old_logs'] = $row['count'];
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Maintenance</li>
    </ol>
</div>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header">Maintenance & Backup</h1>
    </div>
</div>

<div class="row">
    <!-- System Statistics -->
    <div class="col-lg-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-chart-bar"></i> <strong>Statistik Sistem</strong></h3>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Ukuran Database:</strong></td>
                        <td><?php echo $stats['db_size']; ?> MB</td>
                    </tr>
                    <tr>
                        <td><strong>Total Presensi:</strong></td>
                        <td><?php echo number_format($stats['tbl_absensi']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Total Kegiatan:</strong></td>
                        <td><?php echo number_format($stats['tbl_kegiatan']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Total Peserta Magang:</strong></td>
                        <td><?php echo number_format($stats['tbl_mahasiswa']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Total Log User:</strong></td>
                        <td><?php echo number_format($stats['tbl_user_logs']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Total Log Auth:</strong></td>
                        <td><?php echo number_format($stats['tbl_auth_logs']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Ukuran Backup:</strong></td>
                        <td><?php echo $stats['backup_size']; ?> MB</td>
                    </tr>
                    <tr>
                        <td><strong>Ukuran Foto:</strong></td>
                        <td><?php echo $stats['photo_size']; ?> MB</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Old Data Statistics -->
    <div class="col-lg-6">
        <div class="panel panel-warning">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-clock"></i> <strong>Data Lama (> 3 Bulan)</strong></h3>
            </div>
            <div class="panel-body">
                <table class="table table-striped">
                    <tr>
                        <td><strong>Presensi Lama:</strong></td>
                        <td><?php echo number_format($stats['old_absensi']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Kegiatan Lama:</strong></td>
                        <td><?php echo number_format($stats['old_kegiatan']); ?> records</td>
                    </tr>
                    <tr>
                        <td><strong>Log Lama (> 6 Bulan):</strong></td>
                        <td><?php echo number_format($stats['old_logs']); ?> records</td>
                    </tr>
                </table>
                
                <?php if ($stats['old_absensi'] > 0 || $stats['old_kegiatan'] > 0 || $stats['old_logs'] > 0): ?>
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i>
                    <strong>Peringatan:</strong> Ada data lama yang bisa dibersihkan untuk menghemat ruang disk.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Backup & Cleanup Actions -->
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-database"></i> <strong>Backup & Cleanup</strong></h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Fitur Backup & Cleanup</h4>
                        <p>Fitur ini memungkinkan admin untuk melakukan backup database dan menghapus data lama yang tidak lagi diperlukan. Berikut adalah rincian fitur:</p>
                        <ul>
                            <li>Backup database sebelum penghapusan</li>
                            <li>Hapus data presensi & kegiatan > 3 bulan</li>
                            <li>Hapus foto > 3 bulan</li>
                            <li>Hapus log > 6 bulan</li>
                            <li>Hapus backup > 90 hari</li>
                            <li>Maksimal ukuran backup: 100MB</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h4>Aksi Manual</h4>
                        <form method="post" onsubmit="return confirm('Yakin ingin menjalankan backup SAJA (tanpa hapus data)?');">
                            <input type="hidden" name="mode" value="backup">
                            <button type="submit" name="run_backup" class="btn btn-info btn-lg">
                                <i class="fa fa-download"></i> Backup Saja (Tanpa Hapus Data)
                            </button>
                        </form>
                        <br>
                        <form method="post" onsubmit="return confirm('Yakin ingin menjalankan backup & cleanup? Data lama akan dihapus setelah backup.');">
                            <input type="hidden" name="mode" value="full">
                            <button type="submit" name="run_backup" class="btn btn-primary btn-lg">
                                <i class="fa fa-play"></i> Backup & Cleanup (Backup + Hapus Data Lama)
                            </button>
                        </form>
                        <hr>
                        <h4>Cara menggunakan backup</h4>
                        <ol>
                            <li>Gunakan <strong>Backup Saja</strong> untuk menyalin isi database ke file <code>.sql</code> di folder <code>backups/</code> tanpa menghapus data aplikasi.</li>
                            <li>Untuk <strong>restore</strong>, di phpMyAdmin atau klien MySQL pilih basis data lalu impor file <code>.sql</code> hasil backup.</li>
                            <li><strong>Backup &amp; Cleanup</strong> mem-backup lalu menghapus data lama (presensi, kegiatan, foto, log).</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Monitoring -->
    <div class="col-lg-12">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-eye"></i> Monitoring Status</h3>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><strong>Folder Backup</strong></h5>
                        <p><code><?php echo realpath('backups/'); ?></code></p>
                        <p>Status: <?php echo is_dir('backups/') ? '<span class="label label-success">Ada</span>' : '<span class="label label-danger">Tidak Ada</span>'; ?></p>
                    </div>
                    <div class="col-md-4">
                        <h5><strong>Database</strong></h5>
                        <p>Ukuran: <strong><?php echo $stats['db_size']; ?> MB</strong></p>
                        <p>Status: <?php echo $stats['db_size'] > 100 ? '<span class="label label-warning">Besar</span>' : '<span class="label label-success">Normal</span>'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto refresh statistics every 30 seconds
setInterval(function() {
    location.reload();
}, 30000);
</script> 