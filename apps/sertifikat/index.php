<?php
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}

include 'config/database.php';
$tbl_banding_ok = mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'tbl_pengajuan_banding'")) > 0;
if (!$tbl_banding_ok) {
    echo "<div class='alert alert-warning'>Jalankan migrasi <code>database/migration_sertifikat_magang.sql</code> terlebih dahulu.</div>";
    exit;
}
include_once 'config/sertifikat_helper.php';

$pending_count = sertifikat_count_banding_pending($kon);
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda"><em class="fa fa-home"></em></a></li>
        <li class="active">Pengajuan Banding Sertifikat</li>
    </ol>
</div>

<?php
if (isset($_GET['aksi'])) {
    $map = [
        'berhasil' => ['success', 'Pengajuan banding disetujui. Tombol unduh sertifikat peserta telah aktif.'],
        'tolak_berhasil' => ['warning', 'Pengajuan banding ditolak.'],
        'bypass_berhasil' => ['success', 'Bypass sertifikat berhasil diberikan.'],
        'gagal' => ['danger', 'Terjadi kesalahan saat memproses.'],
        'sudah_diproses' => ['info', 'Pengajuan ini sudah diproses sebelumnya.'],
    ];
    if (isset($map[$_GET['aksi']])) {
        [$cls, $msg] = $map[$_GET['aksi']];
        echo "<div class='alert alert-$cls'><strong>Info!</strong> $msg</div>";
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                <em class="fa fa-gavel"></em> Daftar Pengajuan Banding
                <?php if ($pending_count > 0): ?>
                    <span class="badge" style="background:#d9534f;"><?php echo $pending_count; ?> menunggu</span>
                <?php endif; ?>
            </div>
            <div class="panel-body">
                <p class="text-muted">
                    Tinjau keluhan peserta magang yang merasa kehadirannya terhitung salah (misalnya hari libur nasional belum tercatat).
                    Jika setuju, klik <strong>Setujui / Bypass</strong> agar tombol unduh sertifikat muncul di profil peserta.
                    Anda juga dapat menambah hari libur di <a href="index.php?page=pengaturan">Pengaturan</a> — perhitungan akan otomatis diperbarui.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Peserta</th>
                                <th>Tanggal Ajuan</th>
                                <th>Catatan Keluhan</th>
                                <th>PDF Presensi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $sql = "SELECT b.*, m.nama, m.nim, m.mulai_magang, m.akhir_magang
                            FROM tbl_pengajuan_banding b
                            JOIN tbl_mahasiswa m ON m.id_mahasiswa = b.id_mahasiswa
                            ORDER BY FIELD(b.status, 'pending', 'disetujui', 'ditolak'), b.tanggal_ajuan DESC";
                        $hasil = mysqli_query($kon, $sql);
                        $no = 0;
                        if ($hasil && mysqli_num_rows($hasil) > 0):
                            while ($row = mysqli_fetch_assoc($hasil)):
                                $no++;
                                $status_label = [
                                    'pending' => '<span class="label label-warning">Menunggu</span>',
                                    'disetujui' => '<span class="label label-success">Disetujui</span>',
                                    'ditolak' => '<span class="label label-danger">Ditolak</span>',
                                ];
                                $file_url = 'apps/sertifikat/banding/' . htmlspecialchars($row['file_presensi']);
                        ?>
                            <tr>
                                <td><?php echo $no; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['nama']); ?></strong><br>
                                    <small>NIM: <?php echo htmlspecialchars($row['nim']); ?></small>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal_ajuan'])); ?></td>
                                <td style="max-width:220px;"><?php echo nl2br(htmlspecialchars($row['catatan'])); ?></td>
                                <td>
                                    <a href="<?php echo $file_url; ?>" target="_blank" class="btn btn-xs btn-default">
                                        <i class="fa fa-file-pdf-o"></i> Buka PDF
                                    </a>
                                </td>
                                <td><?php echo $status_label[$row['status']] ?? $row['status']; ?></td>
                                <td style="min-width:200px;">
                                    <?php if ($row['status'] === 'pending'): ?>
                                    <form action="apps/sertifikat/banding_action.php" method="post" style="margin-bottom:6px;">
                                        <input type="hidden" name="id_banding" value="<?php echo (int) $row['id_banding']; ?>">
                                        <input type="hidden" name="aksi_banding" value="setujui">
                                        <input type="text" name="catatan_admin" class="form-control input-sm" placeholder="Catatan admin (opsional)" style="margin-bottom:4px;">
                                        <button type="submit" class="btn btn-success btn-sm btn-block" onclick="return confirm('Setujui banding dan aktifkan unduh sertifikat?');">
                                            <i class="fa fa-check"></i> Setujui / Bypass
                                        </button>
                                    </form>
                                    <form action="apps/sertifikat/banding_action.php" method="post">
                                        <input type="hidden" name="id_banding" value="<?php echo (int) $row['id_banding']; ?>">
                                        <input type="hidden" name="aksi_banding" value="tolak">
                                        <input type="text" name="catatan_admin" class="form-control input-sm" placeholder="Alasan penolakan" style="margin-bottom:4px;">
                                        <button type="submit" class="btn btn-danger btn-sm btn-block" onclick="return confirm('Tolak pengajuan banding ini?');">
                                            <i class="fa fa-times"></i> Tolak
                                        </button>
                                    </form>
                                    <?php else: ?>
                                        <?php if (!empty($row['catatan_admin'])): ?>
                                            <small class="text-muted"><?php echo nl2br(htmlspecialchars($row['catatan_admin'])); ?></small><br>
                                        <?php endif; ?>
                                        <small>Ditinjau: <?php echo $row['tanggal_tinjau'] ? date('d/m/Y H:i', strtotime($row['tanggal_tinjau'])) : '-'; ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr><td colspan="7" class="text-center text-muted">Belum ada pengajuan banding.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
