<?php
/**
 * Helper functions untuk sistem absensi dengan batas keterlambatan dan ijin pulang cepat
 * File: config/absensi_helper.php
 */

/**
 * Hitung status absensi pagi berdasarkan waktu
 * @param string $waktu_absen Format HH:MM (contoh: "08:15")
 * @param string $batas_pagi Format HH:MM (contoh: "08:00")
 * @return array ['status' => 'Hadir|Terlambat|Tidak Hadir', 'kategori' => 'tepat_waktu|terlambat_10|terlambat_20|tidak_hadir']
 */
function hitungStatusAbsensiPagi($waktu_absen, $batas_pagi) {
    $time_absen = strtotime($waktu_absen);
    $time_batas = strtotime($batas_pagi);
    $time_batas_10min = $time_batas + (10 * 60);
    $time_batas_20min = $time_batas + (20 * 60);
    
    if ($time_absen <= $time_batas) {
        return [
            'status' => 'Hadir',
            'kategori' => 'tepat_waktu',
            'keterangan' => 'Absen tepat waktu'
        ];
    } elseif ($time_absen <= $time_batas_10min) {
        return [
            'status' => 'Terlambat',
            'kategori' => 'terlambat_10',
            'keterangan' => 'Terlambat ≤ 10 menit'
        ];
    } elseif ($time_absen <= $time_batas_20min) {
        return [
            'status' => 'Terlambat',
            'kategori' => 'terlambat_20',
            'keterangan' => 'Terlambat ≤ 20 menit'
        ];
    } else {
        return [
            'status' => 'Tidak Hadir',
            'kategori' => 'tidak_hadir',
            'keterangan' => 'Terlambat > 20 menit'
        ];
    }
}

/**
 * Ambil setting absensi dari database
 * @param mysqli $kon Database connection
 * @return array Setting absensi dengan key: mulai_absen, batas_pagi, jam_mulai_pulang, batas_pulang, dll
 */
function getSettingAbsensi($kon) {
    $query = mysqli_query($kon, "SELECT * FROM tbl_setting_absensi LIMIT 1");
    $setting = mysqli_fetch_assoc($query);
    
    if (!$setting) {
        // Return default jika tidak ada setting
        return [
            'mulai_absen' => '07:00:00',
            'batas_pagi' => '08:00:00',
            'jam_mulai_pulang' => '17:00:00',
            'batas_pulang' => '18:00:00'
        ];
    }
    
    // Jika kolom baru belum ada, gunakan default
    if (empty($setting['jam_mulai_pulang'])) {
        $setting['jam_mulai_pulang'] = '17:00:00';
    }
    if (empty($setting['batas_pulang'])) {
        $setting['batas_pulang'] = '18:00:00';
    }
    
    return $setting;
}

/**
 * Cek apakah mahasiswa punya ijin pulang cepat untuk hari ini
 * @param mysqli $kon Database connection
 * @param int $id_mahasiswa ID mahasiswa
 * @return bool|array False jika tidak ada, atau array detail ijin
 */
