<?php
function MendapatkanBulan($bulan)
{
    switch ($bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function MendapatkanHari($hari)
{
    switch ($hari) {
        case "Monday":
            return "Senin";
            break;
        case "Tuesday":
            return "Selasa";
            break;
        case "Wednesday":
            return "Rabu";
            break;
        case "Thursday":
            return "Kamis";
            break;
        case "Friday":  
            return "Jumat";
            break;
        case "Saturday":
            return "Sabtu";
            break;
        case "Sunday":
            return "Minggu";
            break;
    }
}
?>

<?php
function AbsensiOtomatis($sql, $limit = null, $offset = null)
{
    include 'database.php';
    $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, tbl_mahasiswa.universitas, 
        tbl_mahasiswa.mulai_magang, tbl_mahasiswa.akhir_magang, tbl_absensi.id_absensi, 
        tbl_absensi.waktu_masuk, tbl_absensi.waktu_pulang,
        tbl_absensi.foto_masuk, tbl_absensi.foto_pulang,
        tbl_absensi.status_gps,
        (CASE
            WHEN tbl_absensi.status IS NULL THEN 'Belum Presensi'
            WHEN tbl_absensi.status = 1 THEN 'Hadir'
            WHEN tbl_absensi.status = 2 THEN 'Izin'
        ELSE 'Tidak Hadir' END) AS status, 
        DATE_FORMAT(CURDATE(), '%W') AS hari,
        DATE_FORMAT(CURDATE(), '%Y-%m-%d') AS tanggal
        FROM tbl_mahasiswa 
        JOIN tbl_user ON tbl_user.kode_pengguna = tbl_mahasiswa.kode_mahasiswa
        LEFT JOIN tbl_absensi ON 
            tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
        AND tbl_absensi.tanggal = CURDATE() 
        WHERE DAYNAME(CURDATE()) NOT IN ('Saturday', 'Sunday') AND 
            tbl_mahasiswa.mulai_magang <= CURDATE() AND
            tbl_mahasiswa.akhir_magang >= CURDATE() AND
            tbl_user.status_approval = 'approved'
            ORDER BY tbl_mahasiswa.nama ASC";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $offset, $limit";
    }
    return $sql;
}
?>

<?php
function PencarianAbsensi($nama, $tanggal_awal, $tanggal_akhir, $sort_by = 'tanggal', $sort_order = 'desc', $limit = null, $offset = null)
{
    include 'database.php';
    $allowed_sort = ['nama', 'tanggal'];
    $allowed_order = ['asc', 'desc'];
    if (!in_array(strtolower($sort_by), $allowed_sort))
        $sort_by = 'tanggal';
    if (!in_array(strtolower($sort_order), $allowed_order))
        $sort_order = 'desc';
    $sort_by_sql = ($sort_by == 'nama') ? 'tbl_mahasiswa.nama' : 'tbl_absensi.tanggal';
    $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, 
    tbl_absensi.foto_masuk, tbl_absensi.foto_pulang,
    tbl_absensi.waktu_masuk, tbl_absensi.waktu_pulang,
    tbl_absensi.status_gps,
    COALESCE(CASE tbl_absensi.status 
        WHEN 1 THEN 'Hadir' 
        WHEN 2 THEN 'Izin' 
    ELSE 'Tidak Hadir' END) as status,
    DATE_FORMAT(tbl_absensi.tanggal, '%W') AS hari, 
        tbl_absensi.tanggal, 
        tbl_mahasiswa.nama, tbl_mahasiswa.universitas, 
        tbl_mahasiswa.mulai_magang, tbl_mahasiswa.akhir_magang 
    FROM tbl_mahasiswa 
    JOIN tbl_user ON tbl_user.kode_pengguna = tbl_mahasiswa.kode_mahasiswa
    LEFT JOIN tbl_absensi 
        ON tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
    WHERE tbl_mahasiswa.mulai_magang <= CURDATE() AND 
        tbl_mahasiswa.akhir_magang >= CURDATE() AND 
    DAYNAME(tbl_absensi.tanggal) NOT IN ('Saturday', 'Sunday') AND 
        tbl_mahasiswa.nama LIKE '%$nama%' AND
        tbl_absensi.tanggal >= '$tanggal_awal' AND
        tbl_absensi.tanggal <= '$tanggal_akhir' AND
        tbl_user.status_approval = 'approved'
    ORDER BY $sort_by_sql $sort_order";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $offset, $limit";
    }
    return $sql;
}?>

