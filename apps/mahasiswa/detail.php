<?php 
    include '../../config/database.php';
    include '../../config/function.php';
    $id_mahasiswa=$_POST["id_mahasiswa"];
    $sql="SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa=$id_mahasiswa LIMIT 1";
    $hasil=mysqli_query($kon,$sql);
    $data = mysqli_fetch_array($hasil); 
?>

<style>
  #main-content.blur {
    filter: blur(4px) grayscale(0.2);
    transition: filter 0.2s;
  }
  @media (max-width: 600px) {
    .modal-dialog {
      max-width: 98vw !important;
      margin: 0.5rem auto;
    }
    .modal-content {
      border-radius: 10px;
    }
    .table td, .table th {
      font-size: 13px;
      padding: 6px 4px;
    }
  }
</style>

<!-- Bungkus seluruh konten utama mulai di sini -->
<div id="main-content">
<div class="table-responsive">
    <table class="table">
        <tbody>
            <tr>
                <td>Nama Lengkap</td>
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
                <td width="75%">: <?php echo $data['jurusan'];?></td>
            </tr>
            <tr>
                <td>Mulai Magang</td>
                <td width="75%">: <?php $tgl = date("d", strtotime($data['mulai_magang']));
                                    $bulan = date("m", strtotime($data['mulai_magang']));
                                    $tahun = date("Y", strtotime($data['mulai_magang']));
                                    echo $tgl.' '.MendapatkanBulan($bulan).' '.$tahun ?></td>
            </tr>
            <tr>
                <td>Akhir Magang</td>
                <td width="75%">: <?php $tgl = date("d", strtotime($data['akhir_magang']));
                                    $bulan = date("m", strtotime($data['akhir_magang']));
                                    $tahun = date("Y", strtotime($data['akhir_magang']));
                                    echo $tgl.' '.MendapatkanBulan($bulan).' '.$tahun ?></td>
            </tr>
            <tr>
                <td>No Telp</td>
                <td width="75%">: <?php echo $data['no_telp'];?></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td width="75%">: <?php echo !empty($data['alamat']) ? htmlspecialchars($data['alamat']) : '-';?></td>
            </tr>
            <tr>
                <td>Tempat, Tanggal Lahir</td>
                <td width="75%">: <?php 
                    $tempat = !empty($data['tempat_lahir']) ? $data['tempat_lahir'] : '';
                    $tanggal = '';
                    if (!empty($data['tanggal_lahir']) && $data['tanggal_lahir'] !== '0000-00-00') {
                        $tanggal = date('d/m/Y', strtotime($data['tanggal_lahir']));
                    }
                    
                    if (!empty($tempat) && !empty($tanggal)) {
                        echo htmlspecialchars($tempat) . ', ' . $tanggal;
                    } elseif (!empty($tempat)) {
                        echo htmlspecialchars($tempat);
                    } elseif (!empty($tanggal)) {
                        echo $tanggal;
                    } else {
                        echo '-';
                    }
                ?></td>
            </tr>
            <tr>
                <td>Agama</td>
                <td width="75%">: <?php echo !empty($data['agama']) ? htmlspecialchars($data['agama']) : '-';?></td>
            </tr>
            <tr>
                <td>No HP Orang Tua Peserta</td>
                <td width="75%">: <?php echo !empty($data['no_hp_ortu']) ? htmlspecialchars($data['no_hp_ortu']) : '-';?></td>
            </tr>
            <tr>
                <td>Nama Guru/Dosen Pembimbing</td>
                <td width="75%">: <?php echo !empty($data['nama_pembimbing']) ? htmlspecialchars($data['nama_pembimbing']) : '-';?></td>
            </tr>
            <tr>
                <td>No HP Guru/Dosen Pembimbing</td>
                <td width="75%">: <?php echo !empty($data['no_hp_pembimbing']) ? htmlspecialchars($data['no_hp_pembimbing']) : '-';?></td>
            </tr>
            <tr>
                <td>Scan KTP/KK</td>
                <td width="75%">: <?php if($data['scan_ktp_kk']){
                    $ext = strtolower(pathinfo($data['scan_ktp_kk'], PATHINFO_EXTENSION));
                    $src = '/valendy_presensi/apps/mahasiswa/ktp_mahasiswa/'.$data['scan_ktp_kk'];
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
                        echo '<a href="#" class="preview-image" data-src="'.$src.'">Lihat File</a>';
                    } else if ($ext === 'pdf') {
                        echo '<a href="'.$src.'" target="_blank">Lihat File</a>';
                    } else {
                        echo '<a href="'.$src.'" target="_blank">Lihat File</a>';
                    }
                } else { echo '-'; } ?></td>
            </tr>
            <tr>
                <td>Scan BPJS/KIS/Asuransi</td>
                <td width="75%">: <?php if($data['scan_bpjs']){
                    $ext = strtolower(pathinfo($data['scan_bpjs'], PATHINFO_EXTENSION));
                    $src = '/valendy_presensi/apps/mahasiswa/bpjs_mahasiswa/'.$data['scan_bpjs'];
                    if (in_array($ext, ['jpg','jpeg','png','gif','bmp','webp'])) {
                        echo '<a href="#" class="preview-image" data-src="'.$src.'">Lihat File</a>';
                    } else if ($ext === 'pdf') {
                        echo '<a href="'.$src.'" target="_blank">Lihat File</a>';
                    } else {
                        echo '<a href="'.$src.'" target="_blank">Lihat File</a>';
                    }
                } else { echo '-'; } ?></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Akhir konten utama -->
</div>

<!-- Modal Preview Gambar -->
<div class="modal fade" id="modalPreviewImage" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content" style="background:transparent;box-shadow:none;border:none;">
      <div class="modal-body text-center" id="previewImageContent"></div>
    </div>
  </div>
</div>

<script>
$(document).on('click', '.preview-image', function(e) {
    e.preventDefault();
    var src = $(this).data('src');
    $('#previewImageContent').html('<img src="'+src+'" style="max-width:100%;max-height:70vh;border-radius:8px;box-shadow:0 0 8px #aaa;">');
    $('#main-content').addClass('blur');
    $('#modalPreviewImage').modal('show');
});
$('#modalPreviewImage').on('hidden.bs.modal', function () {
    $('#main-content').removeClass('blur');
    $('#previewImageContent').html('');
});
</script>