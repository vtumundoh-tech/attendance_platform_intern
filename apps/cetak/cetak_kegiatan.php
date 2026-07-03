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
include '../../config/function.php';
require('../../source/plugin/fpdf/fpdf.php');

// ✅ FIX: Class didefinisikan di ATAS sebelum dipakai
class PDF_FancyTable extends FPDF {
    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }
}

// ✅ FIX: Instansiasi PDF_FancyTable, bukan FPDF
$pdf = new PDF_FancyTable('P', 'mm', 'Letter');

// Ambil data site
$query = mysqli_query($kon, "SELECT * FROM tbl_site LIMIT 1");
$row = mysqli_fetch_array($query);

// Ambil data mahasiswa
$sql = "SELECT * FROM tbl_mahasiswa WHERE id_mahasiswa=$id_mahasiswa";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);

$nama = $data ? preg_replace('/[^A-Za-z0-9_\-]/', '_', $data['nama']) : 'mahasiswa';
$namafile = 'Kegiatan-Harian-' . $nama . '-' . date('YmdHis') . '.pdf';

$pembimbing_lapangan = !empty($data['pembimbing_magang']) ? $data['pembimbing_magang'] : '(Belum diisi)';
$pembimbing_hr = $row['pembimbing'];
$pimpinan = $row['pimpinan'];

// ---- HEADER HALAMAN ----
$pdf->AddPage();
$pdf->Image('../../apps/pengaturan/logo/' . $row['logo'], 10, 5, 20, 20);
$pdf->SetFont('Arial', 'B', 8);
$pdf->Cell(0, 7, strtoupper($row['nama_instansi']), 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 6);
$pdf->Cell(0, 7, $row['alamat'] . ', Telp ' . $row['no_telp'], 0, 1, 'C');
$pdf->Cell(0, 7, $row['website'], 0, 1, 'C');

$pdf->SetLineWidth(1);
$pdf->Line(10, 31, 206, 31);
$pdf->SetLineWidth(0);
$pdf->Line(10, 32, 206, 32);

// ---- JUDUL ----
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 7, 'JURNAL KEGIATAN HARIAN', 0, 1, 'C');
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 5, '', 0, 1, 'C');
$pdf->Cell(0, 5, '', 0, 1, 'C');

// ---- INFO MAHASISWA ----
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Nama ', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['nama'], 0, 1);
$pdf->Cell(30, 6, 'Nim ', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['nim'], 0, 1);
$pdf->Cell(30, 6, 'Universitas ', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['universitas'], 0, 1);
$pdf->Cell(30, 6, 'Jurusan ', 0, 0);
$pdf->Cell(31, 6, ': ' . $data['jurusan'], 0, 1);

// ---- HEADER TABEL ----
$pdf->Cell(10, 3, '', 0, 1);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(30, 6, 'Tanggal', 1, 0, 'C');
$pdf->Cell(20, 6, 'Hari', 1, 0, 'C');
$pdf->Cell(20, 6, 'Jam Awal', 1, 0, 'C');
$pdf->Cell(20, 6, 'Jam Akhir', 1, 0, 'C');
$pdf->Cell(90, 6, 'Kegiatan', 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);

// ---- ISI TABEL ----
$kegiatan_per_tanggal = [];
$sql = "SELECT *, DAYNAME(tanggal) AS hari 
        FROM tbl_kegiatan 
        WHERE id_mahasiswa = '$id_mahasiswa' 
          AND tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
        ORDER BY tanggal ASC, waktu_awal ASC";
$hasil = mysqli_query($kon, $sql);
while ($row_kegiatan = mysqli_fetch_assoc($hasil)) {
    $kegiatan_per_tanggal[$row_kegiatan['tanggal']][] = $row_kegiatan;
}

if (empty($kegiatan_per_tanggal)) {
    $pdf->Cell(180, 6, 'Tidak ada data kegiatan pada rentang tanggal yang dipilih.', 1, 1, 'C');
} else {
    foreach ($kegiatan_per_tanggal as $tanggal => $list) {
        $tgl   = date('d', strtotime($tanggal));
        $bulan = date('m', strtotime($tanggal));
        $tahun = date('Y', strtotime($tanggal));
        $hari  = MendapatkanHari($list[0]['hari']);
        $first = true;

        foreach ($list as $item) {
            $waktu_awal  = date('H:i', strtotime($item['waktu_awal']));
            $waktu_akhir = date('H:i', strtotime($item['waktu_akhir']));
            $lines = preg_split('/\r\n|\r|\n/', $item['kegiatan']);

            foreach ($lines as $line) {
                $line = trim(ltrim($line, '- '));
                if ($line === '') continue;

                $teks      = '- ' . $line;
                $nb        = $pdf->NbLines(90, $teks);
                $rowHeight = 6 * $nb;

                $pdf->CheckPageBreak($rowHeight);
                $border = $first ? 1 : 'LRB';

                $pdf->Cell(30, $rowHeight, $first ? $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun : '', $border, 0, 'C');
                $pdf->Cell(20, $rowHeight, $first ? $hari : '', $border, 0, 'C');
                $pdf->Cell(20, $rowHeight, $waktu_awal, $border, 0, 'C');
                $pdf->Cell(20, $rowHeight, $waktu_akhir, $border, 0, 'C');
                $pdf->MultiCell(90, 6, $teks, $border, 'L');

                $first = false;
            }
        }
    }
}

// ---- TANDA TANGAN ----
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 15, '', 0, 1);

$pdf->Cell(98, 5, 'Menyetujui,', 0, 0, 'C');
$pdf->Cell(98, 5, 'Menyetujui,', 0, 1, 'C');
$pdf->Cell(98, 5, 'Pembimbing Lapangan', 0, 0, 'C');
$pdf->Cell(98, 5, 'Human Capital', 0, 1, 'C');

$pdf->Cell(0, 20, '', 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(98, 5, $pembimbing_lapangan, 0, 0, 'C');
$pdf->Cell(98, 5, $pembimbing_hr, 0, 1, 'C');

$pdf->Cell(0, 10, '', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(196, 5, 'Mengetahui,', 0, 1, 'C');
$pdf->Cell(196, 5, 'Pimpinan Instansi', 0, 1, 'C');

$pdf->Cell(0, 20, '', 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(196, 5, $pimpinan, 0, 1, 'C');

// ✅ FIX: Tidak ada manual header() di atas — biarkan FPDF yang handle
$pdf->Output('I', $namafile);
exit;