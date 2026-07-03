<?php 
    if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
    echo"<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
    }
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Peserta Magang</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
            Data Peserta Magang
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span></div>
            <div class="panel-body">
                <div class="row">
                <form action="#" method="GET">
                    <input type="hidden" name="page" value="mahasiswa"/>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <input type="text" name="cari" id="cari" class="form-control" value="<?php echo isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>" placeholder="Pencarian">
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <select name="universitas" class="form-control">
                                <option value="">-- Universitas --</option>
                                <?php
                                $res = mysqli_query($kon, "SELECT nama_universitas FROM tbl_universitas ORDER BY nama_universitas ASC");
                                while($row = mysqli_fetch_assoc($res)) {
                                    $selected = (isset($_GET['universitas']) && $_GET['universitas'] == $row['nama_universitas']) ? 'selected' : '';
                                    echo "<option value=\"{$row['nama_universitas']}\" $selected>{$row['nama_universitas']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <select name="agama" class="form-control">
                                <option value="">-- Agama --</option>
                                <?php
                                $res = mysqli_query($kon, "SELECT nama_agama FROM tbl_agama ORDER BY nama_agama ASC");
                                while($row = mysqli_fetch_assoc($res)) {
                                    $selected = (isset($_GET['agama']) && $_GET['agama'] == $row['nama_agama']) ? 'selected' : '';
                                    echo "<option value=\"{$row['nama_agama']}\" $selected>{$row['nama_agama']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <select name="departemen_unitkerja" class="form-control">
                                <option value="">-- Departemen/Unit Kerja --</option>
                                <?php
                                $res = mysqli_query($kon, "SELECT nama_departemen FROM tbl_departemen ORDER BY nama_departemen ASC");
                                while($row = mysqli_fetch_assoc($res)) {
                                    $selected = (isset($_GET['departemen_unitkerja']) && $_GET['departemen_unitkerja'] == $row['nama_departemen']) ? 'selected' : '';
                                    echo "<option value=\"{$row['nama_departemen']}\" $selected>{$row['nama_departemen']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <?php $current_status = isset($_GET['status_aktif']) ? $_GET['status_aktif'] : 'aktif'; ?>
                            <label class="control-label" style="font-weight:normal;">Status Peserta Magang</label>
                            <select name="status_aktif" class="form-control">
                                <option value="" <?php echo ($current_status === '' ? 'selected' : ''); ?>>-- Semua --</option>
                                <option value="aktif" <?php echo ($current_status === 'aktif' ? 'selected' : ''); ?>>Peserta Magang Aktif</option>
                                <option value="tidak_aktif" <?php echo ($current_status === 'tidak_aktif' ? 'selected' : ''); ?>>Peserta Magang Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-2">
                        <div class="form-group">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<?php if (isset($_SESSION['level']) && (strtolower($_SESSION['level']) == 'mahasiswa') && isset($_SESSION['id_mahasiswa'])): ?>
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Presensi Terakhir Anda
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            include 'config/database.php';
                            $id_mahasiswa = $_SESSION['id_mahasiswa'];
                            $sql = "SELECT * FROM tbl_absensi WHERE id_mahasiswa='$id_mahasiswa' ORDER BY tanggal DESC, waktu_masuk DESC LIMIT 5";
                            $hasil = mysqli_query($kon, $sql);
                            $no = 0;
                            while ($data = mysqli_fetch_array($hasil)) {
                                $no++;
                                echo '<tr>';
                                echo '<td>'.$no.'</td>';
                                echo '<td>'.date('d-m-Y', strtotime($data['tanggal'])).'</td>';
                                echo '<td>'.($data['waktu_masuk'] ? date('H:i', strtotime($data['waktu_masuk'])) : '-').'</td>';
                                echo '<td>'.($data['waktu_pulang'] ? date('H:i', strtotime($data['waktu_pulang'])) : '-').'</td>';
                                echo '<td>'.$data['keterangan'].'</td>';
                                echo '</tr>';
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">

            <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user menambah admin
                if (isset($_GET['add'])) {
                    if ($_GET['add']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Data Peserta Magang Telah Disimpan</div>";
                    }else if ($_GET['add']=='gagal'){
                        echo"<div class='alert alert-danger'><strong>Gagal!</strong> Data Peserta Magang Gagal Disimpan</div>";
                    }    
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user mengedit admin
                if (isset($_GET['edit'])) {
                    if ($_GET['edit']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Data Peserta Magang Telah Diupdate</div>";
                    }else if ($_GET['edit']=='gagal'){
                        echo"<div class='alert alert-danger'><strong>Gagal!</strong> Data Peserta Magang Gagal Diupdate</div>";
                        if (isset($_GET['reason']) && $_GET['reason']==='akhir_magang') {
                            echo "<div class='alert alert-warning'>Untuk mengaktifkan mahasiswa, tanggal akhir magang harus diperpanjang (minimal hari ini atau setelahnya).</div>";
                        }
                        if (isset($_GET['reason']) && $_GET['reason']==='tanggal_tidak_valid') {
                            echo "<div class='alert alert-warning'>Tanggal mulai magang tidak boleh setelah tanggal akhir magang.</div>";
                        }
                    }    
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user menghapus admin
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Setting Data Peserta Magang Berhasil</div>";
                    }else if ($_GET['pengguna']=='gagal'){
                        echo"<div class='alert alert-danger'><strong>Gagal!</strong> Setting Data Peserta Magang Gagal</div>";
                    }    
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user menghapus admin
                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Data Peserta Magang Telah Dihapus</div>";
                    }else if ($_GET['hapus']=='gagal'){
                        echo"<div class='alert alert-danger'><strong>Gagal!</strong> Data Peserta Magang Gagal Dihapus</div>";
                    }    
                }
            ?>
                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah"><i class="fa fa-plus"></i> Tambah</button>
                </div>
                <div class="table-responsive" id="scrollable-table">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Universitas</th>
                                <th>NIM</th>
                                <th>Tempat Lahir</th>
                                <th>Tanggal Lahir</th>
                                <th>Agama</th>
                                <th>Alamat</th>
                                <th>No Telp</th>
                                <th>No HP Ortu</th>
                                <th>Nama Pembimbing</th>
                                <th>No HP Pembimbing</th>
                                <th>Scan KTP/KK</th>
                                <th>Scan BPJS</th>
                                <th>Departemen/Unit Kerja</th>
                                <th>Mulai Magang</th>
                                <th>Akhir Magang</th>
                                <th>Status</th>
                                <th>Foto</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
            
                        <tbody>

                        <?php
                            include 'config/database.php';
                            include_once 'config/mahasiswa_status.php';
                            // PAGINATION & FILTER
                            $data_per_halaman = 10;
                            $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                            if ($halaman < 1) $halaman = 1;
                            $mulai = ($halaman - 1) * $data_per_halaman;
                            // Akun yang ditolak (rejected) tidak masuk daftar data mahasiswa
                            $base_join = " FROM tbl_mahasiswa m INNER JOIN tbl_user u ON u.kode_pengguna = m.kode_mahasiswa ";
                            $base_where = " NOT (u.status_approval <=> 'rejected') ";
                            $where = [$base_where];
                            if (isset($_GET['cari']) && $_GET['cari'] != "") {
                                $cari = trim($_GET["cari"]);
                                $cari = mysqli_real_escape_string($kon, $cari);
                                $where[] = "(m.nama LIKE '%$cari%' OR m.nim LIKE '%$cari%' OR m.universitas LIKE '%$cari%' OR m.jurusan LIKE '%$cari%')";
                            }
                            if (isset($_GET['universitas']) && $_GET['universitas'] != "") {
                                $universitas = mysqli_real_escape_string($kon, $_GET['universitas']);
                                $where[] = "m.universitas = '$universitas'";
                            }
                            if (isset($_GET['agama']) && $_GET['agama'] != "") {
                                $agama = mysqli_real_escape_string($kon, $_GET['agama']);
                                $where[] = "m.agama = '$agama'";
                            }
                            if (isset($_GET['departemen_unitkerja']) && $_GET['departemen_unitkerja'] != "") {
                                $departemen = mysqli_real_escape_string($kon, $_GET['departemen_unitkerja']);
                                $where[] = "m.departemen_unitkerja = '$departemen'";
                            }
                            // Filter status efektif: aktif = admin aktif + dalam periode magang hari ini
                            $current_status = isset($_GET['status_aktif']) ? $_GET['status_aktif'] : 'aktif';
                            if ($current_status === 'aktif') {
                                $where[] = "m.status_aktif = 'aktif' AND m.mulai_magang <= CURDATE() AND m.akhir_magang >= CURDATE()";
                            } elseif ($current_status === 'tidak_aktif') {
                                $where[] = "(m.status_aktif = 'tidak_aktif' OR m.mulai_magang > CURDATE() OR m.akhir_magang < CURDATE())";
                            }
                            $where_sql = " WHERE " . implode(" AND ", $where);
                            $sql_count = "SELECT COUNT(*) as total " . $base_join . $where_sql;
                            $sql_data = "SELECT m.* " . $base_join . $where_sql;
                            $res_total = mysqli_query($kon, $sql_count);
                            $total_data = 0;
                            if ($row_total = mysqli_fetch_assoc($res_total)) {
                                $total_data = $row_total['total'];
                            }
                            $total_halaman = ceil($total_data / $data_per_halaman);
                            $sql_data .= " ORDER BY m.nama ASC LIMIT $mulai, $data_per_halaman";
                            $hasil = mysqli_query($kon, $sql_data);
                            $no = $mulai;
                            // Pastikan kolom status_aktif ada (jika migration belum dijalankan, default aktif)
                        ?>

                        <?php while ($data = mysqli_fetch_array($hasil)):
                        $no++;
                        ?>

                        <tr>
                            <td><?php echo $no; ?></td>
                            <td><?php echo $data['nama']; ?></td>
                            <td><?php echo $data['universitas']; ?></td>
                            <td><?php echo $data['nim'];?></td>
                            <td><?php echo $data['tempat_lahir']; ?></td>
                            <td><?php echo !empty($data['tanggal_lahir']) ? date('d-m-Y', strtotime($data['tanggal_lahir'])) : '-'; ?></td>
                            <td><?php echo $data['agama']; ?></td>
                            <td><?php echo $data['alamat']; ?></td>
                            <td><?php echo $data['no_telp']; ?></td>
                            <td><?php echo $data['no_hp_ortu']; ?></td>
                            <td><?php echo $data['nama_pembimbing']; ?></td>
                            <td><?php echo $data['no_hp_pembimbing']; ?></td>
                            <td><?php if (!empty($data['scan_ktp_kk'])) { 
                                $ext = strtolower(pathinfo($data['scan_ktp_kk'], PATHINFO_EXTENSION));
                                $src = 'apps/mahasiswa/ktp_mahasiswa/'.$data['scan_ktp_kk'];
                                if ($ext == 'pdf') {
                                    echo '<a href="'.$src.'" target="_blank">Lihat PDF</a>';
                                } else {
                                    echo '<img src="'.$src.'" class="thumbnail-gambar" data-gambar="'.$src.'" style="height:60px;cursor:pointer;border-radius:4px;box-shadow:0 0 4px #aaa;" title="Klik untuk preview">';
                                }
                            } else { echo '-'; } ?></td>
                            <td><?php if (!empty($data['scan_bpjs'])) { 
                                $ext = strtolower(pathinfo($data['scan_bpjs'], PATHINFO_EXTENSION));
                                $src = 'apps/mahasiswa/bpjs_mahasiswa/'.$data['scan_bpjs'];
                                if ($ext == 'pdf') {
                                    echo '<a href="'.$src.'" target="_blank">Lihat PDF</a>';
                                } else {
                                    echo '<img src="'.$src.'" class="thumbnail-gambar" data-gambar="'.$src.'" style="height:60px;cursor:pointer;border-radius:4px;box-shadow:0 0 4px #aaa;" title="Klik untuk preview">';
                                }
                            } else { echo '-'; } ?></td>
                            <td><?php echo $data['departemen_unitkerja']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($data["mulai_magang"])); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($data["akhir_magang"])); ?></td>
                            <td><?php
                                $st = mahasiswa_status_tampilan_admin($data);
                                if ($st === 'aktif') {
                                    echo '<span class="label label-success">Aktif</span>';
                                } elseif ($st === 'tidak_aktif_admin') {
                                    echo '<span class="label label-danger">Tidak aktif (admin)</span>';
                                } else {
                                    echo '<span class="label label-default">Tidak aktif (periode)</span>';
                                }
                            ?></td>
                            <td><?php 
                                $foto = $data["foto"];
                                $foto_path = "apps/mahasiswa/foto/" . $foto;
                                if (!empty($foto) && file_exists($foto_path)) {
                                    echo '<img src="'.$foto_path.'" class="thumbnail-gambar" data-gambar="'.$foto_path.'" style="height:60px;cursor:pointer;border-radius:4px;box-shadow:0 0 4px #aaa;" title="Klik untuk preview">';
                                } else {
                                    echo '<img src="apps/mahasiswa/foto/foto_default.png" class="thumbnail-gambar" data-gambar="apps/mahasiswa/foto/foto_default.png" style="height:60px;cursor:pointer;border-radius:4px;box-shadow:0 0 4px #aaa;" title="Klik untuk preview">';
                                }
                            ?>
                            </td>
                            <td>
                                <button id_mahasiswa="<?php echo $data['id_mahasiswa'];?>" class="tombol_detail btn btn-success btn-circle" title="Lihat Detail Peserta Magang"><i class="fa fa-mouse-pointer"></i></button>
                                <button kode_mahasiswa="<?php echo $data['kode_mahasiswa'];?>" class="tombol_setting btn btn-primary btn-circle" title="Setting Peserta Magang"><i class="fa fa-user"></i></button>
                                <button id_mahasiswa="<?php echo $data['id_mahasiswa'];?>" class="tombol_edit btn btn-warning btn-circle" title="Edit Data Peserta Magang"><i class="fa fa-edit"></i></button>
                                <a href="apps/mahasiswa/hapus.php?id_mahasiswa=<?php echo $data['id_mahasiswa']; ?>&kode_mahasiswa=<?php echo $data['kode_mahasiswa']; ?>" class="btn-hapus-mahasiswa btn btn-danger btn-circle" title="Hapus Data Peserta Magang"><i class="fa fa-trash"></i></a>
                                <a href="apps/mahasiswa/export_pdf.php?id_mahasiswa=<?php echo $data['id_mahasiswa']; ?>" class="btn btn-danger btn-circle" title="Download PDF Data Peserta Magang" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
                            </td>
                        </tr>
                        <!-- bagian akhir (penutup) while -->
                        <?php endwhile; if ($no == $mulai) { echo '<tr><td colspan="21" class="text-center">Tidak ada data peserta magang.</td></tr>'; } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<?php
// Pagination navigation (windowed)
$get_params = $_GET;
unset($get_params['halaman']);
$query_string = http_build_query($get_params);
$page_url = '?' . ($query_string ? $query_string . '&' : '') . 'halaman=';
$window = 3;
$start = max(1, $halaman - floor($window / 2));
$end = min($total_halaman, $start + $window - 1);
if ($end - $start + 1 < $window) {
    $start = max(1, $end - $window + 1);
}
if ($total_halaman > 1): ?>
<nav>
  <ul class="pagination">
    <?php if($halaman > 1): ?>
      <li class="page-item"><a class="page-link" href="<?php echo $page_url.($halaman-1); ?>">&laquo; Sebelumnya</a></li>
    <?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
      <li class="page-item <?php if($i == $halaman) echo 'active'; ?>">
        <a class="page-link" href="<?php echo $page_url.$i; ?>"><?php echo $i; ?></a>
      </li>
    <?php endfor; ?>
    <?php if($halaman < $total_halaman): ?>
      <li class="page-item"><a class="page-link" href="<?php echo $page_url.($halaman+1); ?>">Selanjutnya &raquo;</a></li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>

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
            </div>  
        </div>
  
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>

        </div>
    </div>
</div>

<!-- Modal Preview Gambar -->
<div class="modal fade" id="modalPreviewGambar" tabindex="-1" role="dialog" aria-labelledby="modalPreviewGambarLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background: transparent; box-shadow: none; border: none;">
      <div class="modal-body" style="position: relative; padding:0;">
        <div style="position:relative;">
          <img id="preview-blur" src="" style="width:100%; filter: blur(12px); position:absolute; top:0; left:0; z-index:1;">
          <img id="preview-gambar" src="" style="width:100%; max-width:400px; display:block; margin:auto; position:relative; z-index:2; border-radius:8px; box-shadow:0 0 16px #0008;">
        </div>
        <div class="text-center mt-3" style="z-index:3; position:relative;">
          <a id="download-gambar" href="#" download class="btn btn-primary"><i class="fa fa-download"></i> Download Gambar</a>
          <button id="download-gambar-pdf" class="btn btn-danger ml-2"><i class="fa fa-file-pdf-o"></i> Download PDF</button>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Data akan di load menggunakan AJAX -->
<script>
    // Tambah admin
    $('#tombol_tambah').on('click',function(){
        $.ajax({
            url: 'apps/mahasiswa/tambah.php',
            method: 'post',
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Peserta Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Detail Mahasiswa
    $('.tombol_detail').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/detail.php',
            method: 'post',
            data: {id_mahasiswa:id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Detail Peserta Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Setting Mahasiswa
    $('.tombol_setting').on('click',function(){
        var kode_mahasiswa = $(this).attr("kode_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/pengguna.php',
            method: 'post',
            data: {kode_mahasiswa:kode_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Setting Peserta Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Edit Mahasiswa
    $('.tombol_edit').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/mahasiswa/edit.php',
            method: 'post',
            data: {id_mahasiswa:id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Edit Peserta Magang';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
   // Hapus admin
   $('.btn-hapus-mahasiswa').on('click',function(){
        konfirmasi=confirm("Konfirmasi Sebelum Menghapus Peserta Magang?")
        if (konfirmasi){
            return true;
        }else {
            return false;
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
$(document).on('click', '.lihat-gambar, .thumbnail-gambar', function(){
    var src = $(this).data('gambar');
    $('#preview-gambar').attr('src', src);
    $('#preview-blur').attr('src', src);
    $('#download-gambar').attr('href', src);
    $('#download-gambar').attr('download', src.split('/').pop());
    $('#modalPreviewGambar').modal('show');
});
$('#modalPreviewGambar').on('hidden.bs.modal', function(){
    $('#preview-gambar').attr('src', '');
    $('#preview-blur').attr('src', '');
    $('#download-gambar').attr('href', '#');
});
$('#download-gambar-pdf').on('click', function(e){
    e.preventDefault();
    var imgSrc = $('#preview-gambar').attr('src');
    if (!imgSrc) return;
    var pdf = new window.jspdf.jsPDF();
    var img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = function() {
        var width = this.width;
        var height = this.height;
        var pdfWidth = 180;
        var pdfHeight = height * pdfWidth / width;
        pdf.addImage(this, 'JPEG', 15, 20, pdfWidth, pdfHeight);
        pdf.save('gambar_scan.pdf');
    };
    img.src = imgSrc;
});
</script>

<script>
$(function () {
  $('[data-toggle="tooltip"]').tooltip();
});
</script>