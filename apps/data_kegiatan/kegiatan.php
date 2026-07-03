<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Kegiatan Harian</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading bg-info text-white">
            Kegiatan Harian
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span></div>
            <div class="panel-body">
            <div id="div_periode" class='alert alert-warning' style="display:none;"><strong>Tidak dapat menambah kegiatan harian.</strong> Periode magang belum berlangsung, sudah berakhir, atau akun dinonaktifkan admin. Anda tetap dapat melihat dan mencetak data.</div>
                <div class="row">
                <form action="#" method="GET">
                    <input type="hidden" name="page" value="kegiatan"/>
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
                        <div class="form-group">
                            </br>
                            <button type="submit" class="btn btn-info"><i class="fa fa-search"></i> Cari</button>
                        </div>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<?php
    include 'config/database.php';
    include_once 'config/mahasiswa_status.php';
    $id_mahasiswa=$_SESSION["id_mahasiswa"];
    $sql="select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa limit 1";
    $hasil=mysqli_query($kon,$sql);
    $mhs_profil = mysqli_fetch_array($hasil);
    $boleh_tambah_kegiatan = $mhs_profil && mahasiswa_boleh_fitur_magang_penuh($mhs_profil);
    $mulai_magang=$mhs_profil ? $mhs_profil['mulai_magang'] : '';
    $akhir_magang=$mhs_profil ? $mhs_profil['akhir_magang'] : '';

    setlocale(LC_TIME, 'id_ID');
    $tanggal_sekarang = new DateTime();
    $tanggal_masuk = date("d F Y", strtotime($mulai_magang));
    $tanggal_keluar = date("d F Y", strtotime($akhir_magang));

    // --- PAGINATION SETUP ---
    $limit = 10; // jumlah tanggal per halaman
    $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
    if ($page < 1) $page = 1;
    $start = ($page - 1) * $limit;

    // Query kegiatan
    include 'config/function.php';
    $id_mahasiswa= $_SESSION["id_mahasiswa"];
    $tanggal_filter = '';
    if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir']) && $_GET['tanggal_awal'] && $_GET['tanggal_akhir']) {
        $tanggal_awal=$_GET["tanggal_awal"];
        $tanggal_akhir=$_GET["tanggal_akhir"];
        $tanggal_filter = " AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ";
    }
    $sql = "SELECT *, DAYNAME(tanggal) AS hari FROM tbl_kegiatan WHERE id_mahasiswa = '$id_mahasiswa' $tanggal_filter ORDER BY tanggal ASC, waktu_awal ASC";
    $hasil=mysqli_query($kon,$sql);
    $kegiatan_per_tanggal = [];
    while ($data = mysqli_fetch_assoc($hasil)) {
        $tanggal_key = $data['tanggal'];
        $kegiatan_per_tanggal[$tanggal_key][] = $data;
    }
    $all_tanggal = array_keys($kegiatan_per_tanggal);
    $total_tanggal = count($all_tanggal);
    $total_pages = max(1, ceil($total_tanggal / $limit));
    $tanggal_page = array_slice($all_tanggal, $start, $limit);
    $no = $start;
