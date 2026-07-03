<?php
// Session sudah dimulai di index.php, tidak perlu session_start() lagi
if (!isset($_SESSION["level"]) || ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin')) {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}

// Path relatif dari root aplikasi
include __DIR__ . '/../../config/database.php';

$kode_pengguna = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($kode_pengguna)) {
    echo "<div class='alert alert-danger'>ID tidak valid</div>";
    exit;
}

// Ambil data user dan mahasiswa
$query = "SELECT u.*, m.* 
          FROM tbl_user u
          LEFT JOIN tbl_mahasiswa m ON m.kode_mahasiswa = u.kode_pengguna
          WHERE u.kode_pengguna = '$kode_pengguna' AND u.level = 'mahasiswa'";
$result = mysqli_query($kon, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan</div>";
    exit;
}

$app_web_base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
$pub_prefix = ($app_web_base === '' || $app_web_base === '.') ? '' : $app_web_base;
$proj_root = dirname(__DIR__, 2);

function detail_req_media_row($label, $filename, $subfolder, $pub_prefix, $proj_root)
{
    if (empty($filename)) {
        echo '<tr><th>' . htmlspecialchars($label) . '</th><td><span class="text-muted">Belum diunggah</span></td></tr>';
        return;
    }
    $fs = $proj_root . '/apps/mahasiswa/' . $subfolder . '/' . $filename;
    $url = $pub_prefix . '/apps/mahasiswa/' . $subfolder . '/' . rawurlencode($filename);
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    echo '<tr><th>' . htmlspecialchars($label) . '</th><td>';
    if (!is_file($fs)) {
        echo '<span class="text-muted">File tidak ditemukan di server</span>';
    } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
        echo '<a href="' . htmlspecialchars($url) . '" target="_blank"><img src="' . htmlspecialchars($url) . '" alt="' . htmlspecialchars($label) . '" style="max-width:220px;max-height:220px;" class="img-thumbnail"></a>';
        echo ' <a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-external-link"></i> Buka</a>';
    } else {
        echo '<a href="' . htmlspecialchars($url) . '" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-download"></i> Lihat / unduh (' . htmlspecialchars($ext) . ')</a>';
    }
    echo '</td></tr>';
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li><a href="index.php?page=admin&subpage=request">Request Pendaftaran</a></li>
        <li class="active">Detail Pendaftaran</li>
    </ol>
</div>
<!--/.row-->

