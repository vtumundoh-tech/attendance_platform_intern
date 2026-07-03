<?php
/**
 * Status mahasiswa: kolom status_aktif = kebijakan admin;
 * masa magang (mulai–akhir) = batas otomatis fitur absensi/logbook.
 */

function mahasiswa_admin_aktif(array $m): bool
{
    return !isset($m['status_aktif']) || $m['status_aktif'] === 'aktif';
}

function mahasiswa_dalam_periode_magang(array $m, ?string $today = null): bool
{
    $today = $today ?: date('Y-m-d');
    $mulai = isset($m['mulai_magang']) ? $m['mulai_magang'] : '';
    $akhir = isset($m['akhir_magang']) ? $m['akhir_magang'] : '';
    if ($mulai === '' || $akhir === '') {
        return false;
    }
    return ($today >= $mulai && $today <= $akhir);
}

/** Boleh absensi & tambah logbook: admin aktif dan hari ini dalam periode magang */
function mahasiswa_boleh_fitur_magang_penuh(array $m, ?string $today = null): bool
{
    return mahasiswa_admin_aktif($m) && mahasiswa_dalam_periode_magang($m, $today);
}

/** Login diblokir hanya jika admin menonaktifkan (tidak_aktif) */
function mahasiswa_boleh_login(array $m): bool
{
    return mahasiswa_admin_aktif($m);
}

/** Status tampilan admin: aktif | tidak_aktif_admin | tidak_aktif_periode */
function mahasiswa_status_tampilan_admin(array $m, ?string $today = null): string
{
    if (!mahasiswa_admin_aktif($m)) {
        return 'tidak_aktif_admin';
    }
    if (!mahasiswa_dalam_periode_magang($m, $today)) {
        return 'tidak_aktif_periode';
    }
    return 'aktif';
}
