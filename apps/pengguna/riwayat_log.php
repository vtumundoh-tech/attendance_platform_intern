<?php
    // Cek apakah user sudah login
    if (!isset($_SESSION["kode_pengguna"])) {
        // Logging percobaan akses tidak sah
        include_once 'config/logger.php';
        include_once 'config/database.php';
        $logger = new Logger($kon);
        $user_id = $_SESSION["kode_pengguna"] ?? 'unknown';
        $logger->logUserActivity($user_id, $_SESSION["level"] ?? 'unknown', 'unauthorized_access', 'Percobaan akses halaman user');
        header("Location:../../login.php");
        exit;
    }

    // Include database dan logger
    include 'config/database.php';
    include 'config/logger.php';
    
    $logger = new Logger($kon);
    $user_id = $_SESSION["kode_pengguna"];
    $user_type = strtolower($_SESSION["level"]);
    
    // Pagination
    $page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Filter
    $filter_type = isset($_GET['type']) ? $_GET['type'] : '';
    $filter_date = isset($_GET['date']) ? $_GET['date'] : '';
    
    // Get logs
    $logs = $logger->getUserLogs($user_id, $limit, $offset, $filter_type, $filter_date);
    $total_logs = count($logger->getUserLogs($user_id, 1000, 0, $filter_type, $filter_date)); // Get total for pagination
    $total_pages = ceil($total_logs / $limit);

    $date_30_days_ago = date('Y-m-d', strtotime('-30 days'));
    $rekap_total = isset($_GET['rekap_total']) && $_GET['rekap_total'] == '1';
    $statistics = $logger->getLogStatistics($user_id, $date_30_days_ago, date('Y-m-d 23:59:59'), $rekap_total);

    if (!$rekap_total && !empty($statistics)) {
        // Kelompokkan statistik per tanggal
        $grouped = [];
        foreach ($statistics as $stat) {
            $grouped[$stat['activity_date']][] = $stat;
        }
        krsort($grouped); // Urutkan tanggal terbaru ke terlama
        $dates = array_keys($grouped);
    }
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Riwayat Aktivitas</li>
    </ol>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading bg-info text-white">
                <i class="fa fa-history"></i> Riwayat Aktivitas Saya
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                
                <!-- Filter -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <form method="GET" action="" class="form-inline">
                            <input type="hidden" name="page" value="riwayat_log_pengguna">
                            
                            <div class="form-group mr-2">
                                <label for="type" class="mr-2">Jenis Aktivitas:</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Semua Aktivitas</option>
                                    <option value="login" <?php echo $filter_type == 'login' ? 'selected' : ''; ?>>Login</option>
                                    <option value="logout" <?php echo $filter_type == 'logout' ? 'selected' : ''; ?>>Logout</option>
                                    <option value="password_change" <?php echo $filter_type == 'password_change' ? 'selected' : ''; ?>>Perubahan Password</option>
                                    <option value="absensi" <?php echo $filter_type == 'absensi' ? 'selected' : ''; ?>>Absensi</option>
                                    <option value="kegiatan" <?php echo $filter_type == 'kegiatan' ? 'selected' : ''; ?>>Kegiatan</option>
                                    <option value="profil" <?php echo $filter_type == 'profil' ? 'selected' : ''; ?>>Profil</option>
                                </select>
                            </div>
                            
                            <div class="form-group mr-2">
                                <label for="date" class="mr-2">Tanggal:</label>
                                <input type="date" name="date" id="date" class="form-control" value="<?php echo $filter_date; ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            
                            <a href="index.php?page=riwayat_log_pengguna" class="btn btn-secondary" title="Hapus filter dan kembali ke tampilan awal">
                                <i class="fa fa-times"></i> Hapus filter
                            </a>
                        </form>
                    </div>
                </div>

                <!-- Tabel Log -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
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
                                    <td colspan="6" class="text-center">Tidak ada data aktivitas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($logs as $index => $log): ?>
                                    <tr>
                                        <td><?php echo $offset + $index + 1; ?></td>
                                        <td>
                                            <?php 
                                                $date = new DateTime($log['created_at']);
                                                echo $date->format('d/m/Y H:i:s');
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                                $activity_icons = [
                                                    'login' => 'fa-sign-in',
                                                    'logout' => 'fa-sign-out',
                                                    'password_change' => 'fa-key',
                                                    'absensi' => 'fa-calendar-check-o',
                                                    'kegiatan' => 'fa-book',
                                                    'profil' => 'fa-user',
                                                    'export' => 'fa-download',
                                                    'print' => 'fa-print'
                                                ];
                                                
                                                $icon = $activity_icons[$log['activity_type']] ?? 'fa-info-circle';
                                                echo '<i class="fa ' . $icon . '"></i> ' . ucfirst(str_replace('_', ' ', $log['activity_type']));
                                            ?>
                                        </td>
                                        <td><?php echo $log['activity_description'] ?: '-'; ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo $log['ip_address']; ?></span>
                                        </td>
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
                <?php
                $window = 3;
                $start = max(1, $page - floor($window / 2));
                $end = min($total_pages, $start + $window - 1);
                if ($end - $start + 1 < $window) {
                    $start = max(1, $end - $window + 1);
                }
                $params = $_GET;
                unset($params['p']);
                $query_string = http_build_query($params);
                $page_url = '?page=riwayat_log_pengguna' . ($query_string ? '&' . $query_string . '&' : '&') . 'p=';
                ?>
                <div class="mt-3" style="text-align:left;">
                  <nav aria-label="Page navigation">
                    <ul class="pagination" style="margin-bottom:0;">
                      <?php if ($page > 1): ?>
                        <li class="page-item">
                          <a class="page-link" href="<?php echo $page_url . ($page - 1); ?>">
                            <i class="fa fa-chevron-left"></i>
                          </a>
                        </li>
                      <?php endif; ?>
                      <?php for ($i = $start; $i <= $end; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                          <a class="page-link" href="<?php echo $page_url . $i; ?>">
                            <?php echo $i; ?>
                          </a>
                        </li>
                      <?php endfor; ?>
                      <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                          <a class="page-link" href="<?php echo $page_url . ($page + 1); ?>">
                            <i class="fa fa-chevron-right"></i>
                          </a>
                        </li>
                      <?php endif; ?>
                    </ul>
                    <div style="font-size:14px; color:#555; margin-top:2px;">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></div>
                  </nav>
                </div>

                <!-- Swiper.js CDN -->
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
                <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
                <!-- Statistik -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="panel panel-info">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart"></i> Statistik Aktivitas (30 Hari Terakhir)
                            </div>
                            <div class="panel-body">
                                <form method="GET" action="" class="form-inline" style="margin-bottom:18px;">
                                    <input type="hidden" name="page" value="riwayat_log_pengguna">
                                    <div class="form-group mr-2">
                                        <input type="checkbox" name="rekap_total" id="rekap_total" value="1" <?php echo isset($_GET['rekap_total']) && $_GET['rekap_total'] == '1' ? 'checked' : ''; ?>>
                                        <label for="rekap_total" style="margin-left:4px;">Tampilkan Total Statistik</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm ml-2">Terapkan</button>
                                </form>
                                <?php if ($rekap_total): ?>
                                    <?php if (!empty($statistics)): ?>
                                        <?php
                                        // Gabungkan total per aktivitas jika masih ada duplikasi
                                        $total_per_aktivitas = [];
                                        foreach ($statistics as $stat) {
                                            $type = $stat['activity_type'];
                                            if (!isset($total_per_aktivitas[$type])) {
                                                $total_per_aktivitas[$type] = 0;
                                            }
                                            $total_per_aktivitas[$type] += (int)$stat['total_activities'];
                                        }
                                        ?>
                                        <div class="row" style="text-align:center;">
                                            <?php foreach ($total_per_aktivitas as $type => $total): ?>
                                                <div class="col-md-3 col-sm-4 col-xs-6" style="margin-bottom:18px;">
                                                    <div style="font-size:2em; font-weight:500; color:#444;"> <?php echo $total; ?> </div>
                                                    <div style="font-size:1.1em; color:#666;"> <?php echo ucfirst(str_replace('_', ' ', $type)); ?> </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-center text-muted">Belum ada data statistik</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (!empty($statistics)): ?>
                                        <div class="swiper mySwiper">
                                            <div class="swiper-wrapper">
                                                <?php foreach ($dates as $tgl): ?>
                                                    <div class="swiper-slide">
                                                        <div style="font-weight:bold;font-size:18px;margin-bottom:8px; text-align:center;">
                                                            <?php echo date('d/m/Y', strtotime($tgl)); ?>
                                                        </div>
                                                        <div class="row">
                                                            <?php foreach ($grouped[$tgl] as $stat): ?>
                                                                <div class="col-md-12 col-sm-12">
                                                                    <div class="stat-box text-center">
                                                                        <h4><?php echo $stat['total_activities']; ?></h4>
                                                                        <p><?php echo ucfirst(str_replace('_', ' ', $stat['activity_type'])); ?></p>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Tombol navigasi Swiper -->
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                        </div>
                                        <script>
                                        var swiper = new Swiper('.mySwiper', {
                                            slidesPerView: 4,
                                            slidesPerGroup: 4,
                                            spaceBetween: 30,
                                            navigation: {
                                                nextEl: '.swiper-button-next',
                                                prevEl: '.swiper-button-prev',
                                            },
                                            effect: 'slide',
                                            speed: 600,
                                        });
                                        swiper.on('slideChangeTransitionStart', function () {
                                            document.querySelectorAll('.mySwiper .swiper-slide').forEach(el => el.classList.add('blur'));
                                        });
                                        swiper.on('slideChangeTransitionEnd', function () {
                                            document.querySelectorAll('.mySwiper .swiper-slide').forEach(el => el.classList.remove('blur'));
                                        });
                                        </script>
                                    <?php else: ?>
                                        <p class="text-center text-muted">Belum ada data statistik</p>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Diagram Statistik: Stacked Bar Chart -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-bar-chart"></i> Diagram Aktivitas 30 Hari Terakhir
                            </div>
                            <div class="panel-body">
                                <div style="position:relative; min-height:250px; width:100%;">
                                    <canvas id="logChart" style="width:100%; height:250px; max-width:100%;"></canvas>
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
                                            title: { display: true, text: 'Statistik Aktivitas 30 Hari Terakhir' }
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

            </div>
        </div>
    </div>
</div>

<style>
.stat-box {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.stat-box h4 {
    color: #007bff;
    margin: 0 0 5px 0;
    font-weight: bold;
}

.stat-box p {
    margin: 0 0 5px 0;
    font-weight: 500;
}

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
.swiper-slide.blur {
  filter: blur(6px) brightness(0.95);
  transition: filter 0.3s;
}
</style>

<script>
$(document).ready(function() {
    // Auto-submit form when filter changes
    $('#type, #date').change(function() {
        $(this).closest('form').submit();
    });
    
    // Tooltip untuk IP address
    $('[data-toggle="tooltip"]').tooltip();
});
</script> 