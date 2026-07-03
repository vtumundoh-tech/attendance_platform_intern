<?php
session_start();
if (!isset($_SESSION["level"]) || ($_SESSION["level"] != 'Admin' && $_SESSION["level"] != 'admin')) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Tidak Memiliki Hak Akses']);
    exit;
}

include '../../config/database.php';
include_once '../../config/absensi_helper.php';
include_once '../../config/logger.php';
$logger = new Logger($kon);

$id_mahasiswa = isset($_POST['id_mahasiswa']) ? intval($_POST['id_mahasiswa']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : 'approve';
if ($id_mahasiswa <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'ID mahasiswa tidak valid']);
    exit;
}

$admin_kode = $_SESSION['kode_pengguna'];

if ($action === 'approve') {
    $result = approveIjinPulangCepat($kon, $id_mahasiswa, $admin_kode, 'Diberikan oleh admin.');
    $message = 'Izin pulang cepat berhasil diberikan.';
} elseif ($action === 'reject') {
    $result = rejectIjinPulangCepat($kon, $id_mahasiswa, 'Ditolak oleh admin.');
    $message = 'Izin pulang cepat berhasil ditolak.';
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Aksi tidak dikenali']);
    exit;
}

if ($result) {
    if ($logger) {
        $logger->logAdminAction($admin_kode, 'approve_ijin_pulang_cepat', $id_mahasiswa, $message);
    }
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message]);
    exit;
}

if ($logger) {
    $logger->logAdminAction($admin_kode, 'approve_ijin_pulang_cepat_error', $id_mahasiswa, 'Gagal memberikan izin pulang cepat');
}
header('Content-Type: application/json');
echo json_encode(['error' => true, 'message' => 'Gagal memproses permintaan.']);
exit;
