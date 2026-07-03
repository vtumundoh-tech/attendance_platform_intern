<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Riwayat Presensi</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading bg-info text-white">
            Riwayat Presensi
            <span class="pull-right clickable panel-toggle panel-button-tab-left"><em class="fa fa-toggle-up"></em></span></div>
            <div class="panel-body">
            <div class="row">
                <form action="#" method="GET">
                    <input type="hidden" name="page" value="riwayat"/>
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

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-body">
                
                <div class="form-group">
                    <button id_mahasiswa='<?php echo $_SESSION['id_mahasiswa']; ?>' type="button" class="cetak btn btn-primary" id="cetak"><i class="fa fa-print"></i> Cetak</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-center" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="text-center">No</th>
                                <th class="text-center">Hari</th>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Waktu Masuk</th>
                                <th class="text-center">Foto Masuk</th>
                                <th class="text-center">Waktu Pulang</th>
                                <th class="text-center">Foto Pulang</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Keterangan</th>
                            </tr>
                        </thead>
        
                        <tbody>
                        <?php
                            // include database
                            include 'config/database.php';
                            include 'config/function.php';
                            $id_mahasiswa=$_SESSION["id_mahasiswa"];

                            // --- PAGINATION SETUP ---
                            $limit = 10; // jumlah data per halaman
                            $page = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
                            if ($page < 1) $page = 1;
                            $start = ($page - 1) * $limit;

                            // Filter tanggal
                            $tanggal_filter = '';
                            if (isset($_GET['tanggal_awal']) && isset($_GET['tanggal_akhir']) && $_GET['tanggal_awal'] && $_GET['tanggal_akhir']) {
                                $tanggal_awal=$_GET["tanggal_awal"];
                                $tanggal_akhir=$_GET["tanggal_akhir"];
                                $tanggal_filter = " AND tbl_absensi.tanggal >= '$tanggal_awal' AND tbl_absensi.tanggal <= '$tanggal_akhir' ";
                            }

                            // Hitung total data
                            $count_sql = "SELECT COUNT(*) as total FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' $tanggal_filter";
                            $count_result = mysqli_query($kon, $count_sql);
                            $count_row = mysqli_fetch_assoc($count_result);
                            $total_data = $count_row['total'];
                            $total_pages = max(1, ceil($total_data / $limit));

                            // Query utama dengan LIMIT
                            $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, tbl_alasan.id_alasan, 
                                DAYNAME(tbl_absensi.tanggal) AS hari,
                                tbl_absensi.tanggal,
                                tbl_absensi.waktu_masuk, tbl_absensi.foto_masuk, tbl_absensi.waktu_pulang, tbl_absensi.foto_pulang,
                                IFNULL(tbl_alasan.alasan, ' - ') AS alasan,
                                  (CASE
                                    WHEN tbl_absensi.status = 1 THEN 'Hadir'
                                    WHEN tbl_absensi.status = 2 THEN 'Izin'
                                    WHEN tbl_absensi.status = 3 THEN 'Tidak Hadir'
                                    ELSE 'Belum Presensi'
                                END) AS status
                                FROM tbl_absensi
                                LEFT JOIN tbl_alasan ON tbl_absensi.tanggal = tbl_alasan.tanggal AND tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa
                                WHERE tbl_absensi.id_mahasiswa = '$id_mahasiswa' $tanggal_filter
                                ORDER BY tbl_absensi.tanggal DESC
                                LIMIT $start, $limit;";
                            $hasil = mysqli_query($kon, $sql);
                            $no = $start;
                            //Menampilkan data dengan perulangan while
                            while ($data = mysqli_fetch_array($hasil)):
                            $no++;
                        ?>
                        <tr>
                            <td class="text-center"><?php echo $no; ?></td>
                            <td class="text-center">
                                <?php
                                    $hari = $data['hari'];
                                    echo MendapatkanHari($hari);
                                ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                $tgl = date("d", strtotime($data['tanggal']));
                                $bulan = date("m", strtotime($data['tanggal']));
                                $tahun = date("Y", strtotime($data['tanggal']));
                                echo $tgl.' '.MendapatkanBulan($bulan).' '.$tahun
                                ?>
                            </td>
                            <td class="text-center"><?php echo $data['waktu_masuk'] ? date('H:i', strtotime($data['waktu_masuk'])) : '-'; ?></td>
                            <td class="text-center">
                                <?php if ($data['foto_masuk']) {
                                    echo '<img src="/valendy_presensi/apps/data_absensi/foto_absen_masuk/' . $data['foto_masuk'] . '" width="80"/>';
                                } else {
                                    echo '-';
                                } ?>
                            </td>
                            <td class="text-center"><?php echo $data['waktu_pulang'] ? date('H:i', strtotime($data['waktu_pulang'])) : '-'; ?></td>
                            <td class="text-center">
                                <?php if ($data['foto_pulang']) {
                                    echo '<img src="/valendy_presensi/apps/data_absensi/foto_absen_pulang/' . $data['foto_pulang'] . '" width="80"/>';
                                } else {
                                    echo '-';
                                } ?>
                            </td>
                            <td class="text-center"><?php echo $data['status']; ?></td>
                            <td class="text-center"><?php echo $data['alasan']; ?></td>                         
                        </tr>
                        <!-- bagian akhir (penutup) while -->
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="form-group">
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
      // Windowed pagination: hanya tampil 3 nomor halaman sekaligus
      $window = 3;
      $start_page = max(1, $page - 1);
      $end_page = min($total_pages, $start_page + $window - 1);
      if ($end_page - $start_page < $window - 1) {
        $start_page = max(1, $end_page - $window + 1);
      }
      // Tombol prev
      if ($page > 1) {
        echo '<li class="page-item"><a class="page-link" href="?page=riwayat&page_num='.($page-1).(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">&laquo;</a></li>';
      }
      // Nomor halaman
      for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? 'active' : '';
        echo '<li class="page-item '.$active.'"><a class="page-link" href="?page=riwayat&page_num='.$i.(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">'.$i.'</a></li>';
      }
      // Tombol next
      if ($page < $total_pages) {
        echo '<li class="page-item"><a class="page-link" href="?page=riwayat&page_num='.($page+1).(isset($_GET['tanggal_awal']) ? '&tanggal_awal=' . $_GET['tanggal_awal'] : '').(isset($_GET['tanggal_akhir']) ? '&tanggal_akhir=' . $_GET['tanggal_akhir'] : '').'">&raquo;</a></li>';
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
                 <!-- Data akan di load menggunakan AJAX -->                   
            </div>  
        </div>
  
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
        </div>

        </div>
    </div>
</div>

<script>
    // Setting absensi
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