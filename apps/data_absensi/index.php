<?php 
    if ($_SESSION["level"]!='Admin' and $_SESSION["level"]!='admin'){
        echo"<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
        exit;
    }
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Data Presensi</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
            Data Presensi
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span></div>
            <div class="panel-body">
                <div class="row">
                <form action="#" method="GET" id="form-filter">
                    <input type="hidden" name="page" value="data_absensi"/>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Nama Peserta Magang :</label>
                            <input type="text" name="nama" id="nama" class="form-control"  value="" placeholder="Carii Peserta Magang" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Tanggal Awal :</label>
                            <input type="date" name="tanggal_awal" id="tanggal_awal" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label>Tanggal Akhir :</label>
                            <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group" style="display: flex; gap: 5px; align-items: flex-end;">
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">

            
            
                <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user update pengaturan aplikasi                
                if (isset($_GET['mulai'])) {
                    if ($_GET['mulai']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Data Presensi Berhasil Ditambah</div>";
                    }else if ($_GET['mulai']=='gagal'){
                        echo"<div class='alert alert-warning'><strong>Maaf!</strong> Data Presensi Gagal Disimpan</div>";
                    }
                }
                // Tampilkan alasan kegagalan jika diberikan
                if (isset($_GET['reason']) && $_GET['reason'] == 'missing_id_mahasiswa') {
                    echo "<div class='alert alert-danger'><strong>Error:</strong> Peserta Magang tidak dipilih. Silakan pilih peserta magang sebelum menyimpan.</div>";
                }
            ?>

                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tambah_absensi"><i class="tambah_absensi fa fa-plus"></i> Presensi</button>
                </div>
                <div class="form-group" style="margin-bottom: 20px;">
                    <div class="btn-group">
                        <button type="button" id="sortDropdownBtn" class="btn btn-primary">
                            <i class="fa fa-list"></i> Tampilkan Semua & Sortir
                        </button>
                    </div>
                    <div id="sortDropdownMenu">
                        <div class="dropdown-item" onclick="showAllSort('nama','asc')">Nama Ascending</div>
                        <div class="dropdown-item" onclick="showAllSort('nama','desc')">Nama Descending</div>
                        <div class="dropdown-item" onclick="showAllSort('tanggal','asc')">Tanggal Ascending</div>
                        <div class="dropdown-item" onclick="showAllSort('tanggal','desc')">Tanggal Descending</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Universitas</th>
                                <th>Status</th>
                                <th>Status GPS</th>
                                <th>Waktu</th>
                                <th>Hari</th>
                                <th>Tanggal</th>
                                <th>Foto Masuk</th>
                                <th>Foto Pulang</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
        
                        <tbody>
                        <?php
                            include 'config/database.php';
                            include 'config/function.php';
                            // PAGINATION SETUP
                            $data_per_halaman = 10;
                            $halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
                            if ($halaman < 1) $halaman = 1;
                            $mulai = ($halaman - 1) * $data_per_halaman;

                            $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'tanggal';
                            $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'desc';

                            // Hitung total data sesuai filter
                            if (isset($_GET['show_all']) && $_GET['show_all'] == '1') {
                                $sql_total = AmbilSemuaAbsensi($sort_by, $sort_order);
                                $res_total = mysqli_query($kon, $sql_total);
                                $total_data = mysqli_num_rows($res_total);
                            } else if (
                                isset($_GET['nama']) && $_GET['nama'] !== "" &&
                                isset($_GET['tanggal_awal']) && $_GET['tanggal_awal'] !== "" &&
                                isset($_GET['tanggal_akhir']) && $_GET['tanggal_akhir'] !== ""
                            ) {
                                $nama = trim($_GET["nama"]);
                                $tanggal_awal = $_GET["tanggal_awal"];
                                $tanggal_akhir = $_GET["tanggal_akhir"];
                                $sql_total = PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir, $sort_by, $sort_order);
                                $res_total = mysqli_query($kon, $sql_total);
                                $total_data = mysqli_num_rows($res_total);
                            } else {
                                $sql_total = AbsensiOtomatis('', null, null);
                                $res_total = mysqli_query($kon, $sql_total);
                                $total_data = mysqli_num_rows($res_total);
                            }
                            $total_halaman = ceil($total_data / $data_per_halaman);

                            // Query data dengan LIMIT
                            if (isset($_GET['show_all']) && $_GET['show_all'] == '1') {
                                $sql = AmbilSemuaAbsensi($sort_by, $sort_order, $data_per_halaman, $mulai);
                            } else if (
                                isset($_GET['nama']) && $_GET['nama'] !== "" &&
                                isset($_GET['tanggal_awal']) && $_GET['tanggal_awal'] !== "" &&
                                isset($_GET['tanggal_akhir']) && $_GET['tanggal_akhir'] !== ""
                            ) {
                                $nama = trim($_GET["nama"]);
                                $tanggal_awal = $_GET["tanggal_awal"];
                                $tanggal_akhir = $_GET["tanggal_akhir"];
                                $sql = PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir, $sort_by, $sort_order, $data_per_halaman, $mulai);
                            } else {
                                $sql = AbsensiOtomatis('', $data_per_halaman, $mulai);
                            }
                            $hasil = mysqli_query($kon, $sql);
                            $no = $mulai;
                            //Menampilkan data dengan perulangan while
                            while ($data = mysqli_fetch_array($hasil)):
                            $no++;
                        ?>
                        <?php
                            $ijin_cepat_status = null;
                            $status_ijin_query = mysqli_query($kon, "SELECT status FROM tbl_ijin_pulang_cepat WHERE id_mahasiswa = '{$data['id_mahasiswa']}' AND tanggal_ijin = CURDATE() AND status = 'disetujui' AND waktu_ijin_dari_admin >= DATE_SUB(NOW(), INTERVAL 10 MINUTE) LIMIT 1");
                            if ($status_ijin = mysqli_fetch_assoc($status_ijin_query)) {
                                $ijin_cepat_status = $status_ijin['status'];
                            }
                        ?>
                        <tr>
                            <td><?php echo $no; ?></td>
                            <td><?php echo $data['nama']; ?></td>
                            <td><?php echo $data['universitas']; ?></td>
                            <td><?php echo $data['status']; ?></td>
                            <td>
                                <?php 
                                    $status_gps = $data['status_gps'] ?? 'valid';
                                    if ($status_gps == 'valid') {
                                        echo '<span class="badge badge-success" style="background:#28a745;">Valid</span>';
                                    } else if ($status_gps == 'suspicious') {
                                        echo '<span class="badge badge-warning" style="background:#ffc107; color:#000;">Mencurigakan</span>';
                                    } else if ($status_gps == 'fake') {
                                        echo '<span class="badge badge-danger" style="background:#dc3545;">Fake GPS</span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $waktu_masuk = $data['waktu_masuk'] ?? null;
                                    $waktu_pulang = $data['waktu_pulang'] ?? null;
                                    
                                    if ($waktu_masuk && $waktu_masuk != '00:00:00') {
                                        echo date('H:i', strtotime($waktu_masuk));
                                    } else {
                                        echo 'Belum Presensi';
                                    }
                                    
                                    if ($waktu_masuk && $waktu_masuk != '00:00:00') {
                                        echo ' - ';
                                        if ($waktu_pulang && $waktu_pulang != '00:00:00') {
                                            echo date('H:i', strtotime($waktu_pulang));
                                        } else {
                                            echo 'Belum Pulang';
                                        }
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $hari = $data["hari"];
                                    echo MendapatkanHari($hari);
                                ?>
                            </td>
                            <td>
                                <?php
                                    $tgl = date("d", strtotime($data['tanggal']));
                                    $bulan = date("m", strtotime($data['tanggal']));
                                    $tahun = date("Y", strtotime($data['tanggal']));
                                    echo $tgl.' '.MendapatkanBulan($bulan).' '.$tahun
                                ?>
                            </td>
                            <td>
                                <?php
                                    $foto_masuk = $data['foto_masuk'] ?? '';
                                    $path_masuk = 'apps/data_absensi/foto_absen_masuk/' . $foto_masuk;
                                    if (!empty($foto_masuk) && file_exists($path_masuk)) {
                                        echo '<img src="'.$path_masuk.'" width="60" class="foto-absen" data-src="'.$path_masuk.'" style="cursor:pointer">';
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    $foto_pulang = $data['foto_pulang'] ?? '';
                                    $path_pulang = 'apps/data_absensi/foto_absen_pulang/' . $foto_pulang;
                                    if (!empty($foto_pulang) && file_exists($path_pulang)) {
                                        echo '<img src="'.$path_pulang.'" width="60" class="foto-absen" data-src="'.$path_pulang.'" style="cursor:pointer">';
                                    } else {
                                        echo '-';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php if (($data['status_gps'] ?? '') == 'suspicious'): ?>
                                    <button id_absensi="<?php echo $data['id_absensi']; ?>" class="validasi_gps btn btn-warning btn-circle" title="Validasi GPS Mencurigakan"><i class="fa fa-check"></i></button>
                                <?php endif; ?>
                                <?php if ($data['tanggal'] == date('Y-m-d') && !empty($data['waktu_masuk']) && $data['waktu_masuk'] != '00:00:00' && (empty($data['waktu_pulang']) || $data['waktu_pulang'] == '00:00:00')): ?>
                                     <?php if ($ijin_cepat_status === 'disetujui'): ?>
                                         <span class="badge badge-success" style="background:#28a745; margin-right:4px;">Izin Pulang Cepat</span>
                                     <?php else: ?>
                                         <button id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" class="grant_pulang_cepat btn btn-warning btn-circle" title="Berikan izin pulang cepat"><i class="fa fa-bolt"></i></button>
                                     <?php endif; ?>
                                 <?php endif; ?>
                                <button id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" id_absensi="<?php echo $data['id_absensi']; ?>" class="absensi btn btn-success btn-circle" ><i class="fa fa-clock-o"></i> Presensi</button>
                                <button id_mahasiswa="<?php echo $data['id_mahasiswa']; ?>" class="cetak btn btn-primary btn-circle" ><i class="fa fa-print"></i> Cetak</button>
                            </td>
                        </tr>
                        <!-- bagian akhir (penutup) while -->
                        <?php endwhile; if ($no == $mulai) { echo '<tr><td colspan="20" class="text-center">Tidak ada data presensi.</td></tr>'; } ?>
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

<!-- Modal Preview Foto Absen -->
<div id="modalFotoAbsen" class="modal-foto-absen" style="display:none;">
  <div class="modal-foto-content">
    <img id="imgPreviewAbsen" src="" style="max-width:90vw; max-height:80vh; border-radius:12px;">
  </div>
</div>
<style>
.modal-foto-absen {
  position: fixed; z-index: 9999; left: 0; top: 0; width: 100vw; height: 100vh;
  background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
}
.modal-foto-content {
  background: transparent; border-radius: 12px; padding: 0;
  box-shadow: 0 4px 32px rgba(0,0,0,0.3);
}
/* Dropdown statis, mendorong tabel ke bawah */
#sortDropdownMenu {
    display: block;
    width: 260px;
    background: #fff;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    border-radius: 10px;
    padding: 8px 0;
    margin-top: 8px;
    font-size: 16px;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    pointer-events: none;
    transition: max-height 0.45s cubic-bezier(.4,2,.6,1), opacity 0.25s cubic-bezier(.4,2,.6,1);
}
#sortDropdownMenu.show {
    max-height: 500px;
    opacity: 1;
    pointer-events: auto;
    transition: max-height 0.45s cubic-bezier(.4,2,.6,1), opacity 0.32s cubic-bezier(.4,2,.6,1) 0.08s;
}
#sortDropdownMenu .dropdown-item {
    padding: 12px 24px;
    color: #333;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: background 0.2s, color 0.2s;
    margin: 2px 8px;
    white-space: nowrap;
}
#sortDropdownMenu .dropdown-item:hover, #sortDropdownMenu .dropdown-item:focus {
    background: #36d1c4;
    color: #fff;
}
#sortDropdownBtn {
    background: linear-gradient(90deg, #36d1c4 0%, #5b86e5 100%);
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 6px;
    box-shadow: 0 2px 8px rgba(91,134,229,0.08);
    padding: 8px 22px;
}
#sortDropdownBtn:focus, #sortDropdownBtn:active {
    outline: none;
    box-shadow: 0 0 0 2px #5b86e5;
}
</style>
<script>
    //Menambahkan absensi oleh admin
    $('#tambah_absensi').on('click',function(){
        $.ajax({
            url: 'apps/data_absensi/tambah.php',
            method: 'post',
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Absensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    //Mengubah absensi oleh admin
    $('.absensi').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        var id_absensi = $(this).attr("id_absensi");
        $.ajax({
            url: 'apps/data_absensi/absensi.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa, 
                    id_absensi: id_absensi},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Edit Presensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    //Cetak Absensi
    $('.cetak').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_absensi/cetak.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Cetak Presensi';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.foto-absen').forEach(function(img) {
    img.onclick = function() {
      document.getElementById('imgPreviewAbsen').src = this.getAttribute('data-src');
      document.getElementById('modalFotoAbsen').style.display = 'flex';
    }
  });
  document.getElementById('modalFotoAbsen').onclick = function(e) {
    if (e.target === this) {
      this.style.display = 'none';
      document.getElementById('imgPreviewAbsen').src = '';
    }
  };
});
</script>

<script>
function showAllSort(by, order) {
    var url = new URL(window.location.href);
    url.searchParams.set('show_all', '1');
    url.searchParams.set('sort_by', by);
    url.searchParams.set('sort_order', order);
    window.location.href = url.toString();
}
// Dropdown logic statis
const btn = document.getElementById('sortDropdownBtn');
const menu = document.getElementById('sortDropdownMenu');
btn.addEventListener('click', function(e) {
    e.stopPropagation();
    menu.classList.toggle('show');
});
document.addEventListener('click', function(e) {
    if (!menu.contains(e.target) && e.target !== btn) {
        menu.classList.remove('show');
    }
});
window.addEventListener('resize', function() { menu.classList.remove('show'); });
window.addEventListener('scroll', function() { menu.classList.remove('show'); });
</script>

<script>
    //Validasi GPS
    $('.validasi_gps').on('click',function(){
        var id_absensi = $(this).attr("id_absensi");
        konfirmasi=confirm("Validasi status GPS ini menjadi Valid?")
        if (konfirmasi){
            $.ajax({
                url: 'apps/data_absensi/validasi_gps.php',
                method: 'POST',
                data: {id_absensi: id_absensi},
                success:function(data){
                    alert("Status GPS berhasil divalidasi menjadi Valid.");
                    window.location.reload();
                }
            });
        }
    });
</script>

<script>
    // Berikan izin pulang cepat oleh admin
    $('.grant_pulang_cepat').on('click', function() {
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        if (!confirm("Berikan izin pulang cepat untuk mahasiswa ini?")) {
            return;
        }
        $.ajax({
            url: 'apps/admin/approve_pulang_cepat.php',
            method: 'POST',
            data: {
                id_mahasiswa: id_mahasiswa,
                action: 'approve'
            },
            success: function(response) {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                if (res && res.success) {
                    alert(res.message);
                    window.location.reload();
                } else {
                    alert(res && res.message ? res.message : 'Gagal memberikan izin pulang cepat.');
                }
            },
            error: function() {
                alert('Gagal memproses permintaan izin pulang cepat.');
            }
        });
    });
</script>