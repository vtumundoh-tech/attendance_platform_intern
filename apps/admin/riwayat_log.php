<?php
// Cek session dan hak akses admin
// session_start(); // Dihapus karena session sudah aktif dari index.php
if (!isset($_SESSION["level"]) || strtolower($_SESSION["level"]) != 'admin') {
    // Logging percobaan akses tidak sah
    include_once 'config/logger.php';
    include_once 'config/database.php';
    $logger = new Logger($kon);
    $user_id = $_SESSION["kode_pengguna"] ?? 'unknown';
    $logger->logUserActivity($user_id, $_SESSION["level"] ?? 'unknown', 'unauthorized_access', 'Percobaan akses halaman admin');
    echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat mengakses halaman ini.</div>";
    exit;
}

include 'config/database.php';
include 'config/logger.php';
$logger = new Logger($kon);

// Filter
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Query log dengan filter
$where = [];
$params = [];
if ($filter_user) {
    $where[] = "user_id = ?";
    $params[] = $filter_user;
}
if ($filter_type) {
    $where[] = "activity_type = ?";
    $params[] = $filter_type;
}
if ($filter_date) {
    $where[] = "DATE(created_at) = ?";
    $params[] = $filter_date;
}
$where_sql = $where ? ("WHERE " . implode(" AND ", $where)) : "";

// Prepare query
$sql = "SELECT * FROM tbl_user_logs $where_sql ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $kon->prepare($sql);
$types = str_repeat('s', count($params)) . 'ii';
$params[] = $limit;
$params[] = $offset;
$stmt->bind_param($types, ...$params);
$stmt->execute();
$logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Total log untuk pagination
$sql_count = "SELECT COUNT(*) as total FROM tbl_user_logs $where_sql";
$stmt_count = $kon->prepare($sql_count);
if ($where) {
    $types_count = str_repeat('s', count($params) - 2);
    $stmt_count->bind_param($types_count, ...array_slice($params, 0, count($params) - 2));
}
$stmt_count->execute();
$total_logs = $stmt_count->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_logs / $limit);

// Ambil daftar user untuk filter
$user_result = mysqli_query($kon, "SELECT DISTINCT user_id FROM tbl_user_logs ORDER BY user_id");
$users = [];
while ($row = mysqli_fetch_assoc($user_result)) {
    $users[] = $row['user_id'];
}

// Ambil statistik aktivitas semua user 30 hari terakhir
$date_30_days_ago = date('Y-m-d', strtotime('-30 days'));
$statistics = $logger->getLogStatistics(null, $date_30_days_ago, date('Y-m-d 23:59:59'));
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Riwayat Aktivitas Semua User</li>
    </ol>
</div>

