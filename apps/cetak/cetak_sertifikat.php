<?php
session_start();
if (!isset($_SESSION['level']) || strtolower($_SESSION['level']) !== 'mahasiswa') {
    http_response_code(403);
    echo 'Akses ditolak.';
    exit;
}

include '../../config/database.php';
include_once '../../config/sertifikat_helper.php';

$kode = mysqli_real_escape_string($kon, $_SESSION['kode_pengguna'] ?? '');
$q = mysqli_query($kon, "SELECT * FROM tbl_mahasiswa WHERE kode_mahasiswa = '$kode' LIMIT 1");
if (!$q || !($mahasiswa = mysqli_fetch_assoc($q))) {
    http_response_code(404);
    echo 'Data tidak ditemukan.';
    exit;
}

$cek = sertifikat_boleh_download($kon, $mahasiswa);
if (!$cek['boleh']) {
    http_response_code(403);
    echo 'Anda sedang mencoba mengakses halaman yang belum berhak anda akses karena belum memenuhi syarat unduh sertifikat.';
    exit;
}

require '../../source/plugin/fpdf/fpdf.php';
include '../../config/function.php';

function u($text) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
}

$site_q = mysqli_query($kon, 'SELECT * FROM tbl_site LIMIT 1');
$site   = mysqli_fetch_assoc($site_q);

$pimpinan = !empty($site['pimpinan']) ? $site['pimpinan'] : '(Belum diisi)';

$nomor_sertifikat = 'SERT/' . date('Y') . '/' . str_pad($mahasiswa['id_mahasiswa'], 4, '0', STR_PAD_LEFT);

$namafile = 'Sertifikat-Magang-' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $mahasiswa['nama']) . '-' . date('Ymd') . '.pdf';

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetAutoPageBreak(false);

$W  = 297;
$cx = $W / 2; // 148.5

// ── [UBAH 1] Ukuran logo — cukup ubah dua angka ini ─────────────────────
$logo_w = 50;   // lebar logo dalam mm  ← sesuaikan di sini
$logo_h = 50;   // tinggi logo dalam mm ← sesuaikan di sini

// ── Border dekoratif ─────────────────────────────────────────────────────
$pdf->SetDrawColor(180, 150, 80);
$pdf->SetLineWidth(1.2);
$pdf->Rect(8, 8, 281, 194);
$pdf->SetLineWidth(0.4);
$pdf->Rect(11, 11, 275, 188);

// ── [UBAH 2] Nomor sertifikat — pojok kanan atas, dalam border ───────────
// Teks berakhir di x ≈ 283mm, border dalam berakhir di x = 286mm → aman
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(120, 120, 120);
$pdf->SetXY(0, 14);
$pdf->Cell($W - 14, 5, u('No: ' . $nomor_sertifikat), 0, 0, 'R');

// ── Logo (ukuran dari variabel di atas) ──────────────────────────────────
$logo_path = '../../apps/pengaturan/logo/' . $site['logo'];
$logo_y    = 18;
if (!empty($site['logo']) && file_exists($logo_path)) {
    $pdf->Image($logo_path, $cx - ($logo_w / 2), $logo_y, $logo_w, $logo_h);
}

// ── Nama instansi ─────────────────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetXY(0, $logo_y + $logo_h + 2);
$pdf->Cell($W, 6, u($site['nama_instansi'] ?? 'PT Angkasa Pura Indonesia'), 0, 1, 'C');

// ── Garis pemisah tipis ───────────────────────────────────────────────────
$pdf->SetDrawColor(180, 150, 80);
$pdf->SetLineWidth(0.5);
$pdf->Line(40, $logo_y + $logo_h + 10, 257, $logo_y + $logo_h + 10);

// ── Judul SERTIFIKAT MAGANG ───────────────────────────────────────────────
$pdf->SetFont('Arial', 'B', 22);
$pdf->SetTextColor(30, 30, 30);
$pdf->SetXY(0, $logo_y + $logo_h + 13);
$pdf->Cell($W, 10, 'SERTIFIKAT MAGANG', 0, 1, 'C');

// (nomor sertifikat tidak lagi di sini — sudah dipindah ke pojok kanan atas)

// ── Diberikan kepada ──────────────────────────────────────────────────────
$pdf->Ln(3);
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($W, 5, 'Diberikan kepada :', 0, 1, 'C');

// ── Nama mahasiswa ────────────────────────────────────────────────────────
$pdf->Ln(1);
$pdf->SetFont('Arial', 'B', 17);
$pdf->SetTextColor(20, 20, 20);
$pdf->Cell($W, 8, strtoupper(u($mahasiswa['nama'])), 0, 1, 'C');

// ── NIM & universitas ─────────────────────────────────────────────────────
$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell($W, 5, u('NIM: ' . $mahasiswa['nim'] . '   |   ' . $mahasiswa['universitas'] . ' - ' . $mahasiswa['jurusan']), 0, 1, 'C');

// ── Garis pemisah ─────────────────────────────────────────────────────────
$pdf->Ln(2);
$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line(60, $pdf->GetY(), 237, $pdf->GetY());
$pdf->Ln(3);

// ── Isi sertifikat ────────────────────────────────────────────────────────
$mulai_fmt = date('d F Y', strtotime($mahasiswa['mulai_magang']));
$akhir_fmt = date('d F Y', strtotime($mahasiswa['akhir_magang']));
$unit      = $mahasiswa['departemen_unitkerja'] ?: '-';

$baris = [
    'Telah melaksanakan program Magang / Praktek Kerja Lapangan (PKL)',
    u('Di ' . $unit),
    u($site['nama_instansi'] ?? ''),
    u('Pada tanggal ' . $mulai_fmt . ' s.d ' . $akhir_fmt . '.'),
];
foreach ($baris as $i => $line) {
    $pdf->SetFont('Arial', ($i === 0 ? 'B' : ''), 10);
    $pdf->SetTextColor(40, 40, 40);
    $pdf->Cell($W, 6, $line, 0, 1, 'C');
}

// ── Kota & bulan tahun terbit ─────────────────────────────────────────────
$pdf->Ln(5);
$nama_bulan_id = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];
$kota    = $site['alamat'] ? explode(',', $site['alamat'])[0] : 'Manado';
$bln_thn = $nama_bulan_id[(int)date('n')] . ' ' . date('Y');

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(40, 40, 40);
$pdf->Cell($W, 5, u($kota . ',   ' . $bln_thn), 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($W, 5, u(strtoupper($site['nama_instansi'] ?? '')), 0, 1, 'C');
if (!empty($site['cabang'])) {
    $pdf->Cell($W, 5, u(strtoupper($site['cabang'])), 0, 1, 'C');
}

// ── [UBAH 3] Tanda tangan — di tengah ────────────────────────────────────
$pdf->Ln(6);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell($W, 5, 'Pimpinan Instansi', 0, 1, 'C');

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

// ── Nama penandatangan (garis bawah + nama) ───────────────────────────────
$garis_x1 = $cx - 35;
$garis_x2 = $cx + 35;
$pdf->SetDrawColor(40, 40, 40);
$pdf->SetLineWidth(0.3);
$pdf->Line($garis_x1, $pdf->GetY(), $garis_x2, $pdf->GetY());
$pdf->Ln(1);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(30, 30, 30);
$pdf->Cell($W, 5, u($pimpinan), 0, 1, 'C');

// ── Output PDF ────────────────────────────────────────────────────────────
$pdf->Output('I', $namafile);
exit;