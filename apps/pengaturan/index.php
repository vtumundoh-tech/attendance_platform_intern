<?php
if ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Pengaturan Website</li>
    </ol>
</div>
<!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Profil Instansi
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <?php

                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Pengaturan Website Telah Diupdate</div>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Pengaturan Website Gagal Diupdate</div>";
                    }
                }

                if (isset($_GET['absen'])) {
                    if ($_GET['absen'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Pengaturan Presensi Telah Diupdate</div>";
                    } else if ($_GET['absen'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Pengaturan Presensi Gagal Diupdate</div>";
                    }
                }

                if (isset($_GET['fonnte'])) {
                    if ($_GET['fonnte'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Pengaturan Notifikasi Fonnte Telah Diupdate</div>";
                    } else if ($_GET['fonnte'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Pengaturan Notifikasi Fonnte Gagal Diupdate</div>";
                    }
                }

                if (isset($_GET['fonnte_kirim'])) {
                    if ($_GET['fonnte_kirim'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Jadwal Notifikasi Telah Dikirim ke Fonnte</div>";
                    } else if ($_GET['fonnte_kirim'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Terjadi Kesalahan Saat Mengirim ke Fonnte</div>";
                    }
                }

                if (isset($_GET['sertifikat'])) {
                    if ($_GET['sertifikat'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Pengaturan Sertifikat Magang telah disimpan. Perhitungan kehadiran peserta otomatis mengikuti nilai baru.</div>";
                    } else if ($_GET['sertifikat'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Pengaturan Sertifikat Magang gagal disimpan</div>";
                    }
                }

                if (isset($_GET['libur'])) {
                    if ($_GET['libur'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Hari libur ditambahkan. Perhitungan kehadiran peserta magang otomatis diperbarui.</div>";
                    } else if ($_GET['libur'] == 'hapus_berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Hari libur dihapus. Perhitungan kehadiran otomatis diperbarui.</div>";
                    } else if ($_GET['libur'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Operasi hari libur gagal</div>";
                    }
                }
                ?>

                <?php
                //Include database
                include 'config/database.php';
                //Mengambil data profil aplikasi
                $hasil = mysqli_query($kon, "select * from tbl_site order by nama_instansi desc limit 1");
                $data = mysqli_fetch_array($hasil);
                ?>

                <form action="apps/pengaturan/edit.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="hidden" class="form-control" value="<?php echo $data['id_site']; ?>" name="id">
                    </div>
                    <div class="form-group">
                        <label>Nama Instansi :</label>
                        <input type="text" class="form-control" value="<?php echo $data['nama_instansi']; ?>"
                            name="nama_instansi" placeholder="Masukan Nama Instansi" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Ketua (Pimpinan) :</label>
                        <input type="text" class="form-control" value="<?php echo $data['pimpinan']; ?>" name="pimpinan"
                            placeholder="Masukan Nama Ketua" required>
                    </div>
                    <div class="form-group">
                        <label>Human Capital :</label>
                        <input type="text" class="form-control" value="<?php echo $data['pembimbing']; ?>" name="pembimbing"
                            placeholder="Masukan Nama Pembimbing Magang (HR)" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat :</label>
                        <input type="text" class="form-control" value="<?php echo $data['alamat']; ?>"
                            placeholder="Masukan Alamat Instansi" name="alamat">
                    </div>
                    <div class="form-group">
                        <label>No Telp :</label>
                        <input type="text" class="form-control" value="<?php echo $data['no_telp']; ?>"
                            placeholder="Masukan Nomor Telp" name="no_telp">
                    </div>
                    <div class="form-group">
                        <label>Website :</label>
                        <input type="text" class="form-control" value="<?php echo $data['website']; ?>"
                            placeholder="Masukan Alamat Website" name="website">
                    </div>
                    <div class="form-group">
                        <div id="msg"></div>
                        <label>Logo :</label>
                        <input type="file" name="logo" class="file" accept="image/*">
                        <div class="input-group my-3">
                            <input type="text" class="form-control" disabled placeholder="Upload Gambar" id="file">
                            <div class="input-group-append">
                                <button type="button" id="pilih_logo" class="browse btn btn-info"><i
                                        class="fa fa-search"></i> Pilih Logo</button>
                            </div>
                        </div>
                        <?php
                        $logo_path = "apps/pengaturan/logo/" . $data['logo'];
                        if (!empty($data['logo']) && file_exists($logo_path)) {
                            echo '<img src="' . $logo_path . '" id="preview" width="10%" class="img-thumbnail">';
                        } else {
                            echo '<div class="alert alert-info">Tidak ada logo yang tersimpan. Silakan upload logo baru.</div>';
                        }
                        ?>
                        <small class="form-text text-muted">Format yang diizinkan: PNG, JPG, JPEG. Ukuran maksimal:
                            2MB</small>
                        <input type="hidden" name="logo_sebelumnya" value="<?php echo $data['logo']; ?>" />
                    </div>
                    <!-- TTD form-group DI SINI, sebelum tombol submit -->
                    <div class="form-group">
                        <label>Tanda Tangan Digital Pimpinan :</label>
                        <input type="file" name="ttd_pimpinan" class="file_ttd" accept="image/png,image/jpeg">
                        <div class="input-group my-3">
                            <input type="text" class="form-control" disabled placeholder="Upload Tanda Tangan" id="file_ttd_display">
                            <div class="input-group-append">
                                <button type="button" id="pilih_ttd" class="btn btn-info">
                                    <i class="fa fa-search"></i> Pilih File
                                </button>
                            </div>
                        </div>
                        <?php
                        $ttd_path_view = "apps/pengaturan/ttd/" . $data['ttd_pimpinan'];
                        if (!empty($data['ttd_pimpinan']) && file_exists($ttd_path_view)) {
                            echo '<div style="background:#f9f9f9;padding:10px;display:inline-block;border:1px solid #ddd;border-radius:4px;">';
                            echo '<img src="' . $ttd_path_view . '" id="preview_ttd" style="max-height:80px;max-width:200px;">';
                            echo '</div>';
                        } else {
                            echo '<img id="preview_ttd" src="" style="display:none;max-height:80px;max-width:200px;background:#f9f9f9;border:1px solid #ddd;">';
                            echo '<div id="no_ttd_msg" class="alert alert-info">Belum ada tanda tangan tersimpan. Silakan upload.</div>';
                        }
                        ?>
                        <input type="hidden" name="ttd_sebelumnya" value="<?php echo htmlspecialchars($data['ttd_pimpinan'] ?? ''); ?>">
                        <small class="form-text text-muted">PNG dengan latar transparan/putih. Maks 2MB.</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-success" name="ubah_aplikasi">
                            <i class="fa fa-edit"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Pengaturan Presensi
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">

                <?php
                // Profil Aplikasi
                include 'config/database.php';
                $query = mysqli_query($kon, "select * from tbl_setting_absensi limit 1");
                $row = mysqli_fetch_array($query);
                $id_waktu = $row['id_waktu'];
                $mulai_absen = $row['mulai_absen'];
                $akhir_absen = $row['akhir_absen'];
                $jam_mulai_pulang = isset($row['jam_mulai_pulang']) ? $row['jam_mulai_pulang'] : '17:00:00';
                $batas_pulang = isset($row['batas_pulang']) ? $row['batas_pulang'] : '18:00:00';
                $kantor_latitude = isset($row['kantor_latitude']) ? $row['kantor_latitude'] : '1.54545';
                $kantor_longitude = isset($row['kantor_longitude']) ? $row['kantor_longitude'] : '124.92220';
                $radius_meter = isset($row['radius_meter']) ? (int) $row['radius_meter'] : 600;
                ?>
                <p class="text-muted small">Hari <strong>Sabtu dan Minggu</strong> Presensi dinonaktifkan di kode aplikasi (bukan dari pengaturan di halaman ini).</p>
                <p class="text-muted small"><strong>Logika Presensi Pagi:</strong> Jika presensi ≤ Batas Presensi Pagi → Hadir, Jika > Batas tapi ≤ Batas+10 menit → Terlambat, Jika > Batas+20 menit → Tidak Hadir</p>

                <form action="apps/pengaturan/absensi.php" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="hidden" class="form-control" value="<?php echo $id_waktu ?>" name="id_waktu">
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Jam Mulai Presensi Pagi :</label>
                                <input type="time" class="form-control" value="<?php echo substr($mulai_absen, 0, 5) ?>" name="mulai_absen"
                                    placeholder="Masukan Jam Mulai Absensi Pagi" required>
                                <small class="text-muted">Jam kapan mahasiswa mulai bisa melakukan Presensi pagi</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Batas Presensi Pagi :</label>
                                <input type="time" class="form-control" value="<?php echo substr($akhir_absen, 0, 5) ?>" name="batas_absensi_pagi"
                                    placeholder="Masukan Batas Absensi Pagi" required>
                                <small class="text-muted">Batas jam tanpa terlambat. +10 min = Terlambat, +20 min = Tidak Hadir</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Jam Mulai Presensi Pulang :</label>
                                <input type="time" class="form-control" value="<?php echo substr($jam_mulai_pulang, 0, 5) ?>" name="jam_mulai_pulang"
                                    placeholder="Masukan Jam Mulai Presensi Pulang" required>
                                <small class="text-muted">Jam kapan mahasiswa mulai bisa melakukan Presensi pulang</small>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Batas Presensi Pulang :</label>
                                <input type="time" class="form-control" value="<?php echo substr($batas_pulang, 0, 5) ?>" name="batas_pulang"
                                    placeholder="Masukan Batas Presensi Pulang" required>
                                <small class="text-muted">Batas akhir jam melakukan Presensi pulang</small>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Latitude titik kantor (presensi hadir):</label>
                        <input type="text" class="form-control" name="kantor_latitude"
                            value="<?php echo htmlspecialchars($kantor_latitude); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Longitude titik kantor:</label>
                        <input type="text" class="form-control" name="kantor_longitude"
                            value="<?php echo htmlspecialchars($kantor_longitude); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jarak maksimal presensi hadir (meter):</label>
                        <input type="number" class="form-control" name="radius_meter" min="1" max="50000"
                            value="<?php echo (int) $radius_meter; ?>" required>
                    </div>
                    <?php if (!isset($row['kantor_latitude'])): ?>
                        <div class="alert alert-warning">Jalankan migrasi SQL
                            <code>database/add_setting_absensi_lokasi.sql</code> agar kolom lokasi tersimpan di database.
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" name="ubah_absen"><i class="fa fa-edit"></i>
                            Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Pengaturan Notifikasi Fonnte (Grup)
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <?php
                $query_fonnte = mysqli_query($kon, "select * from tbl_fonnte_setting limit 1");
                $row_fonnte = mysqli_fetch_array($query_fonnte);
                ?>
                <p class="text-muted small">Atur jadwal pengiriman pesan pengingat presensi ke grup WhatsApp Anda.</p>
                <form action="apps/pengaturan/fonnte.php" method="post">
                    <div class="form-group">
                        <label>Target Grup ID :</label>
                        <input type="text" class="form-control" name="target_grup"
                            value="<?php echo htmlspecialchars($row_fonnte['target_grup']); ?>"
                            placeholder="120363289080588508@g.us" required>
                        <small class="text-muted">Masukkan ID grup WhatsApp, contoh: 120363289080588508@g.us</small>
                    </div>
                    <div class="form-group">
                        <label>Tanggal Jadwal Pengingat :</label>
                        <input type="date" class="form-control" name="jadwal_tanggal"
                            value="<?php echo htmlspecialchars($row_fonnte['jadwal_tanggal']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Jam Jadwal Pengingat (WITA):</label>
                        <input type="time" class="form-control" name="jadwal_jam"
                            value="<?php echo htmlspecialchars($row_fonnte['jadwal_jam']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Isi Pesan :</label>
                        <textarea class="form-control" name="pesan_grup" rows="4"
                            required><?php echo htmlspecialchars($row_fonnte['pesan_grup']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" name="ubah_fonnte"><i class="fa fa-edit"></i>
                            Simpan Pengaturan</button>
                        <a href="apps/pengaturan/kirim_fonnte.php" class="btn btn-primary"><i
                                class="fa fa-paper-plane"></i> Kirim Jadwal ke Fonnte Sekarang</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$sertifikat_tables_ok = mysqli_num_rows(mysqli_query($kon, "SHOW TABLES LIKE 'tbl_setting_sertifikat'")) > 0;
if (!$sertifikat_tables_ok):
?>
<div class="row">
    <div class="col-md-12">
        <div class="alert alert-warning">
            <strong>Modul Sertifikat Magang belum diaktifkan.</strong>
            Jalankan migrasi SQL <code>database/migration_sertifikat_magang.sql</code> di phpMyAdmin untuk mengaktifkan pengaturan sertifikat dan hari libur.
        </div>
    </div>
</div>
<?php else:
include_once 'config/sertifikat_helper.php';
$setting_sertifikat = sertifikat_get_setting($kon);
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Pengaturan Sertifikat Magang
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <p class="text-muted small">
                    Tentukan persentase minimum kehadiran bulanan sebagai syarat unduh sertifikat.
                    Perhitungan: <strong>(Hadir + Izin) ÷ Hari Kerja</strong> per bulan (Senin–Jumat, dikurangi hari libur nasional).
                    Status <em>Tidak Hadir</em> tidak dihitung. Nilai dapat diubah kapan saja; peserta melihat hasil terbaru di profil.
                </p>
                <form action="apps/pengaturan/sertifikat.php" method="post">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Minimum Kehadiran Bulanan (%):</label>
                                <input type="number" name="min_persentase_kehadiran" class="form-control"
                                    min="1" max="100" step="0.01" required
                                    value="<?php echo htmlspecialchars($setting_sertifikat['min_persentase_kehadiran']); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <label>Batas Upload PDF Banding (MB):</label>
                                <input type="number" name="max_upload_banding_mb" class="form-control"
                                    min="1" max="50" required
                                    value="<?php echo (int) $setting_sertifikat['max_upload_banding_mb']; ?>">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="simpan_sertifikat" class="btn btn-success">
                        <i class="fa fa-save"></i> Simpan Pengaturan Sertifikat
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Manajemen Hari Libur Nasional
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <p class="text-muted small">
                    Masukkan tanggal libur kapan saja (sebelum atau sesudah periode magang berakhir).
                    Sistem otomatis mengurangi hari kerja pada bulan terkait. Jika admin terlambat mengisi,
                    peserta dapat mengajukan banding; setelah tanggal libur disimpan, tombol sertifikat dapat aktif otomatis.
                </p>
                <form action="apps/pengaturan/hari_libur.php" method="post" class="form-inline" style="margin-bottom:16px;">
                    <input type="hidden" name="tambah_libur" value="1">
                    <div class="form-group" style="margin-right:8px;">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="form-group" style="margin-right:8px;">
                        <label>Keterangan</label>
                        <input type="text" name="keterangan" class="form-control" placeholder="Contoh: Hari Kemerdekaan">
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:24px;">
                        <i class="fa fa-plus"></i> Tambah Hari Libur
                    </button>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $no_libur = 0;
                        $q_libur = @mysqli_query($kon, 'SELECT * FROM tbl_hari_libur ORDER BY tanggal DESC');
                        if ($q_libur && mysqli_num_rows($q_libur) > 0):
                            while ($row_libur = mysqli_fetch_assoc($q_libur)):
                                $no_libur++;
                        ?>
                            <tr>
                                <td><?php echo $no_libur; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row_libur['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($row_libur['keterangan'] ?: '-'); ?></td>
                                <td>
                                    <a href="apps/pengaturan/hari_libur.php?hapus=<?php echo (int) $row_libur['id_libur']; ?>"
                                       class="btn btn-danger btn-xs"
                                       onclick="return confirm('Hapus hari libur ini? Perhitungan kehadiran akan diperbarui.');">
                                        <i class="fa fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr><td colspan="4" class="text-center text-muted">Belum ada hari libur terdaftar.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading clearfix">
                Tambah
                <button type="button" class="btn btn-default btn-xs pull-right minimize-btn" style="margin-top:-3px;"><i
                        class="fa fa-minus"></i></button>
            </div>
            <div class="panel-body">
                <form action="" method="post">
                    <div class="form-group">
                        <label for="universitas">Tambah Universitas :</label>
                        <input type="text" class="form-control" id="universitas" name="universitas"
                            placeholder="Masukkan nama universitas">
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Universitas</button>
                </form>
                <hr>
                <form action="" method="post">
                    <div class="form-group">
                        <label for="departemen">Tambah Departemen/Unit Kerja :</label>
                        <input type="text" class="form-control" id="departemen" name="departemen"
                            placeholder="Masukkan nama departemen/unit kerja">
                    </div>
                    <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Simpan Departemen</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .file,
    .file_ttd {
        visibility: hidden;
        position: absolute;
    }
</style>

<script>
    // ── Logo ─────────────────────────────────────────────────────────────
    $(document).on("click", "#pilih_logo", function () {
        $(".file").trigger("click");
    });

    $('.file').change(function (e) {
        var fileName = e.target.files[0].name;
        $("#file").val(fileName);
        var reader = new FileReader();
        reader.onload = function (e) {
            var preview = document.getElementById("preview");
            if (preview) preview.src = e.target.result; // aman jika preview belum ada
        };
        reader.readAsDataURL(this.files[0]);
    });

    // ── Tanda tangan pimpinan ─────────────────────────────────────────────
    $(document).on("click", "#pilih_ttd", function () {
        $(".file_ttd").trigger("click");
    });

    $('.file_ttd').change(function (e) {
        var file = e.target.files[0];
        if (!file) return;
        $("#file_ttd_display").val(file.name);
        $("#no_ttd_msg").hide();
        var reader = new FileReader();
        reader.onload = function (e) {
            $("#preview_ttd").attr("src", e.target.result).show();
        };
        reader.readAsDataURL(file);
    });

    // ── Minimize panel ────────────────────────────────────────────────────
    $(document).on('click', '.minimize-btn', function () {
        var $panel = $(this).closest('.panel');
        $panel.find('.panel-body').slideToggle(200);
        $(this).find('i').toggleClass('fa-minus fa-plus');
    });
</script>

<?php
// Proses simpan universitas
if (isset($_POST['universitas']) && !empty(trim($_POST['universitas']))) {
    include 'config/database.php';
    $nama_universitas = mysqli_real_escape_string($kon, trim($_POST['universitas']));
    $query = "INSERT INTO tbl_universitas (nama_universitas) VALUES ('$nama_universitas')";
    if (mysqli_query($kon, $query)) {
        echo "<div class='alert alert-success'>Universitas berhasil ditambahkan!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menambahkan universitas.</div>";
    }
}
// Proses simpan departemen
if (isset($_POST['departemen']) && !empty(trim($_POST['departemen']))) {
    include 'config/database.php';
    $nama_departemen = mysqli_real_escape_string($kon, trim($_POST['departemen']));
    $query = "INSERT INTO tbl_departemen (nama_departemen) VALUES ('$nama_departemen')";
    if (mysqli_query($kon, $query)) {
        echo "<div class='alert alert-success'>Departemen/Unit Kerja berhasil ditambahkan!</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menambahkan departemen/unit kerja.</div>";
    }
}