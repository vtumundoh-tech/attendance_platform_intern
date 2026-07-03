<?php
/**
 * Pengaturan lokasi absensi dari tbl_setting_absensi (dengan fallback jika migrasi belum dijalankan).
 */
function absensi_lokasi_defaults(): array
{
    return [
        'kantor_latitude' => 1.54545,
        'kantor_longitude' => 124.92220,
        'radius_meter' => 600,
    ];
}

function absensi_lokasi_ambil(mysqli $kon): array
{
    $def = absensi_lokasi_defaults();
    $res = mysqli_query($kon, 'SELECT * FROM tbl_setting_absensi LIMIT 1');
    if (!$res) {
        return $def;
    }
    $row = mysqli_fetch_assoc($res);
    if (!$row) {
        return $def;
    }
    return [
        'kantor_latitude' => isset($row['kantor_latitude']) ? (float) $row['kantor_latitude'] : $def['kantor_latitude'],
        'kantor_longitude' => isset($row['kantor_longitude']) ? (float) $row['kantor_longitude'] : $def['kantor_longitude'],
        'radius_meter' => isset($row['radius_meter']) ? max(1, (int) $row['radius_meter']) : $def['radius_meter'],
    ];
}
