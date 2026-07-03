<?php
/**
 * Panel Sertifikat Magang di halaman profil peserta.
 * Di-include dari apps/pengguna/profil.php
 */
if (!isset($data) || !isset($kon)) {
    return;
}

$tbl_ok = mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'tbl_setting_sertifikat'")) > 0;
if (!$tbl_ok) {
    return;
}

include_once __DIR__ . '/../../config/sertifikat_helper.php';

$setting_sert = sertifikat_get_setting($kon);
$cek_sert = sertifikat_boleh_download($kon, $data);
$rekap = $cek_sert['rekap'];
$banding_pending = sertifikat_get_banding_pending($kon, (int) $data['id_mahasiswa']);
$banding_terakhir = sertifikat_get_banding_terakhir($kon, (int) $data['id_mahasiswa']);
$max_mb = (int) $setting_sert['max_upload_banding_mb'];
?>

<div class="row" style="margin-top:20px;">
    <div class="col-md-12">
        <div class="panel panel-primary">
            <div class="panel-heading" style="background:#337ab7;color:#fff;">
                <strong><i class="fa fa-certificate"></i> Sertifikat Magang</strong>
            </div>
            <div class="panel-body">

                <?php if (isset($_GET['banding'])): ?>
                    <?php if ($_GET['banding'] === 'berhasil'): ?>
                        <div class="alert alert-success">Pengajuan banding berhasil dikirim. Admin akan meninjaunya.</div>
                    <?php elseif ($_GET['banding'] === 'gagal'): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['msg'] ?? 'Gagal mengirim banding.'); ?></div>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (!$rekap['periode_berakhir']): ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        Periode magang Anda berakhir pada <strong><?php echo date('d/m/Y', strtotime($data['akhir_magang'])); ?></strong>.
                        Fitur sertifikat akan tersedia setelah tanggal tersebut.
                    </div>
                <?php else: ?>

                    <p class="text-muted">
                        Syarat kelulusan: kehadiran bulanan minimal <strong><?php echo number_format($rekap['min_persen'], 0); ?>%</strong>
                        (Hadir + Izin ÷ Hari Kerja). Hari kerja = Senin–Jumat, tidak termasuk hari libur nasional.
                        Status <em>Tidak Hadir</em> tidak dihitung sebagai kehadiran.
                    </p>
                    <p class="text-muted">
                        Bagi mereka yang <strong><em>izin</em></strong> tidak akan berpengaruh ke perhitungan kehadiran.
                    </p>

                    <?php if (!empty($rekap['months'])): ?>
                    <div class="table-responsive" style="margin-bottom:16px;">
                        <table class="table table-bordered table-condensed">
                            <thead>
                                <tr class="active">
                                    <th>Bulan</th>
                                    <th class="text-center">Hari Kerja</th>
                                    <th class="text-center">Kehadiran</th>
                                    <th class="text-center">Persentase</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($rekap['months'] as $bln): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($bln['label']); ?></td>
                                    <td class="text-center"><?php echo (int) $bln['workdays']; ?></td>
                                    <td class="text-center"><?php echo (int) $bln['hadir_izin']; ?></td>
                                    <td class="text-center"><?php echo number_format($bln['persen'], 2); ?>%</td>
                                    <td class="text-center">
                                        <?php if ($bln['lolos']): ?>
                                            <span class="label label-success">Lolos</span>
                                        <?php else: ?>
                                            <span class="label label-danger">Di bawah syarat</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                    <?php if ($cek_sert['boleh']): ?>
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i>
                            <?php if ($cek_sert['sumber'] === 'bypass'): ?>
                                Anda dapat mengunduh sertifikat (persetujuan admin / banding disetujui).
                            <?php else: ?>
                                Selamat! Anda memenuhi syarat kehadiran bulanan.
                            <?php endif; ?>
                        </div>
                        <a href="apps/cetak/cetak_sertifikat.php" target="_blank" class="btn btn-success btn-lg">
                            <i class="fa fa-download"></i> Unduh Sertifikat Magang (PDF)
                        </a>
                    <?php else: ?>

                        <div class="alert alert-warning">
                            <strong>Anda belum memenuhi syarat kehadiran bulanan.</strong><br>
                            <?php echo htmlspecialchars($cek_sert['alasan']); ?>
                        </div>

                        <p>
                            Jika Anda merasa kehadiran tercatat salah (misalnya ada hari libur nasional yang belum dihitung sistem),
                            unduh rekap presensi di bawah lalu ajukan banding melalui formulir.
                            Bagi keluhan di luar hal tersebut, silakan menghadap admin secara langsung.
                        </p>

                        <p>
                            <a href="apps/cetak/cetak_absensi.php?id_mahasiswa=<?php echo (int) $data['id_mahasiswa']; ?>&tanggal_awal=<?php echo urlencode($data['mulai_magang']); ?>&tanggal_akhir=<?php echo urlencode($data['akhir_magang']); ?>"
                               target="_blank" class="btn btn-default">
                                <i class="fa fa-file-pdf-o"></i> Unduh Rekap Presensi (PDF)
                            </a>
                        </p>

                        <?php if ($banding_pending): ?>
                            <div class="alert alert-info">
                                <i class="fa fa-clock-o"></i>
                                Pengajuan banding Anda sedang ditinjau admin (<?php echo date('d/m/Y H:i', strtotime($banding_pending['tanggal_ajuan'])); ?>).
                            </div>
                        <?php elseif ($banding_terakhir && $banding_terakhir['status'] === 'ditolak'): ?>
                            <div class="alert alert-danger">
                                <strong>Pengajuan banding ditolak.</strong>
                                <?php if (!empty($banding_terakhir['catatan_admin'])): ?>
                                    <br>Catatan admin: <?php echo nl2br(htmlspecialchars($banding_terakhir['catatan_admin'])); ?>
                                <?php endif; ?>
                                <br>Silakan menghadap admin untuk konfirmasi lebih lanjut.
                            </div>
                            <button type="button" class="btn btn-warning" data-toggle="collapse" data-target="#formBanding">
                                <i class="fa fa-gavel"></i> Ajukan Banding Lagi
                            </button>
                        <?php else: ?>
                            <button type="button" class="btn btn-warning" data-toggle="collapse" data-target="#formBanding">
                                <i class="fa fa-gavel"></i> Ajukan Banding
                            </button>
                        <?php endif; ?>

                        <div id="formBanding" class="collapse<?php echo ($banding_terakhir && $banding_terakhir['status'] === 'ditolak' && isset($_GET['banding'])) ? ' in' : ''; ?>" style="margin-top:16px;">
                            <div class="well">
                                <h4><i class="fa fa-edit"></i> Formulir Pengajuan Banding</h4>
                                <p class="text-muted small">
                                    Unggah PDF riwayat presensi (maks. <?php echo $max_mb; ?> MB) dan jelaskan keluhan Anda.
                                    Admin akan meninjau dan dapat menyetujui agar tombol unduh sertifikat aktif.
                                </p>
                                <form action="apps/sertifikat/ajukan_banding.php" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="ajukan_banding" value="1">
                                    <div class="form-group">
                                        <label>Catatan / Keluhan <span class="text-danger">*</span></label>
                                        <textarea name="catatan" class="form-control" rows="4" required
                                            placeholder="Contoh: Tanggal 17 Agustus adalah hari libur nasional, mohon ditinjau ulang perhitungan kehadiran bulan Agustus."></textarea>
                                    </div>
                                    <div class="form-group">
                                        <label>PDF Riwayat Presensi <span class="text-danger">*</span></label>
                                        <input type="file" name="file_presensi" class="form-control" accept="application/pdf,.pdf" required>
                                        <small class="text-muted">Format PDF, maksimal <?php echo $max_mb; ?> MB.</small>
                                    </div>
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Kirim pengajuan banding?');">
                                        <i class="fa fa-paper-plane"></i> Kirim Pengajuan
                                    </button>
                                </form>
                            </div>
                        </div>

                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