<?php
function EditAbsensi($id_absensi)
{
    include 'database.php';
    $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, 
    tbl_absensi.status, tbl_absensi.tanggal,
    tbl_absensi.waktu_masuk, tbl_absensi.waktu_pulang,
    tbl_absensi.status_gps, tbl_absensi.jenis_absensi,
    tbl_absensi.foto_masuk, tbl_absensi.foto_pulang,
    tbl_alasan.id_alasan, tbl_alasan.alasan
    FROM tbl_absensi 
    LEFT JOIN tbl_alasan ON tbl_absensi.id_mahasiswa = tbl_alasan.id_mahasiswa 
        AND tbl_absensi.tanggal = tbl_alasan.tanggal
    WHERE tbl_absensi.id_absensi = '$id_absensi' LIMIT 1;";
    return $sql;
}
?>

<?php
function DataKegiatan($sql, $limit = null, $offset = null)
{
    include 'database.php';
    $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, 
    tbl_mahasiswa.universitas, tbl_kegiatan.id_kegiatan, 
    tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
    CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
    FROM tbl_mahasiswa JOIN tbl_kegiatan ON 
    tbl_mahasiswa.id_mahasiswa = tbl_kegiatan.id_mahasiswa 
    ORDER BY tbl_kegiatan.tanggal DESC";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $offset, $limit";
    }
    return $sql;
}
?>

<?php
function CariKegiatan($nama, $tanggal_awal, $tanggal_akhir, $limit = null, $offset = null)
{
    include 'database.php';
    $sql = "SELECT tbl_mahasiswa.id_mahasiswa, tbl_mahasiswa.nama, 
    tbl_mahasiswa.universitas, tbl_kegiatan.id_kegiatan, 
    tbl_kegiatan.kegiatan, tbl_kegiatan.tanggal, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%W') AS hari, 
    CONCAT(SUBSTRING(tbl_kegiatan.waktu_awal, 1, 5), ' - ', SUBSTRING(tbl_kegiatan.waktu_akhir, 1, 5)) AS waktu
    FROM tbl_mahasiswa JOIN tbl_kegiatan ON 
    tbl_mahasiswa.id_mahasiswa = tbl_kegiatan.id_mahasiswa
    WHERE tbl_mahasiswa.nama = '$nama' AND
    tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    ORDER BY tbl_kegiatan.tanggal DESC";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $offset, $limit";
    }
    return $sql;
}
?>

<?php
function MenampilkanKegiatan($id_mahasiswa)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_mahasiswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    ORDER BY tbl_kegiatan.waktu_awal ASC SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_mahasiswa = '$id_mahasiswa' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_mahasiswa 
    ORDER BY tbl_kegiatan.tanggal DESC, MIN(tbl_kegiatan.waktu_awal) ASC";
    return $sql;
}
?>

<?php
function MencarikanKegiatan($id_mahasiswa, $tanggal_awal, $tanggal_akhir)
{
    include 'database.php';
    $sql = "SELECT tbl_kegiatan.id_mahasiswa, 
    DATE_FORMAT(tbl_kegiatan.tanggal, '%d-%M-%Y') AS tanggal, 
    DAYNAME(tbl_kegiatan.tanggal) AS hari, 
    GROUP_CONCAT(CONCAT(tbl_kegiatan.kegiatan, 
    ' (', tbl_kegiatan.waktu_awal, ' - ', tbl_kegiatan.waktu_akhir, ')') 
    ORDER BY tbl_kegiatan.waktu_awal ASC SEPARATOR ', ') AS kegiatan 
    FROM tbl_kegiatan WHERE tbl_kegiatan.id_mahasiswa = '$id_mahasiswa'
    AND tbl_kegiatan.tanggal BETWEEN '$tanggal_awal' AND '$tanggal_akhir' 
    GROUP BY tbl_kegiatan.tanggal, tbl_kegiatan.id_mahasiswa 
    ORDER BY tbl_kegiatan.tanggal DESC, MIN(tbl_kegiatan.waktu_awal) ASC";
    return $sql;
}
?>

