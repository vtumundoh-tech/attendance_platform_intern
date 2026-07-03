<?php
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=absensi-export-".date('YmdHis').".xls");

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
<p align="center" style="font-weight:bold;font-size:16pt">LAPORAN PRESENSI PESERTA MAGANG</p>
<p align="center" style="font-size:12pt">Nama: <?php echo $nama_mahasiswa; ?></p>
<p align="center" style="font-size:12pt">Periode: <?php echo date('d/m/Y', strtotime($tanggal_awal)); ?> s/d <?php echo date('d/m/Y', strtotime($tanggal_akhir)); ?></p>

<p style="color:red;font-style:italic;">
    Reminder: Silakan rapikan dan periksa kembali riwayat kegiatan Anda sebelum mengumpulkan/menggunakan file ini. Anda dapat menghapus pesan ini setelah file diunduh.
</p>

<table border="1" align="center" cellpadding="5" cellspacing="0">
    <tr style="background:#eee;font-weight:bold;">
        <th>No</th>
        <th>Hari</th>
        <th>Tanggal</th>
        <th>Waktu Masuk</th>
        <th>Waktu Pulang</th>
        <th>Status</th>
        <th>Keterangan</th>
    </tr>
    <?php
    $sql = "SELECT tbl_absensi.*, IFNULL(tbl_alasan.alasan, '-') AS alasan, DAYNAME(tbl_absensi.tanggal) AS hari
    FROM tbl_absensi
    LEFT JOIN tbl_alasan ON tbl_absensi.tanggal = tbl_alasan.tanggal AND tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa
    WHERE tbl_absensi.id_mahasiswa = '$id_mahasiswa' AND tbl_absensi.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ORDER BY tbl_absensi.tanggal ASC";
    $hasil = mysqli_query($kon, $sql);
    $no = 0;
    while ($data = mysqli_fetch_assoc($hasil)) {
        $no++;
        $hari = MendapatkanHari($data['hari']);
        $tgl = date('d/m/Y', strtotime($data['tanggal']));
        $waktu_masuk = $data['waktu_masuk'] ?: '-';
        $waktu_pulang = $data['waktu_pulang'] ?: '-';
        $status = ($data['status'] == 1 ? 'Hadir' : ($data['status'] == 2 ? 'Izin' : ($data['status'] == 3 ? 'Tidak Hadir' : '-')));
        $alasan = $data['alasan'];
        echo "<tr>";
        echo "<td align='center'>$no</td>";
        echo "<td align='center'>$hari</td>";
        echo "<td align='center'>$tgl</td>";
        echo "<td align='center'>$waktu_masuk</td>";
        echo "<td align='center'>$waktu_pulang</td>";
        echo "<td align='center'>$status</td>";
        echo "<td align='center'>$alasan</td>";
        echo "</tr>";
    }
    if ($no == 0) {
        echo "<tr><td colspan='7' align='center'>Tidak ada data presensi pada periode ini.</td></tr>";
    }
    ?>
</table> 