<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Profil</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
            Profil
            <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span></div>
            <div class="panel-body">

            <?php
                //Menghubungkan database
                include 'config/database.php';
                //Mengambil kode_pengguna dari session
                $kode_pengguna=isset($_SESSION["kode_pengguna"]) ? $_SESSION["kode_pengguna"] : '';
                //Query untuk menampilkan data mahasiswa dari tbl_mahasiswa
                $sql="SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa='$kode_pengguna' LIMIT 1";
                //Menyimpan hasil query
                $hasil=mysqli_query($kon,$sql);
                if (!$hasil) {
                    die('Query Error: ' . mysqli_error($kon));
                }
                //Menyimpan hasil jadi array
                $data = mysqli_fetch_array($hasil);
            ?>

            <?php
                //Validasi Untuk menampilkan memberitahuan saat mahasiswa mengubah password
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Ubah Password berhasil</div>";
                    }else if ($_GET['pengguna']=='gagal'){
                        echo"<div class='alert alert-danger'><strong>Gagal!</strong> Ubah Password gagal</div>";
                    }    
                }
            ?>

            <?php
// TAMBAHAN: Notifikasi hasil submit revisi berkas
if (isset($_GET['revisi'])) {
    if ($_GET['revisi'] == 'berhasil') {
        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Revisi berkas berhasil dikirim.</div>";
    } elseif ($_GET['revisi'] == 'gagal') {
        $pesan_revisi = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'Terjadi kesalahan saat mengirim revisi.';
        echo "<div class='alert alert-danger'><strong>Gagal!</strong> " . $pesan_revisi . "</div>";
    }
}
?>
                
                <div class="table-responsive">
                <table class="table">
                    <tbody>
                        <tr>
                            <td>Nama</td>
                            <td width="75%">: <?php echo $data['nama'];?></td>
                        </tr>
                        <tr>
                            <td>Nomor Induk Mahasiswa</td>
                            <td width="75%">: <?php echo $data['nim'];?></td>
                        </tr>
                        <tr>
                            <td>Universitas</td>
                            <td width="75%">: <?php echo $data['universitas'];?></td>
                        </tr>
                        <tr>
                            <td>Jurusan</td>
                            <td width="75%">: <?php echo $data['jurusan']; ?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Masuk</td>
                            <td width="75%">: <?php echo date('d/m/Y', strtotime($data["mulai_magang"]));?></td>
                        </tr>
                        <tr>
                            <td>Tanggal Selesai</td>
                            <td width="75%">: <?php echo date('d/m/Y', strtotime($data["akhir_magang"]));?></td>
                        </tr>
                        <tr>
                            <td>No Telp</td>
                            <td width="75%">: <?php echo $data['no_telp'];?></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td width="75%">: <?php echo $data['alamat'];?></td>
                        </tr>
                        <tr>
                            <td>Departemen/Unit Kerja</td>
                            <td width="75%">: <?php echo $data['departemen_unitkerja']; ?></td>
                        </tr>
                        <tr>
                            <td>Tempat, Tanggal Lahir</td>
                            <td width="75%">: 
                                <?php 
                                    if (!empty($data['tempat_lahir']) && !empty($data['tanggal_lahir'])) {
                                        echo $data['tempat_lahir'] . ', ' . date('d/m/Y', strtotime($data['tanggal_lahir']));
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Agama</td>
                            <td width="75%">: <?php echo $data['agama'];?></td>
                        </tr>
                        <tr>
                            <td>Nama Guru/Dosen Pembimbing</td>
                            <td width="75%">: <?php echo $data['nama_pembimbing'];?></td>
                        </tr>
                        <tr>
                            <td>No HP Guru/Dosen Pembimbing</td>
                            <td width="75%">: <?php echo $data['no_hp_pembimbing'];?></td>
                        </tr>
                        <tr>
                            <td>Foto</td>
                            <td width="20%">: <img src="/valendy_presensi/apps/mahasiswa/foto/<?php echo $data['foto']; ?>" id="preview" width="25%" class="rounded" alt="Foto Profil"></td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <div class="form-group">
                <button kode_mahasiswa="<?php echo $data['kode_mahasiswa'];?>" class="password btn btn-info btn-circle" ><i class="fa fa-key"></i>Password</button>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<?php
// TAMBAHAN: Panel Permintaan Revisi Berkas dari Admin
$revisi_fields_panel = [];
if (!empty($data['revisi_berkas'])) {
    $revisi_fields_panel = json_decode($data['revisi_berkas'], true);
    if (!is_array($revisi_fields_panel)) $revisi_fields_panel = [];
}
if (!empty($revisi_fields_panel)):
?>
<div class="row" style="margin-top: 20px;">
    <div class="col-md-12">
        <div class="panel panel-danger">
            <div class="panel-heading" style="background-color:#d9534f; color:#fff;">
                <strong><i class="fa fa-warning"></i> Permintaan Perbaikan Data / Berkas dari Admin</strong>
            </div>
            <div class="panel-body">
                <?php if (!empty($data['catatan_revisi'])): ?>
                <div class="alert alert-warning">
                    <strong><i class="fa fa-comment"></i> Catatan Admin:</strong><br>
                    <?php echo nl2br(htmlspecialchars($data['catatan_revisi'])); ?>
                </div>
                <?php endif; ?>
                <p class="text-muted">
                    Admin meminta Anda melengkapi atau memperbaiki data/berkas berikut.
                    Isi ulang dan klik <strong>Kirim Revisi</strong>.
                </p>
                <form action="apps/pengguna/submit_revisi_berkas.php" method="post" enctype="multipart/form-data">
                    <?php
                    $meta_field = [
                        'nama'                 => ['label' => 'Nama Lengkap',                          'type' => 'text'],
                        'universitas'          => ['label' => 'Universitas',                           'type' => 'text'],
                        'departemen_unitkerja' => ['label' => 'Departemen / Unit Kerja',               'type' => 'text'],
                        'jurusan'              => ['label' => 'Jurusan',                               'type' => 'text'],
                        'nim'                  => ['label' => 'Nomor Induk Mahasiswa',                 'type' => 'text'],
                        'mulai_magang'         => ['label' => 'Tanggal Mulai Magang',                  'type' => 'date'],
                        'akhir_magang'         => ['label' => 'Tanggal Akhir Magang',                  'type' => 'date'],
                        'alamat'               => ['label' => 'Alamat',                                'type' => 'textarea'],
                        'no_telp'              => ['label' => 'Nomor Telepon',                         'type' => 'text'],
                        'tempat_lahir'         => ['label' => 'Tempat Lahir',                          'type' => 'text'],
                        'tanggal_lahir'        => ['label' => 'Tanggal Lahir',                         'type' => 'date'],
                        'agama'                => ['label' => 'Agama',                                 'type' => 'dropdown',
                                                   'options' => ['Islam','Kristen Protestan','Kristen Katolik','Hindu','Buddha','Konghucu']],
                        'no_hp_ortu'           => ['label' => 'Nomor HP Orang Tua',                   'type' => 'text'],
                        'nama_pembimbing'      => ['label' => 'Nama Guru / Dosen Pembimbing',          'type' => 'text'],
                        'no_hp_pembimbing'     => ['label' => 'Nomor HP Guru / Dosen Pembimbing',      'type' => 'text'],
                        'foto'                 => ['label' => 'Foto Profil',                           'type' => 'image'],
                        'scan_ktp_kk' => ['label' => 'Scan KTP / KK',                        'type' => 'file', 'dir' => 'apps/mahasiswa/ktp_mahasiswa/'],
'scan_bpjs'   => ['label' => 'Scan BPJS Kesehatan / KIS / Asuransi', 'type' => 'file', 'dir' => 'apps/mahasiswa/bpjs_mahasiswa/'],
                    ];

                    foreach ($revisi_fields_panel as $field):
                        if (!isset($meta_field[$field])) continue;
                        $meta        = $meta_field[$field];
                        $nilai_saat  = isset($data[$field]) ? $data[$field] : '';
                    ?>
                    <div class="form-group">
                        <label>
                            <strong><?php echo $meta['label']; ?></strong>
                            <span class="label label-danger" style="margin-left:6px;">Perlu Diperbaiki</span>
                        </label>

                        <?php if ($meta['type'] === 'text'): ?>
                            <input type="text" name="<?php echo $field; ?>" class="form-control"
                                   value="<?php echo htmlspecialchars($nilai_saat); ?>" required>

                        <?php elseif ($meta['type'] === 'date'): ?>
                            <input type="date" name="<?php echo $field; ?>" class="form-control"
                                   value="<?php echo htmlspecialchars($nilai_saat); ?>" required>

                        <?php elseif ($meta['type'] === 'textarea'): ?>
                            <textarea name="<?php echo $field; ?>" class="form-control" rows="3"
                                      required><?php echo htmlspecialchars($nilai_saat); ?></textarea>

                        <?php elseif ($meta['type'] === 'dropdown'): ?>
                            <select name="<?php echo $field; ?>" class="form-control" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($meta['options'] as $opt): ?>
                                <option value="<?php echo $opt; ?>"
                                    <?php echo $nilai_saat === $opt ? 'selected' : ''; ?>>
                                    <?php echo $opt; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>

                        <?php elseif ($meta['type'] === 'image'): ?>
                            <?php if (!empty($nilai_saat)): ?>
                            <div style="margin-bottom:8px;">
                                <img src="apps/mahasiswa/foto/<?php echo htmlspecialchars($nilai_saat); ?>"
                                     width="70" class="rounded" alt="Foto saat ini">
                                <small class="text-muted">&nbsp;(foto saat ini)</small>
                            </div>
                            <?php endif; ?>
                            <input type="file" name="foto" class="form-control"
                                   accept="image/png,image/jpeg,image/gif" required>

                        <?php elseif ($meta['type'] === 'file'): ?>
<?php
    $dir_berkas  = isset($meta['dir']) ? $meta['dir'] : 'apps/mahasiswa/berkas/';
    $path_cek    = $dir_berkas . $nilai_saat;
    $berkas_ada  = !empty($nilai_saat) && file_exists($path_cek);
    $ext_berkas  = !empty($nilai_saat) ? strtolower(pathinfo($nilai_saat, PATHINFO_EXTENSION)) : '';
    $tipe_berkas = in_array($ext_berkas, ['png','jpg','jpeg','gif']) ? 'image' : 'pdf';
    ?>
    <?php if (!empty($nilai_saat)): ?>
    <div style="margin-bottom:8px;">
        <?php if ($berkas_ada): ?>
        <button type="button" class="btn btn-xs btn-default btn-preview-berkas"
            data-url="<?php echo $dir_berkas . htmlspecialchars($nilai_saat); ?>"
            data-label="<?php echo htmlspecialchars($meta['label']); ?>"
            data-tipe="<?php echo $tipe_berkas; ?>">
            <i class="fa fa-<?php echo $tipe_berkas === 'image' ? 'image' : 'file-pdf-o'; ?>"></i>
            Lihat berkas saat ini
        </button>
        <?php else: ?>
        <span class="text-warning">
            <i class="fa fa-exclamation-triangle"></i>
            Berkas sebelumnya tidak ditemukan di server — silakan upload ulang.
        </span>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <input type="file" name="<?php echo $field; ?>" class="form-control"
           accept="image/png,image/jpeg,image/gif,.pdf" required>

                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>

                    <button type="submit" name="submit_revisi" class="btn btn-danger">
                        <i class="fa fa-paper-plane"></i> Kirim Revisi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Preview Berkas -->
<div class="modal fade" id="modalPreviewBerkas">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="judulPreviewBerkas"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center" id="isiPreviewBerkas">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-preview-berkas', function () {
    var url   = $(this).data('url');
    var label = $(this).data('label');
    var tipe  = $(this).data('tipe');

    $('#judulPreviewBerkas').text(label);

    if (tipe === 'image') {
        $('#isiPreviewBerkas').html(
            '<img src="' + url + '" class="img-responsive" style="max-width:100%;" alt="' + label + '">'
        );
    } else {
        $('#isiPreviewBerkas').html(
            '<iframe src="' + url + '" width="100%" height="500px" style="border:none;"></iframe>'
        );
    }

    $('#modalPreviewBerkas').modal('show');
});
</script>

