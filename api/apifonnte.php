<?php
include __DIR__ . '/../config/database.php';
include_once __DIR__ . '/../config/fonnte_helper.php';

$query_fonnte = mysqli_query($kon, "SELECT * FROM tbl_fonnte_setting LIMIT 1");
$row_fonnte = mysqli_fetch_array($query_fonnte);

$pesan = !empty($row_fonnte['pesan_grup']) ? $row_fonnte['pesan_grup'] : "Jangan Lupa melakukan absensi dan kegiatan magang, abaikan pesan ini di hari libur";

//konfigurasi jadwal
$jadwal_tanggal = !empty($row_fonnte['jadwal_tanggal']) ? $row_fonnte['jadwal_tanggal'] : "2026-06-02";
$jadwal_jam = !empty($row_fonnte['jadwal_jam']) ? $row_fonnte['jadwal_jam'] : "07:50:00";
$jadwal = $jadwal_tanggal . " " . $jadwal_jam;
$timezone = "+08:00"; // WITA
$schedule_epoch = strtotime($jadwal . " " . $timezone);

$response = KirimFonnte($pesan, $schedule_epoch);

echo $response;