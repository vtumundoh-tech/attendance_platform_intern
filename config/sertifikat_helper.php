<?php
/**
 * Helper modul Sertifikat Magang: perhitungan kehadiran bulanan, kelulusan, bypass.
 */

if (defined('SERTIFIKAT_HELPER_LOADED')) return;
define('SERTIFIKAT_HELPER_LOADED', true);

function sertifikat_get_setting($kon): array
{
    $default = [
        'min_persentase_kehadiran' => 90.0,
        'max_upload_banding_mb' => 5,
    ];
    $q = @mysqli_query($kon, 'SELECT * FROM tbl_setting_sertifikat ORDER BY id_setting ASC LIMIT 1');
    if (!$q || mysqli_num_rows($q) === 0) {
        return $default;
    }
    $row = mysqli_fetch_assoc($q);
    return [
        'id_setting' => (int) $row['id_setting'],
        'min_persentase_kehadiran' => (float) $row['min_persentase_kehadiran'],
        'max_upload_banding_mb' => (int) $row['max_upload_banding_mb'],
    ];
}

function sertifikat_get_hari_libur_map($kon): array
{
    $map = [];
    $q = @mysqli_query($kon, 'SELECT tanggal, keterangan FROM tbl_hari_libur ORDER BY tanggal ASC');
    if (!$q) {
        return $map;
    }
    while ($r = mysqli_fetch_assoc($q)) {
        $map[$r['tanggal']] = $r['keterangan'] ?: 'Hari Libur';
    }
    return $map;
}

function sertifikat_is_weekend(string $date): bool
{
    $dow = (int) date('N', strtotime($date));
    return $dow >= 6;
}

function sertifikat_is_workday(string $date, array $libur_map): bool
{
    if (sertifikat_is_weekend($date)) {
        return false;
    }
    return !isset($libur_map[$date]);
}

/** Rentang efektif satu bulan dalam periode magang */
function sertifikat_month_range(string $yearMonth, string $mulai, string $akhir): ?array
{
    $first = $yearMonth . '-01';
    $last = date('Y-m-t', strtotime($first));
    $from = max($first, $mulai);
    $to = min($last, $akhir);
    if ($from > $to) {
        return null;
    }
    return ['from' => $from, 'to' => $to];
}

function sertifikat_list_year_months(string $mulai, string $akhir): array
{
    $months = [];
    $cur = date('Y-m-01', strtotime($mulai));
    $end = date('Y-m-01', strtotime($akhir));
    while ($cur <= $end) {
        $months[] = date('Y-m', strtotime($cur));
        $cur = date('Y-m-01', strtotime($cur . ' +1 month'));
    }
    return $months;
}

function sertifikat_count_workdays($kon, string $from, string $to, array $libur_map): int
{
    $count = 0;
    $d = $from;
    while ($d <= $to) {
        if (sertifikat_is_workday($d, $libur_map)) {
            $count++;
        }
        $d = date('Y-m-d', strtotime($d . ' +1 day'));
    }
    return $count;
}

/** Hadir (1) + Izin (2) — satu hari kerja dihitung sekali */
function sertifikat_count_hadir_izin($kon, int $id_mahasiswa, string $from, string $to, array $libur_map): int
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $sql = "SELECT DISTINCT tanggal FROM tbl_absensi
            WHERE id_mahasiswa = $id_mahasiswa
            AND tanggal BETWEEN '$from' AND '$to'
            AND status IN (1, 2)";
    $q = mysqli_query($kon, $sql);
    if (!$q) {
        return 0;
    }
    $count = 0;
    while ($r = mysqli_fetch_assoc($q)) {
        if (sertifikat_is_workday($r['tanggal'], $libur_map)) {
            $count++;
        }
    }
    return $count;
}

function sertifikat_nama_bulan_id(string $yearMonth): string
{
    $bulan = (int) date('m', strtotime($yearMonth . '-01'));
    $nama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $tahun = date('Y', strtotime($yearMonth . '-01'));
    return ($nama[$bulan] ?? $yearMonth) . ' ' . $tahun;
}

/**
 * Hitung rekap bulanan kehadiran selama periode magang.
 * @return array{periode_berakhir:bool,min_persen:float,months:array,lolos:bool,alasan:string}
 */