<div class="row" style="margin-top: 10px;">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-user"></i> <b>Detail Data Pendaftaran</b>
                <a href="index.php?page=admin&subpage=request" class="btn btn-default btn-sm pull-right"><i
                        class="fa fa-arrow-left"></i> Kembali</a>
            </div>
            <div class="panel-body" style="padding: 20px;">
                <div class="row">
                    <div class="col-md-6">
                        <h4><i class="fa fa-info-circle"></i> Informasi Akun</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Kode Pengguna</th>
                                <td><?php echo htmlspecialchars($data['kode_pengguna']); ?></td>
                            </tr>
                            <tr>
                                <th>Username</th>
                                <td><?php echo htmlspecialchars($data['username']); ?></td>
                            </tr>
                            <tr>
                                <th>Status Approval</th>
                                <td>
                                    <?php
                                    $status = $data['status_approval'];
                                    if ($status == 'pending') {
                                        echo "<span class='label label-warning'>Pending</span>";
                                    } elseif ($status == 'approved') {
                                        echo "<span class='label label-success'>Approved</span>";
                                    } elseif ($status == 'rejected') {
                                        echo "<span class='label label-danger'>Rejected</span>";
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Registrasi</th>
                                <td><?php echo date('d/m/Y H:i:s', strtotime($data['tanggal_registrasi'])); ?></td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h4><i class="fa fa-user-circle"></i> Data Pribadi</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Nama Lengkap</th>
                                <td><?php echo htmlspecialchars($data['nama'] ? $data['nama'] : '-'); ?></td>
                            </tr>
                            <tr>
                                <th>NIM</th>
                                <td><?php echo htmlspecialchars($data['nim'] ? $data['nim'] : '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Tempat Lahir</th>
                                <td><?php echo htmlspecialchars($data['tempat_lahir'] ? $data['tempat_lahir'] : '-'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Lahir</th>
                                <td><?php echo $data['tanggal_lahir'] ? date('d/m/Y', strtotime($data['tanggal_lahir'])) : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Agama</th>
                                <td><?php echo htmlspecialchars($data['agama'] ? $data['agama'] : '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Alamat</th>
                                <td><?php echo htmlspecialchars($data['alamat'] ? $data['alamat'] : '-'); ?></td>
                            </tr>
                            <tr>
                                <th>No. Telepon</th>
                                <td><?php echo htmlspecialchars($data['no_telp'] ? $data['no_telp'] : '-'); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-md-6">
                        <h4><i class="fa fa-graduation-cap"></i> Data Akademik</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">Universitas</th>
                                <td><?php echo htmlspecialchars($data['universitas'] ? $data['universitas'] : '-'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Departemen/Unit Kerja</th>
                                <td><?php echo htmlspecialchars($data['departemen_unitkerja'] ? $data['departemen_unitkerja'] : '-'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Jurusan</th>
                                <td><?php echo htmlspecialchars($data['jurusan'] ? $data['jurusan'] : '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Mulai Magang</th>
                                <td><?php echo $data['mulai_magang'] ? date('d/m/Y', strtotime($data['mulai_magang'])) : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Akhir Magang</th>
                                <td><?php echo $data['akhir_magang'] ? date('d/m/Y', strtotime($data['akhir_magang'])) : '-'; ?>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="col-md-6">
                        <h4><i class="fa fa-users"></i> Data Kontak</h4>
                        <table class="table table-bordered">
                            <tr>
                                <th width="40%">No HP Orang Tua</th>
                                <td><?php echo htmlspecialchars($data['no_hp_ortu'] ? $data['no_hp_ortu'] : '-'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Nama Pembimbing</th>
                                <td><?php echo htmlspecialchars($data['nama_pembimbing'] ? $data['nama_pembimbing'] : '-'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>No HP Pembimbing</th>
                                <td><?php echo htmlspecialchars($data['no_hp_pembimbing'] ? $data['no_hp_pembimbing'] : '-'); ?>
                                </td>
                            </tr>
                            <?php
                            detail_req_media_row('Foto profil', $data['foto'] ?? '', 'foto', $pub_prefix, $proj_root);
                            detail_req_media_row('Scan KTP/KK', $data['scan_ktp_kk'] ?? '', 'ktp_mahasiswa', $pub_prefix, $proj_root);
                            detail_req_media_row('Scan BPJS/KIS', $data['scan_bpjs'] ?? '', 'bpjs_mahasiswa', $pub_prefix, $proj_root);
                            ?>
                        </table>
                    </div>
                </div>

                <?php if ($data['status_approval'] == 'pending'): ?>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12 text-center">
                            <a href="index.php?page=admin&subpage=approve_request&action=approve&id=<?php echo $kode_pengguna; ?>"
                                class="btn btn-success btn-lg"
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui request ini?')">
                                <i class="fa fa-check"></i> Accept Request
                            </a>
                            <a href="index.php?page=admin&subpage=approve_request&action=reject&id=<?php echo $kode_pengguna; ?>"
                                class="btn btn-danger btn-lg"
                                onclick="return confirm('Apakah Anda yakin ingin menolak request ini?')">
                                <i class="fa fa-times"></i> Reject Request
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .label {
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: bold;
    }

    .label-warning {
        background-color: #ffc107;
        color: #212529;
    }

    .label-success {
        background-color: #28a745;
        color: #fff;
    }

    .label-danger {
        background-color: #dc3545;
        color: #fff;
    }
</style>