<!-- Diagram Statistik: Stacked Bar Chart (Paling Atas) -->
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-bar-chart"></i> Diagram Aktivitas Semua User (30 Hari Terakhir)
            </div>
            <div class="panel-body">
                <div style="position:relative; min-height:250px; width:100%;">
                    <canvas id="logChart" style="width:100%; height:250px; max-width:100%"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                var statistics = <?php echo json_encode($statistics); ?>;
                let labels = [];
                let dataMap = {};
                statistics.forEach(stat => {
                    let date = stat.activity_date;
                    let type = stat.activity_type;
                    let total = parseInt(stat.total_activities);
                    if (!labels.includes(date)) labels.push(date);
                    if (!dataMap[type]) dataMap[type] = {};
                    dataMap[type][date] = total;
                });
                labels.sort();
                let colors = [
                    '#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#17a2b8', '#fd7e14', '#20c997', '#e83e8c', '#343a40'
                ];
                let colorIdx = 0;
                let datasets = Object.keys(dataMap).map(type => {
                    let ds = {
                        label: type,
                        data: labels.map(date => dataMap[type][date] || 0),
                        backgroundColor: colors[colorIdx % colors.length],
                        stack: 'Stack 0'
                    };
                    colorIdx++;
                    return ds;
                });
                var ctx = document.getElementById('logChart').getContext('2d');
                var logChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: window.innerWidth < 600 ? 'bottom' : 'top' },
                            title: { display: true, text: 'Statistik Aktivitas Semua User 30 Hari Terakhir' }
                        },
                        scales: {
                            x: { stacked: true },
                            y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah Aktivitas' } }
                        }
                    }
                });
                </script>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Aktivitas Semua User (Tengah) -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="panel panel-info">
            <div class="panel-heading">
                <i class="fa fa-bar-chart"></i> Statistik Aktivitas Semua User (30 Hari Terakhir)
            </div>
            <div class="panel-body">
                <?php
                if (!empty($statistics)):
                    // Kelompokkan statistik per hari dan jenis aktivitas
                    $stat_by_type = [];
                    foreach ($statistics as $stat) {
                        $type = $stat['activity_type'];
                        if (!isset($stat_by_type[$type])) $stat_by_type[$type] = 0;
                        $stat_by_type[$type] += $stat['total_activities'];
                    }
                ?>
                <div class="row">
                    <?php foreach ($stat_by_type as $type => $total): ?>
                        <div class="col-md-3 col-sm-6">
                            <div class="stat-box text-center">
                                <h4><?php echo $total; ?></h4>
                                <p><?php echo ucfirst(str_replace('_', ' ', $type)); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p class="text-center text-muted">Belum ada data statistik</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading bg-info text-white">
                <i class="fa fa-history"></i> Riwayat Aktivitas Semua User
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <!-- Filter -->
                <form method="GET" action="" class="form-inline mb-3">
                    <input type="hidden" name="page" value="riwayat_log_admin">
                    <div class="form-group mr-2">
                        <label for="user" class="mr-2">User:</label>
                        <select name="user" id="user" class="form-control">
                            <option value="">Semua User</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo $user; ?>" <?php echo $filter_user == $user ? 'selected' : ''; ?>><?php echo $user; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="type" class="mr-2">Jenis Aktivitas:</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">Semua Aktivitas</option>
                            <option value="login" <?php echo $filter_type == 'login' ? 'selected' : ''; ?>>Login</option>
                            <option value="logout" <?php echo $filter_type == 'logout' ? 'selected' : ''; ?>>Logout</option>
                            <option value="password_change" <?php echo $filter_type == 'password_change' ? 'selected' : ''; ?>>Perubahan Password</option>
                            <option value="absensi" <?php echo $filter_type == 'absensi' ? 'selected' : ''; ?>>Presensi</option>
                            <option value="kegiatan" <?php echo $filter_type == 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                            <option value="profil" <?php echo $filter_type == 'profil' ? 'selected' : ''; ?>>Profil</option>
                            <option value="register" <?php echo $filter_type == 'register' ? 'selected' : ''; ?>>Registrasi</option>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="date" class="mr-2">Tanggal:</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?php echo $filter_date; ?>">
                    </div>
                    <button type="submit" class="btn btn-primary mr-2">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <a href="index.php?page=riwayat_log_admin" class="btn btn-secondary" title="Muat ulang dan hapus filter">
                        <i class="fa fa-refresh"></i> Segarkan
                    </a>
                </form>
                <!-- Tabel Log -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>User ID</th>
                                <th>Waktu</th>
                                <th>Jenis Aktivitas</th>
                                <th>Deskripsi</th>
                                <th>IP Address</th>
                                <th>Browser</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($logs)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data aktivitas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $index => $log): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td><?php echo htmlspecialchars($log['user_id']); ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $log['activity_type'])); ?></td>
                                        <td><?php echo $log['activity_description'] ?: '-'; ?></td>
                                        <td><span class="badge badge-info"><?php echo $log['ip_address']; ?></span></td>
                                        <td>
                                            <?php
                                                $user_agent = $log['user_agent'];
                                                if (strpos($user_agent, 'Chrome') !== false) {
                                                    echo '<i class="fa fa-chrome"></i> Chrome';
                                                } elseif (strpos($user_agent, 'Firefox') !== false) {
                                                    echo '<i class="fa fa-firefox"></i> Firefox';
                                                } elseif (strpos($user_agent, 'Safari') !== false) {
                                                    echo '<i class="fa fa-safari"></i> Safari';
                                                } elseif (strpos($user_agent, 'Edge') !== false) {
                                                    echo '<i class="fa fa-edge"></i> Edge';
                                                } else {
                                                    echo '<i class="fa fa-globe"></i> Browser Lain';
                                                }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=riwayat_log_admin&p=<?php echo $page - 1; ?>&user=<?php echo $filter_user; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>">
                                        <i class="fa fa-chevron-left"></i> Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=riwayat_log_admin&p=<?php echo $i; ?>&user=<?php echo $filter_user; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=riwayat_log_admin&p=<?php echo $page + 1; ?>&user=<?php echo $filter_user; ?>&type=<?php echo $filter_type; ?>&date=<?php echo $filter_date; ?>">
                                        Selanjutnya <i class="fa fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.8em;
}
.table th {
    background-color: #f8f9fa;
    border-top: none;
}
.pagination .page-link {
    color: #007bff;
}
.pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
}
</style>

<script>
$(document).ready(function() {
    $('#user, #type, #date').change(function() {
        $(this).closest('form').submit();
    });
});
</script> 