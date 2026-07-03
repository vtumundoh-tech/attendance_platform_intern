<?php
require_once '../../source/plugin/fpdf/fpdf.php';
include '../../config/database.php';

if (!isset($_GET['id_mahasiswa'])) {
    die('ID mahasiswa tidak ditemukan.');
}
$id_mahasiswa = intval($_GET['id_mahasiswa']);

// Query data mahasiswa
$sql = "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa' LIMIT 1";
$result = mysqli_query($kon, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    die('Data mahasiswa tidak ditemukan.');
}
$data = mysqli_fetch_assoc($result);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'Data Peserta OJT/PKL/Magang',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln(5);

function row($pdf, $label, $value) {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(60,8,$label,0,0);
    $pdf->SetFont('Arial','',11);
    $pdf->MultiCell(0,8,': '.$value,0,1);
}

// Format Tempat, Tanggal Lahir secara aman
$tempat_lahir = !empty($data['tempat_lahir']) ? $data['tempat_lahir'] : '';
$tanggal_lahir = '';
if (!empty($data['tanggal_lahir']) && $data['tanggal_lahir'] !== '0000-00-00') {
    $tanggal_lahir = date('d-m-Y', strtotime($data['tanggal_lahir']));
}
$ttl = '-';
if (!empty($tempat_lahir) && !empty($tanggal_lahir)) {
    $ttl = $tempat_lahir . ', ' . $tanggal_lahir;
} elseif (!empty($tempat_lahir)) {
    $ttl = $tempat_lahir;
} elseif (!empty($tanggal_lahir)) {
    $ttl = $tanggal_lahir;
}

row($pdf, 'Nama', !empty($data['nama']) ? $data['nama'] : '-');
row($pdf, 'Universitas', !empty($data['universitas']) ? $data['universitas'] : '-');
row($pdf, 'NIM', !empty($data['nim']) ? $data['nim'] : '-');
row($pdf, 'Departemen/Unit Kerja', !empty($data['departemen_unitkerja']) ? $data['departemen_unitkerja'] : '-');
row($pdf, 'Jurusan', !empty($data['jurusan']) ? $data['jurusan'] : '-');
row($pdf, 'Tempat, Tanggal Lahir', $ttl);
row($pdf, 'Agama', !empty($data['agama']) ? $data['agama'] : '-');
row($pdf, 'Alamat', !empty($data['alamat']) ? $data['alamat'] : '-');
row($pdf, 'No Telp', !empty($data['no_telp']) ? $data['no_telp'] : '-');
row($pdf, 'No HP Ortu', !empty($data['no_hp_ortu']) ? $data['no_hp_ortu'] : '-');
row($pdf, 'Nama Pembimbing', !empty($data['nama_pembimbing']) ? $data['nama_pembimbing'] : '-');
row($pdf, 'No HP Pembimbing', !empty($data['no_hp_pembimbing']) ? $data['no_hp_pembimbing'] : '-');
row($pdf, 'Mulai Magang', !empty($data['mulai_magang']) ? date('d-m-Y', strtotime($data['mulai_magang'])) : '-');
row($pdf, 'Akhir Magang', !empty($data['akhir_magang']) ? date('d-m-Y', strtotime($data['akhir_magang'])) : '-');

$fotoDir = __DIR__ . '/foto/';
$ktpDir = __DIR__ . '/ktp_mahasiswa/';
$bpjsDir = __DIR__ . '/bpjs_mahasiswa/';

// Foto profil
if (!empty($data['foto']) && is_file($fotoDir . $data['foto'])) {
    $imgHeight = 40;
    if ($pdf->GetY() + $imgHeight > 270) {
        $pdf->AddPage();
    }
    $pdf->Ln(3);
    $pdf->SetFont('Arial','B',11);
    $y = $pdf->GetY();
    $pdf->Cell(60, 40, 'Foto Profil', 0, 0, 'L');
    $pdf->SetFont('Arial','',11);
    $pdf->Cell(5, 40, ':', 0, 0, 'C');
    $pdf->Image($fotoDir . $data['foto'], $pdf->GetX(), $y, 40, 40);
    $pdf->Ln(45);
}
// Scan KTP/KK (gambar ditampilkan; PDF hanya keterangan file)
if (!empty($data['scan_ktp_kk'])) {
    $ktpPath = $ktpDir . $data['scan_ktp_kk'];
    if (is_file($ktpPath)) {
        $ext = strtolower(pathinfo($data['scan_ktp_kk'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'], true)) {
            $imgHeight = 50;
            if ($pdf->GetY() + $imgHeight > 270) {
                $pdf->AddPage();
            }
            $pdf->SetFont('Arial','B',11);
            $y = $pdf->GetY();
            $pdf->Cell(60, 50, 'Scan KTP/KK', 0, 0, 'L');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(5, 50, ':', 0, 0, 'C');
            $pdf->Image($ktpPath, $pdf->GetX(), $y, 80, 50);
            $pdf->Ln(55);
        } else {
            row($pdf, 'Scan KTP/KK', 'Berkas terunggah (' . $ext . ') — buka file asli di sistem.');
        }
    } else {
        row($pdf, 'Scan KTP/KK', '(File tidak ditemukan di server)');
    }
}
// Scan BPJS
if (!empty($data['scan_bpjs'])) {
    $bpjsPath = $bpjsDir . $data['scan_bpjs'];
    if (is_file($bpjsPath)) {
        $ext = strtolower(pathinfo($data['scan_bpjs'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif'], true)) {
            $imgHeight = 50;
            if ($pdf->GetY() + $imgHeight > 270) {
                $pdf->AddPage();
            }
            $pdf->SetFont('Arial','B',11);
            $y = $pdf->GetY();
            $pdf->Cell(60, 50, 'Scan BPJS', 0, 0, 'L');
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(5, 50, ':', 0, 0, 'C');
            $pdf->Image($bpjsPath, $pdf->GetX(), $y, 80, 50);
            $pdf->Ln(55);
        } else {
            row($pdf, 'Scan BPJS', 'Berkas terunggah (' . $ext . ') — buka file asli di sistem.');
        }
    } else {
        row($pdf, 'Scan BPJS', '(File tidak ditemukan di server)');
    }
}

$pdf->Output('I', 'Data_Peserta_OJT_PKL_Magang_'.$data['nama'].'.pdf');
exit; 