<?php
function WaktuKegiatan($string_kegiatan)
{
    $array_kegiatan = explode(", ", $string_kegiatan);
    $kegiatan = array();
    foreach ($array_kegiatan as $kgt) {
        $kgt_array = explode(" (", $kgt);
        if (count($kgt_array) < 2)
            continue; // skip jika tidak ada waktu
        $nama_kegiatan = trim($kgt_array[0]);
        $waktu_kegiatan = trim($kgt_array[1], ")");
        $waktu_array = explode(" - ", $waktu_kegiatan);
        if (count($waktu_array) < 2)
            continue; // skip jika format waktu tidak lengkap
        $waktu_awal = trim($waktu_array[0]);
        $waktu_akhir = trim($waktu_array[1]);
        $waktu_awal = date('H:i', strtotime($waktu_awal));
        $waktu_akhir = date('H:i', strtotime($waktu_akhir));
        $kegiatan[] = $waktu_awal . " - " . $waktu_akhir;
    }
    foreach ($kegiatan as $kegiatan) {
        echo $kegiatan . ' </br>';
    }
}
?>

<?php
function BarisKegiatan($string_kegiatan)
{
    $array_kegiatan = preg_split('/\r\n|\r|\n/', $string_kegiatan);
    $no1 = 1;
    foreach ($array_kegiatan as $kegiatan) {
        $kegiatan = trim($kegiatan);
        if ($kegiatan !== '') {
            echo $no1 . ". " . htmlspecialchars($kegiatan) . "<br>";
            $no1++;
        }
    }
}
?>

<?php
function MendapatkanAwalBulan($mulai_bulan)
{
    switch ($mulai_bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function MendapatkanAkhirBulan($akhir_bulan)
{
    switch ($akhir_bulan) {
        case 1:
            return "Januari";
            break;
        case 2:
            return "Februari";
            break;
        case 3:
            return "Maret";
            break;
        case 4:
            return "April";
            break;
        case 5:
            return "Mei";
            break;
        case 6:
            return "Juni";
            break;
        case 7:
            return "Juli";
            break;
        case 8:
            return "Agustus";
            break;
        case 9:
            return "September";
            break;
        case 10:
            return "Oktober";
            break;
        case 11:
            return "November";
            break;
        case 12:
            return "Desember";
            break;
        default:
            return "Bulan tidak valid";
            break;
    }
}
?>

<?php
function StatusAbsensi($status)
{
    switch ($status) {
        case 1:
            $status = "Hadir";
            break;
        case 2:
            $status = "Izin";
            break;
        case 3:
            $status = "Tidak Hadir";
            break;
    }
    return $status;
}
?>

<?php
function AmbilSemuaAbsensi($sort_by = 'tanggal', $sort_order = 'desc', $limit = null, $offset = null)
{
    include 'database.php';
    $allowed_sort = ['nama', 'tanggal'];
    $allowed_order = ['asc', 'desc'];
    if (!in_array(strtolower($sort_by), $allowed_sort))
        $sort_by = 'tanggal';
    if (!in_array(strtolower($sort_order), $allowed_order))
        $sort_order = 'desc';
    $sort_by_sql = ($sort_by == 'nama') ? 'tbl_mahasiswa.nama' : 'tbl_absensi.tanggal';
    $sql = "SELECT tbl_absensi.id_absensi, tbl_absensi.id_mahasiswa, 
        tbl_absensi.foto_masuk, tbl_absensi.foto_pulang,
        tbl_absensi.waktu_masuk, tbl_absensi.waktu_pulang,
        tbl_absensi.status_gps,
        COALESCE(CASE tbl_absensi.status 
            WHEN 1 THEN 'Hadir' 
            WHEN 2 THEN 'Izin' 
        ELSE 'Tidak Hadir' END) as status,
        DATE_FORMAT(tbl_absensi.tanggal, '%W') AS hari, 
        tbl_absensi.tanggal, 
        tbl_mahasiswa.nama, tbl_mahasiswa.universitas, 
        tbl_mahasiswa.mulai_magang, tbl_mahasiswa.akhir_magang 
        FROM tbl_mahasiswa 
        JOIN tbl_user ON tbl_user.kode_pengguna = tbl_mahasiswa.kode_mahasiswa
        LEFT JOIN tbl_absensi ON tbl_absensi.id_mahasiswa = tbl_mahasiswa.id_mahasiswa 
        WHERE tbl_absensi.tanggal IS NOT NULL
            AND tbl_user.status_approval = 'approved'
        ORDER BY $sort_by_sql $sort_order";
    if ($limit !== null && $offset !== null) {
        $sql .= " LIMIT $offset, $limit";
    }
    return $sql;
}
?>