<?php
// Validasi parameter GET
if (!isset($_GET["id_mahasiswa"]) || !isset($_GET["tanggal_awal"]) || !isset($_GET["tanggal_akhir"])) {
    http_response_code(400);
    echo "<h2>Parameter tidak lengkap. Silakan akses fitur cetak dari aplikasi.</h2>";
    exit;
}

$id_mahasiswa = (int) $_GET["id_mahasiswa"];
$tanggal_awal = trim($_GET["tanggal_awal"]);
$tanggal_akhir = trim($_GET["tanggal_akhir"]);

if ($tanggal_awal === '' || $tanggal_akhir === '') {
    http_response_code(400);
    echo "<h2>Harap mengisi rentang tanggal terlebih dahulu.</h2>";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) || !strtotime($tanggal_awal)) {
    http_response_code(400);
    echo "<h2>Format tanggal awal tidak valid.</h2>";
    exit;
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir) || !strtotime($tanggal_akhir)) {
    http_response_code(400);
    echo "<h2>Format tanggal akhir tidak valid.</h2>";
    exit;
}

    include '../../config/database.php';
    $kueri = mysqli_query($kon, "SELECT nama FROM tbl_mahasiswa WHERE id_mahasiswa=" . (int) $id_mahasiswa . " LIMIT 1");
    $hasilnama = mysqli_fetch_array($kueri);
    $nama = $hasilnama ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $hasilnama['nama']) : 'mahasiswa';
    $namafile = 'Absensi-' . $nama . '-' . date('YmdHis') . '.pdf';

    require('../../source/plugin/fpdf/fpdf.php');
    $pdf = new FPDF('P', 'mm','Letter');

    include '../../config/function.php';
    $query = mysqli_query($kon, "select * from tbl_site limit 1");    
    $row = mysqli_fetch_array($query);
    $pembimbing = $row['pembimbing'];

    $pdf->AddPage();

    $pdf->Image('../../apps/pengaturan/logo/'.$row['logo'],10,5,21,27);
    $pdf->SetFont('Arial','B',8);
    $pdf->Cell(0,7,strtoupper($row['nama_instansi']),0,1,'C');
    $pdf->SetFont('Arial','B',6);
    $pdf->Cell(0,7,$row['alamat'].', Telp '.$row['no_telp'],0,1,'C');
    $pdf->Cell(0,7,$row['website'],0,1,'C');

    $pdf->SetLineWidth(1);
    $pdf->Line(10,31,206,31);
    $pdf->SetLineWidth(0);
    $pdf->Line(10,32,206,32);

    $sql="select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa";
    $hasil=mysqli_query($kon,$sql);
    $data = mysqli_fetch_array($hasil); 

    // Pilih pembimbing magang: jika mahasiswa punya, pakai itu, jika tidak pakai default dari pengaturan
    $pembimbing = !empty($data['pembimbing_magang']) ? $data['pembimbing_magang'] : $row['pembimbing'];

    $awal_magang = $data['mulai_magang'];
    $akhir_magang = $data['akhir_magang'];
    $mulai_bulan = date("m", strtotime($awal_magang));
    $akhir_bulan = date("m", strtotime($akhir_magang));
    $mulai_hari = date("d", strtotime($awal_magang));
    $akhir_hari = date("d", strtotime($akhir_magang));
    $akhir_tahun = date("Y", strtotime($akhir_magang));

    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,5,'',0,1,'C');
    $pdf->Cell(0,7,'DAFTAR HADIR PESERTA MAGANG',0,1,'C');
    $pdf->Cell(0,7,'PERIODE '.$mulai_hari.' '.MendapatkanAwalBulan($mulai_bulan).' - '.$akhir_hari.' '.MendapatkanAkhirBulan($akhir_bulan).' '.$akhir_tahun,0,1,'C');
    $pdf->Cell(0,5,'',0,1,'C');
    $pdf->Cell(0,5,'',0,1,'C');

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(30,6,'Nama ',0,0);
    $pdf->Cell(31,6,': '.$data['nama'],0,1);
    $pdf->Cell(30,6,'Nim ',0,0);
    $pdf->Cell(31,6,': '.$data['nim'],0,1);
    $pdf->Cell(30,6,'Universitas ',0,0);
    $pdf->Cell(31,6,': '.$data['universitas'],0,1);
    $pdf->Cell(30,6,'Jurusan ',0,0);
    $pdf->Cell(31,6,': '.$data['jurusan'],0,1);

    $pdf->Cell(10,3,'',0,1);
    $pdf->SetFont('Arial','B',10);
    $pdf->Cell(10,6,'No',1,0,'C');
    $pdf->Cell(40,6,'Hari',1,0,'C');
    $pdf->Cell(50,6,'Tanggal',1,0,'C');
    $pdf->Cell(47,6,'Waktu',1,0,'C');
    $pdf->Cell(48,6,'Keterangan',1,1,'C');
    $pdf->SetFont('Arial','',10);

    $no= 0;

    $sql="SELECT id_absensi, id_mahasiswa, status, tanggal, waktu_masuk, waktu_pulang,
    DATE_FORMAT(tanggal, '%W') AS hari 
    FROM tbl_absensi WHERE id_mahasiswa = $id_mahasiswa AND 
    tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir'
    ORDER BY tanggal ASC";
    $hasil=mysqli_query($kon,$sql);

    while ($row_absen = mysqli_fetch_assoc($hasil)){
        $waktu_masuk = $row_absen['waktu_masuk'] ? date('H:i', strtotime($row_absen['waktu_masuk'])) : '';
        $waktu_pulang = $row_absen['waktu_pulang'] ? date('H:i', strtotime($row_absen['waktu_pulang'])) : '';
        $waktu = ($waktu_masuk && $waktu_pulang) ? ($waktu_masuk.' - '.$waktu_pulang) : ($waktu_masuk ?: ($waktu_pulang ?: '-'));
        $status = $row_absen['status'];
        $hari = $row_absen['hari'];
        $tgl = date("d", strtotime($row_absen['tanggal']));
        $bulan = date("m", strtotime($row_absen['tanggal']));
        $tahun = date("Y", strtotime($row_absen['tanggal']));
        $no++;
        $pdf->Cell(10,6,$no,1,0,'C');
        $pdf->Cell(40,6,MendapatkanHari($hari),1,0,'C');
        $pdf->Cell(50,6,$tgl.' '.MendapatkanBulan($bulan).' '.$tahun.'',1,0,'C');
        $pdf->Cell(47,6,$waktu,1,0,'C');
        $pdf->Cell(48,6,StatusAbsensi($status),1,1,'C');
    }
    if ($no === 0) {
        $pdf->Cell(195,6,'Tidak ada data presensi pada rentang tanggal yang dipilih.',1,1,'C');
    }

    $pembimbing_lapangan = !empty($data['pembimbing_magang']) ? $data['pembimbing_magang'] : '(Belum diisi)';
    $pembimbing_hr = $row['pembimbing'];
    $pimpinan = $row['pimpinan'];

    $pdf->SetFont('Arial','',10);
    $pdf->Cell(0,15,'',0,1);
    
    // Baris 1: Menyetujui (Pembimbing Lapangan & HR)
    $pdf->Cell(98, 5, 'Menyetujui,', 0, 0, 'C');
    $pdf->Cell(98, 5, 'Menyetujui,', 0, 1, 'C');
    
    $pdf->Cell(98, 5, 'Pembimbing Lapangan', 0, 0, 'C');
    $pdf->Cell(98, 5, 'Human Capital', 0, 1, 'C');
    
    // Ruang Tanda Tangan
    $pdf->Cell(0, 20, '', 0, 1);
    
    // Nama Pembimbing
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(98, 5, $pembimbing_lapangan, 0, 0, 'C');
    $pdf->Cell(98, 5, $pembimbing_hr, 0, 1, 'C');
    
    // Jarak ke tanda tangan Pimpinan
    $pdf->Cell(0, 10, '', 0, 1);
    
    // Baris 2: Mengetahui (Pimpinan Instansi)
    $pdf->SetFont('Arial','',10);
    $pdf->Cell(196, 5, 'Mengetahui,', 0, 1, 'C');
    $pdf->Cell(196, 5, 'Pimpinan Instansi', 0, 1, 'C');
    
    // Ruang Tanda Tangan Pimpinan
    $pdf->Cell(0, 20, '', 0, 1);
    
    // Nama Pimpinan
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(196, 5, $pimpinan, 0, 1, 'C');

    // ── Gambar tanda tangan digital (jika sudah diupload admin) ───────────────
$ttd_w = 45;  // lebar tanda tangan dalam mm ← bisa disesuaikan
$ttd_h = 18;  // tinggi tanda tangan dalam mm ← bisa disesuaikan
$ttd_path = '../../apps/pengaturan/ttd/' . ($site['ttd_pimpinan'] ?? '');

if (!empty($site['ttd_pimpinan']) && file_exists($ttd_path)) {
    // Posisi X agar tanda tangan tepat di tengah
    $ttd_x = $cx - ($ttd_w / 2);
    $ttd_y = $pdf->GetY() + 2;
    $pdf->Image($ttd_path, $ttd_x, $ttd_y, $ttd_w, $ttd_h);
    $pdf->Ln($ttd_h + 4);
} else {
    // Ruang kosong jika belum ada TTD
    $pdf->Ln(16);
}

    // Kirim langsung ke browser (hindari simpan ke path relatif yang sering gagal)
    $pdf->Output('I', $namafile);
    exit;
?>