function sertifikat_hitung_kelulusan($kon, array $mahasiswa): array
{
    $setting = sertifikat_get_setting($kon);
    $min_persen = (float) $setting['min_persentase_kehadiran'];
    $libur_map = sertifikat_get_hari_libur_map($kon);

    $mulai = $mahasiswa['mulai_magang'] ?? '';
    $akhir = $mahasiswa['akhir_magang'] ?? '';
    $id_mahasiswa = (int) ($mahasiswa['id_mahasiswa'] ?? 0);

    $periode_berakhir = ($akhir !== '' && date('Y-m-d') > $akhir);
    $months = [];
    $lolos = true;
    $alasan_gagal = [];

    if ($mulai === '' || $akhir === '' || $id_mahasiswa <= 0) {
        return [
            'periode_berakhir' => $periode_berakhir,
            'min_persen' => $min_persen,
            'months' => [],
            'lolos' => false,
            'alasan' => 'Data periode magang tidak lengkap.',
        ];
    }

    foreach (sertifikat_list_year_months($mulai, $akhir) as $ym) {
        $range = sertifikat_month_range($ym, $mulai, $akhir);
        if (!$range) {
            continue;
        }
        $workdays = sertifikat_count_workdays($kon, $range['from'], $range['to'], $libur_map);
        $hadir_izin = sertifikat_count_hadir_izin($kon, $id_mahasiswa, $range['from'], $range['to'], $libur_map);
        $persen = $workdays > 0 ? round(($hadir_izin / $workdays) * 100, 2) : 100.0;
        $bulan_lolos = $persen >= $min_persen;
        if (!$bulan_lolos) {
            $lolos = false;
            $alasan_gagal[] = sertifikat_nama_bulan_id($ym) . " ($persen%)";
        }
        $months[] = [
            'year_month' => $ym,
            'label' => sertifikat_nama_bulan_id($ym),
            'from' => $range['from'],
            'to' => $range['to'],
            'workdays' => $workdays,
            'hadir_izin' => $hadir_izin,
            'persen' => $persen,
            'lolos' => $bulan_lolos,
        ];
    }

    if (empty($months)) {
        $lolos = false;
    }

    return [
        'periode_berakhir' => $periode_berakhir,
        'min_persen' => $min_persen,
        'months' => $months,
        'lolos' => $lolos,
        'alasan' => $lolos ? '' : 'Kehadiran bulanan di bawah syarat pada: ' . implode(', ', $alasan_gagal),
    ];
}

function sertifikat_has_bypass($kon, int $id_mahasiswa): bool
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $q = @mysqli_query($kon, "SELECT id_bypass FROM tbl_sertifikat_bypass
        WHERE id_mahasiswa = $id_mahasiswa AND aktif = 1 LIMIT 1");
    return $q && mysqli_num_rows($q) > 0;
}

function sertifikat_has_banding_disetujui($kon, int $id_mahasiswa): bool
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $q = @mysqli_query($kon, "SELECT id_banding FROM tbl_pengajuan_banding
        WHERE id_mahasiswa = $id_mahasiswa AND status = 'disetujui' LIMIT 1");
    return $q && mysqli_num_rows($q) > 0;
}

function sertifikat_get_banding_pending($kon, int $id_mahasiswa): ?array
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $q = @mysqli_query($kon, "SELECT * FROM tbl_pengajuan_banding
        WHERE id_mahasiswa = $id_mahasiswa AND status = 'pending' ORDER BY tanggal_ajuan DESC LIMIT 1");
    if ($q && ($r = mysqli_fetch_assoc($q))) {
        return $r;
    }
    return null;
}

function sertifikat_get_banding_terakhir($kon, int $id_mahasiswa): ?array
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $q = @mysqli_query($kon, "SELECT * FROM tbl_pengajuan_banding
        WHERE id_mahasiswa = $id_mahasiswa ORDER BY tanggal_ajuan DESC LIMIT 1");
    if ($q && ($r = mysqli_fetch_assoc($q))) {
        return $r;
    }
    return null;
}

/** Apakah peserta boleh mengunduh sertifikat */
function sertifikat_boleh_download($kon, array $mahasiswa): array
{
    $rekap = sertifikat_hitung_kelulusan($kon, $mahasiswa);
    $id_mahasiswa = (int) ($mahasiswa['id_mahasiswa'] ?? 0);

    if (!$rekap['periode_berakhir']) {
        return [
            'boleh' => false,
            'alasan' => 'Periode magang belum berakhir.',
            'rekap' => $rekap,
            'sumber' => 'belum_berakhir',
        ];
    }

    if ($rekap['lolos']) {
        return [
            'boleh' => true,
            'alasan' => '',
            'rekap' => $rekap,
            'sumber' => 'sistem',
        ];
    }

    if (sertifikat_has_bypass($kon, $id_mahasiswa) || sertifikat_has_banding_disetujui($kon, $id_mahasiswa)) {
        return [
            'boleh' => true,
            'alasan' => '',
            'rekap' => $rekap,
            'sumber' => 'bypass',
        ];
    }

    return [
        'boleh' => false,
        'alasan' => $rekap['alasan'],
        'rekap' => $rekap,
        'sumber' => 'tidak_lolos',
    ];
}

function sertifikat_set_bypass($kon, int $id_mahasiswa, string $keterangan, string $admin_kode): bool
{
    $id_mahasiswa = (int) $id_mahasiswa;
    $keterangan = mysqli_real_escape_string($kon, $keterangan);
    $admin_kode = mysqli_real_escape_string($kon, $admin_kode);
    mysqli_query($kon, "UPDATE tbl_sertifikat_bypass SET aktif = 0, revoked_at = NOW()
        WHERE id_mahasiswa = $id_mahasiswa AND aktif = 1");
    return (bool) mysqli_query($kon, "INSERT INTO tbl_sertifikat_bypass
        (id_mahasiswa, aktif, keterangan, created_by) VALUES
        ($id_mahasiswa, 1, '$keterangan', '$admin_kode')");
}

function sertifikat_count_banding_pending($kon): int
{
    $q = @mysqli_query($kon, "SELECT COUNT(*) AS total FROM tbl_pengajuan_banding WHERE status = 'pending'");
    if (!$q) {
        return 0;
    }
    $r = mysqli_fetch_assoc($q);
    return (int) ($r['total'] ?? 0);
}