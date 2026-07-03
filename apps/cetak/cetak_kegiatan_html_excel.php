<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=kegiatan-export-".date('YmdHis').".xls");

if (!isset($_GET["id_mahasiswa"]) || !isset($_GET["tanggal_awal"]) || !isset($_GET["tanggal_akhir"])) {
    echo "<b>Parameter tidak lengkap.</b>";
    exit;
}

$id_mahasiswa = $_GET["id_mahasiswa"];
$tanggal_awal = trim($_GET["tanggal_awal"]);
$tanggal_akhir = trim($_GET["tanggal_akhir"]);

if ($tanggal_awal === '' || $tanggal_akhir === '') {
    echo "<b>Harap mengisi rentang tanggal terlebih dahulu.</b>";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) || !strtotime($tanggal_awal)) {
    echo "<b>Format tanggal awal tidak valid.</b>";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir) || !strtotime($tanggal_akhir)) {
    echo "<b>Format tanggal akhir tidak valid.</b>";
    exit;
}

include '../../config/database.php';
include '../../config/function.php';

// Ambil nama mahasiswa
$kueri = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa' LIMIT 1");
$mahasiswa = mysqli_fetch_assoc($kueri);
$nama_mahasiswa = $mahasiswa ? $mahasiswa['nama'] : '-';

?>
<p align="center" style="font-weight:bold;font-size:16pt">LAPORAN KEGIATAN MAHASISWA</p>
<p align="center" style="font-size:12pt">Nama: <?php echo $nama_mahasiswa; ?></p>
<p align="center" style="font-size:12pt">Periode: <?php echo date('d/m/Y', strtotime($tanggal_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tanggal_akhir)); ?></p>

<p style="color:red;font-style:italic;">
    Reminder: File ini untuk mempermudah penulisan kegiatan harian peserta magang, silakan rapikan dan periksa kembali riwayat kegiatan Anda sebelum mengumpulkan/menggunakan file ini. Jika tidak ingin menggunakan file ini anda dapat mengcopy seluruh isi kolom kegiatan anda kemudian di paste di new file excel untuk anda gunakan kembali. Jika ingin menggunakan file ini anda dapat menghapus pesan ini setelah file diunduh.
</p>
<p style="color:red;font-style:italic;">
    Untuk mengedit silakan lakukan spasi sebelum tanda (-) di awal agar tidak terjadi error. Contoh error: tidak ada spasi sebagai karakter pertama dalam teks (- Masuk), Contoh tidak error: ada spasi sebagai karakter pertama dalam teks ( - Masuk).
</p>

<table border="1" align="center" cellpadding="5" cellspacing="0">
    <tr style="background:#eee;font-weight:bold;">
        <th>No</th>
        <th>Hari</th>
        <th>Tanggal</th>
        <th>Kegiatan</th>
        <th>Waktu Awal</th>
        <th>Waktu Akhir</th>
    </tr>
    <?php
    $kegiatan_per_tanggal = [];
    $sql = "SELECT *, DAYNAME(tanggal) AS hari FROM tbl_kegiatan WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' ORDER BY tanggal ASC, waktu_awal ASC";
    $hasil = mysqli_query($kon, $sql);
    while ($data = mysqli_fetch_assoc($hasil)) {
        $tanggal_key = $data['tanggal'];
        $kegiatan_per_tanggal[$tanggal_key][] = $data;
    }
    $no = 0;
    foreach ($kegiatan_per_tanggal as $tanggal => $list) {
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
        $kegiatan_str = implode('<br>', $kegiatan_arr);
        $waktu_awal_str = implode('<br>', $waktu_awal_arr);
        $waktu_akhir_str = implode('<br>', $waktu_akhir_arr);
        echo '<tr>';
        echo '<td align="center">'.$no.'</td>';
        echo '<td align="center">'.$hari.'</td>';
        echo '<td align="center">'.$tgl.' '.MendapatkanBulan($bulan).' '.$tahun.'</td>';
        echo '<td style="white-space:pre-line; mso-data-placement:same-cell;">'.$kegiatan_str.'</td>';
        echo '<td align="center">'.$waktu_awal_str.'</td>';
        echo '<td align="center">'.$waktu_akhir_str.'</td>';
        echo '</tr>';
    }
    if ($no == 0) {
        echo "<tr><td colspan='6' align='center'>Tidak ada data kegiatan pada periode ini.</td></tr>";
    }
    ?>
</table>

<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            <br>
            <button type="submit" name="cetak" id="cetak" class="btn btn-primary" ><i class="fa fa-print"></i> Cetak</button>
            <a href="#" id="exportExcel" class="btn btn-success" style="margin-left:10px;"><i class="fa fa-file-excel-o"></i> Export Excel</a>
        </div>
    </div>
</div>
<script>
    // Script untuk mengisi link export excel sesuai input tanggal
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.querySelector('form');
        var exportBtn = document.getElementById('exportExcel');
        form.addEventListener('input', function() {
            var id_mahasiswa = form.querySelector('input[name=\"id_mahasiswa\"]').value;
            var tanggal_awal = form.querySelector('input[name=\"tanggal_awal\"]').value;
            var tanggal_akhir = form.querySelector('input[name=\"tanggal_akhir\"]').value;
            exportBtn.href = 'apps/cetak/cetak_kegiatan_html_excel.php?id_mahasiswa=' + encodeURIComponent(id_mahasiswa) + '&tanggal_awal=' + encodeURIComponent(tanggal_awal) + '&tanggal_akhir=' + encodeURIComponent(tanggal_akhir);
        });
        // Set awal saat load
        form.dispatchEvent(new Event('input'));
    });
</script> 