function cekIjinPulangCepat($kon, $id_mahasiswa, $expireMinutes = 10) {
    $id_mhs = (int)$id_mahasiswa;
    $query = mysqli_query($kon, "
        SELECT i.*
        FROM tbl_ijin_pulang_cepat i
        LEFT JOIN tbl_absensi a ON a.id_mahasiswa = i.id_mahasiswa AND a.tanggal = i.tanggal_ijin
        WHERE i.id_mahasiswa = $id_mhs
          AND i.tanggal_ijin = CURDATE()
          AND i.status = 'disetujui'
          AND i.waktu_ijin_dari_admin >= DATE_SUB(NOW(), INTERVAL $expireMinutes MINUTE)
          AND (a.waktu_pulang IS NULL OR a.waktu_pulang = '')
        LIMIT 1
    ");
    
    $result = mysqli_fetch_assoc($query);
    return $result ? $result : false;
}

/**
 * Cek apakah ada ijin pulang cepat yang sudah kadaluarsa hari ini
 * @param mysqli $kon Database connection
 * @param int $id_mahasiswa ID mahasiswa
 * @param int $expireMinutes Durasi berlaku izin dalam menit
 * @return bool|array False jika tidak ada expired izin, atau array detail ijin
 */
function cekIjinPulangCepatKadaluarsa($kon, $id_mahasiswa, $expireMinutes = 10) {
    $id_mhs = (int)$id_mahasiswa;
    $query = mysqli_query($kon, "
        SELECT i.*
        FROM tbl_ijin_pulang_cepat i
        LEFT JOIN tbl_absensi a ON a.id_mahasiswa = i.id_mahasiswa AND a.tanggal = i.tanggal_ijin
        WHERE i.id_mahasiswa = $id_mhs
          AND i.tanggal_ijin = CURDATE()
          AND i.status = 'disetujui'
          AND i.waktu_ijin_dari_admin < DATE_SUB(NOW(), INTERVAL $expireMinutes MINUTE)
          AND (a.waktu_pulang IS NULL OR a.waktu_pulang = '')
        LIMIT 1
    ");
    
    $result = mysqli_fetch_assoc($query);
    return $result ? $result : false;
}

/**
 * Tentukan apakah button absen pulang harus disabled
 * @param string $waktu_sekarang Format HH:MM
 * @param string $jam_mulai_pulang Format HH:MM
 * @param string $batas_pulang Format HH:MM
 * @param bool $ada_ijin Apakah mahasiswa punya ijin pulang cepat
 * @return array ['disabled' => bool, 'pesan' => string]
 */
function cekStatusButtonAbsenPulang($waktu_sekarang, $jam_mulai_pulang, $batas_pulang, $ada_ijin = false) {
    $time_sekarang = strtotime($waktu_sekarang);
    $time_mulai = strtotime($jam_mulai_pulang);
    $time_batas = strtotime($batas_pulang);
    
    if ($ada_ijin) {
        // Jika ada ijin pulang cepat, button enabled (tapi tetap harus belum absen)
        return [
            'disabled' => false,
            'pesan' => 'Anda mendapat ijin pulang lebih awal. Silakan absen pulang.',
            'ada_ijin' => true
        ];
    }
    
    if ($time_sekarang >= $time_mulai && $time_sekarang <= $time_batas) {
        return [
            'disabled' => false,
            'pesan' => 'Waktu absen pulang sudah dibuka.',
            'ada_ijin' => false
        ];
    }
    
    if ($time_sekarang < $time_mulai) {
        $selisih_menit = ceil(($time_mulai - $time_sekarang) / 60);
        return [
            'disabled' => true,
            'pesan' => "Belum waktu absen pulang. Buka dalam $selisih_menit menit. Jika urgent, minta ijin ke admin.",
            'alasan' => 'belum_waktunya',
            'ada_ijin' => false
        ];
    }
    
    if ($time_sekarang > $time_batas) {
        return [
            'disabled' => true,
            'pesan' => 'Waktu absen pulang sudah lewat.',
            'alasan' => 'sudah_lewat',
            'ada_ijin' => false
        ];
    }
}

/**
 * Cek apakah mahasiswa sudah absen hari ini
 * @param mysqli $kon Database connection
 * @param int $id_mahasiswa ID mahasiswa
 * @param string $jenis_absensi 'masuk' atau 'pulang'
 * @return bool|array False jika belum absen, atau array detail absensi
 */
function cekAbsenHariIni($kon, $id_mahasiswa, $jenis_absensi = 'masuk') {
    $id_mhs = (int)$id_mahasiswa;
    $jenis = mysqli_real_escape_string($kon, $jenis_absensi);
    
    $query = mysqli_query($kon, "
        SELECT * FROM tbl_absensi 
        WHERE id_mahasiswa = $id_mhs 
        AND DATE(tanggal) = CURDATE()
        AND jenis_absensi = '$jenis'
        LIMIT 1
    ");
    
    $result = mysqli_fetch_assoc($query);
    return $result ? $result : false;
}

/**
 * Approve ijin pulang cepat
 * @param mysqli $kon Database connection
 * @param int $id_mahasiswa ID mahasiswa
 * @param string $kode_admin Admin yang approve
 * @param string $alasan Alasan ijin (opsional)
 * @return bool True jika berhasil
 */
function approveIjinPulangCepat($kon, $id_mahasiswa, $kode_admin, $alasan = '') {
    $id_mhs = (int)$id_mahasiswa;
    $kode = mysqli_real_escape_string($kon, $kode_admin);
    $alasan_esc = mysqli_real_escape_string($kon, $alasan);
    
    $sql = "
        INSERT INTO tbl_ijin_pulang_cepat 
        (id_mahasiswa, tanggal_ijin, kode_admin_pemberi_ijin, status, alasan)
        VALUES ($id_mhs, CURDATE(), '$kode', 'disetujui', '$alasan_esc')
        ON DUPLICATE KEY UPDATE
        status = 'disetujui',
        kode_admin_pemberi_ijin = '$kode',
        alasan = '$alasan_esc',
        waktu_ijin_dari_admin = CURRENT_TIMESTAMP
    ";
    
    return mysqli_query($kon, $sql);
}

/**
 * Tolak ijin pulang cepat
 * @param mysqli $kon Database connection
 * @param int $id_mahasiswa ID mahasiswa
 * @param string $alasan Alasan penolakan
 * @return bool True jika berhasil
 */
function rejectIjinPulangCepat($kon, $id_mahasiswa, $alasan = '') {
    $id_mhs = (int)$id_mahasiswa;
    $alasan_esc = mysqli_real_escape_string($kon, $alasan);
    
    $sql = "
        UPDATE tbl_ijin_pulang_cepat 
        SET status = 'ditolak', alasan = '$alasan_esc'
        WHERE id_mahasiswa = $id_mhs 
        AND tanggal_ijin = CURDATE()
    ";
    
    return mysqli_query($kon, $sql);
}

?>