?>

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
            
            <?php
            
                if (isset($_GET['tambah'])) {
                    if ($_GET['tambah']=='berhasil'){
                        echo"<div class='alert alert-success'><strong>Berhasil!</strong> Menambahkan Kegiatan Harian</div>";
                    }else if ($_GET['mulai']=='tambah'){
                        echo"<div class='alert alert-warning'><strong>Sudah!</strong> Menambahkan Kegiatan Harian</div>";
                    }
                }
            ?>

                <div class="form-group">
                    <button id_mahasiswa="<?php echo $_SESSION['id_mahasiswa']; ?>" type="button" class="btn btn-success" id="tombol_kegiatan" <?php echo $boleh_tambah_kegiatan ? '' : 'style="display:none;"'; ?>><i class="fa fa-plus"></i>  Tambah</button>
                    <button id_mahasiswa="<?php echo $_SESSION['id_mahasiswa']; ?>" class="cetak_kegiatan btn btn-primary btn-circle" id="cetak_kegiatan"><i class="fa fa-print"></i> Cetak</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Waktu Awal</th>
                                <th class="text-center">Waktu Akhir</th>
                                <th class="text-center">Kegiatan</th>
                            </tr>
                        </thead>
        
                        <tbody>
                        <?php
                            foreach ($tanggal_page as $tanggal) {
                                $list = $kegiatan_per_tanggal[$tanggal];
                                usort($list, function($a, $b) {
                                    return strtotime($a['waktu_awal']) - strtotime($b['waktu_awal']);
                                });
                                $no++;
                                $hari = MendapatkanHari($list[0]['hari']);
                                $tgl = date('d', strtotime($tanggal));
                                $bulan = date('m', strtotime($tanggal));
                                $tahun = date('Y', strtotime($tanggal));
                                $kegiatan_arr = [];
                                $waktu_awal_arr = [];
                                $waktu_akhir_arr = [];
                                foreach ($list as $row) {
                                    $lines = preg_split('/\r\n|\r|\n/', $row['kegiatan']);
                                    foreach ($lines as $line) {
                                        $line = trim($line);
                                        if ($line !== '') {
                                            $line = ltrim($line, '- ');
                                            $kegiatan_arr[] = '- ' . $line;
                                            $waktu_awal_arr[] = date('H:i', strtotime($row['waktu_awal']));
                                            $waktu_akhir_arr[] = date('H:i', strtotime($row['waktu_akhir']));
                                        }
                                    }
                                }
                                $waktu_awal_str = implode('<br>', $waktu_awal_arr);
                                $waktu_akhir_str = implode('<br>', $waktu_akhir_arr);
                                $kegiatan_str = implode('<br>', $kegiatan_arr);
                                echo '<tr>';
                                echo '<td class="text-center">'.$no.'</td>';
                                echo '<td class="text-center">'.$hari.'</td>';
                                echo '<td class="text-center">'.$tgl.' '.MendapatkanBulan($bulan).' '.$tahun.'</td>';
                                echo '<td class="text-center">'.$waktu_awal_str.'</td>';
                                echo '<td class="text-center">'.$waktu_akhir_str.'</td>';
                                echo '<td>'.$kegiatan_str.'</td>';
                                echo '</tr>';
                            }
                            if ($no == $start) { echo '<tr><td colspan="20" class="text-center">Tidak ada data kegiatan.</td></tr>'; }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<!-- PAGINATION NAVIGATION -->
<div class="mt-3" style="text-align:left;">
  <nav>
    <ul class="pagination" style="margin-bottom:0;">
      <?php
      $window = 3;
      $start_page = max(1, $page - 1);
      $end_page = min($total_pages, $start_page + $window - 1);
      if ($end_page - $start_page < $window - 1) {
        $start_page = max(1, $end_page - $window + 1);
      }
      // Tombol prev
      if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?page=kegiatan&page_num='.($page-1).(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">&laquo;</a></li>';
      }
      // Nomor halaman
      for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="?page=kegiatan&page_num='.$i.(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">'.$i.'</a></li>';
      }
      // Tombol next
      if ($page < $total_pages) {
        echo '<li class="page-item"><a class="page-link" href="?page=kegiatan&page_num='.($page+1).(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">&raquo;</a></li>';
      }
      ?>
    </ul>
    <div style="font-size:14px; color:#555; margin-top:2px;">Halaman <?= $page ?> dari <?= $total_pages ?></div>
  </nav>
</div>

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

<script>
    $('#tombol_kegiatan').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/pengguna/mulai_kegiatan.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Tambah Kegiatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    $('#cetak_kegiatan').on('click',function(){
        var id_mahasiswa = $(this).attr("id_mahasiswa");
        $.ajax({
            url: 'apps/data_kegiatan/cetak.php',
            method: 'POST',
            data: {id_mahasiswa: id_mahasiswa},
            success:function(data){
                $('#tampil_data').html(data);  
                document.getElementById("judul").innerHTML='Cetak Kegiatan';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    $(document).ready(function() {
        var bolehTambah = <?php echo $boleh_tambah_kegiatan ? 'true' : 'false'; ?>;
        if (!bolehTambah) {
            $("#tombol_kegiatan").hide();
            $("#div_periode").show();
        }
        var hari = new Date().getDay();
        if (bolehTambah && (hari == 0 || hari == 6)) {
            $('#tombol_kegiatan').attr('disabled', true);
        }
    });
</script>