<?php endif; ?>
<!-- END TAMBAHAN: Panel Revisi Berkas -->

<form method="post" style="margin-top:24px;">
  <div class="form-group row">
    <label class="col-sm-3 col-form-label">Pembimbing Magang Lapangan</label>
    <div class="col-sm-6">
      <input type="text" name="pembimbing_magang" class="form-control" value="<?php echo htmlspecialchars($data['pembimbing_magang'] ?? ''); ?>" required>
    </div>
    <div class="col-sm-3">
      <button type="submit" name="simpan_pembimbing" class="btn btn-primary">Simpan</button>
    </div>
  </div>
</form>
<?php
if (isset($_POST['simpan_pembimbing'])) {
    $pembimbing = mysqli_real_escape_string($kon, $_POST['pembimbing_magang']);
    $kode_pengguna = $_SESSION["kode_pengguna"];
    $update = mysqli_query($kon, "UPDATE tbl_mahasiswa SET pembimbing_magang='$pembimbing' WHERE kode_mahasiswa='$kode_pengguna'");
    if ($update) {
        echo "<div class='alert alert-success'>Pembimbing magang berhasil diperbarui.</div>";
        echo "<meta http-equiv='refresh' content='1'>";
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui pembimbing magang.</div>";
    }
}
?>

<?php include __DIR__ . '/../sertifikat/panel_profil.php'; ?>

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

        <div class="modal-header">
            <h4 class="modal-title" id="judul"></h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>

        <div class="modal-body">
            <div id="tampil_data">
                    <!-- Data akan di load menggunakan AJAX -->                   
            </div>  
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>

        </div>
    </div>
</div>
<!-- Modal -->

<script>
    // Setting password mahasiswa
    $('.password').on('click',function(){
        var kode_mahasiswa = $(this).attr("kode_mahasiswa");
        $.ajax({
            url: 'apps/pengguna/ubah_password.php',
            method: 'post',
            data: {kode_mahasiswa:kode_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Ubah Password';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });

    // Pop-up error password jika ada parameter password_error di URL
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if(params.has('password_error')) {
            alert(params.get('password_error'));
        }
    });
</script>