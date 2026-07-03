<?php
session_start();
include '../../config/database.php';
include_once '../../config/mahasiswa_status.php';
include_once '../../config/absensi_lokasi.php';
include_once '../../config/absensi_helper.php';

date_default_timezone_set("Asia/Makassar");
$id_mahasiswa = $_SESSION["id_mahasiswa"];
$cek_status = mysqli_query($kon, "SELECT mulai_magang, akhir_magang, status_aktif FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa' LIMIT 1");
if ($row_status = mysqli_fetch_assoc($cek_status)) {
    if (!mahasiswa_boleh_fitur_magang_penuh($row_status)) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Absensi tidak dapat dilakukan: masa magang belum berlangsung, sudah berakhir, atau akun dinonaktifkan admin.'
        ]);
        exit;
    }
}
$tanggal = date("Y-m-d");
$waktu = date("H:i:s");

// Blokir absensi di hari Sabtu dan Minggu
// Sabtu/Minggu: diblokir di kode (bukan dari panel admin).
$hari_ini = date('N'); // 6 = Sabtu, 7 = Minggu
if ($hari_ini == 6 || $hari_ini == 7) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Absensi tidak dapat dilakukan pada hari Sabtu atau Minggu.'
    ]);
    exit;
}

$setting = getSettingAbsensi($kon);
$mulai_absen = date("H:i:s", strtotime($setting['mulai_absen']));
$akhir_absen = date("H:i:s", strtotime($setting['akhir_absen']));
$jam_mulai_pulang = date("H:i:s", strtotime($setting['jam_mulai_pulang']));
$batas_pulang = date("H:i:s", strtotime($setting['batas_pulang']));

if (isset($_POST['foto_data']) && isset($_POST['jenis_absen'])) {
    $jenis_absen = $_POST['jenis_absen']; // 'masuk' atau 'pulang'
    $foto_data = $_POST['foto_data'];
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1; // default hadir
    $alasan = isset($_POST['alasan']) ? trim($_POST['alasan']) : '';
    $status_gps = isset($_POST['status_gps']) ? $_POST['status_gps'] : 'valid';
    $pesan_status = '';
    $dengan_ijin_cepat = false;

    if ($jenis_absen == 'masuk') {
        if ($status == 1) {
            $status_info = hitungStatusAbsensiPagi(substr($waktu, 0, 5), substr($akhir_absen, 0, 5));
            if ($status_info['kategori'] === 'tidak_hadir') {
                $status = 3;
                $pesan_status = 'Anda telah melewati batas no-show. Status disimpan sebagai Tidak Hadir.';
            } elseif ($status_info['kategori'] !== 'tepat_waktu') {
                $pesan_status = 'Anda terlambat (' . $status_info['keterangan'] . ').';
            }
        }

        if ($status == 2 && empty($alasan)) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => 'Alasan izin wajib diisi ketika status izin dipilih.'
            ]);
            exit;
        }
    }

    if ($jenis_absen == 'pulang') {
        $ijin_cepat = cekIjinPulangCepat($kon, $id_mahasiswa);
        if (!$ijin_cepat && strtotime($waktu) < strtotime($jam_mulai_pulang)) {
            $expired_ijin = cekIjinPulangCepatKadaluarsa($kon, $id_mahasiswa);
            $message = 'Belum waktunya absen pulang. Jika diperlukan, minta izin pulang cepat kepada admin.';
            if ($expired_ijin) {
                $message = 'Izin pulang cepat Anda telah kedaluwarsa. Minta admin memberikan izin lagi.';
            }
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'message' => $message
            ]);
            exit;
        }

        if (strtotime($waktu) > strtotime($batas_pulang)) {
            header('Content-Type: application/json');
            echo json_encode([
                'late' => true,
                'message' => 'Waktu absen pulang sudah lewat. Mohon hubungi admin jika ada kendala.'
            ]);
            exit;
        }

        if ($ijin_cepat && strtotime($waktu) < strtotime($jam_mulai_pulang)) {
            $dengan_ijin_cepat = true;
        }
    }

    // Validasi radius - hanya berlaku jika status = Hadir (1)
    if ($jenis_absen == 'masuk' && $status == 1) {
        $lokAbs = absensi_lokasi_ambil($kon);
        $kantorLat = $lokAbs['kantor_latitude'];
        $kantorLng = $lokAbs['kantor_longitude'];
        $radiusMeter = $lokAbs['radius_meter'];
        $userLat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
        $userLng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;

        if ($userLat && $userLng) {
            $earthRadius = 6371000;
            $latDiff = deg2rad($userLat - $kantorLat);
            $lngDiff = deg2rad($userLng - $kantorLng);
            $a = sin($latDiff / 2) * sin($latDiff / 2) +
                 cos(deg2rad($kantorLat)) * cos(deg2rad($userLat)) *
                 sin($lngDiff / 2) * sin($lngDiff / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
            $distance = $earthRadius * $c;

            if ($distance > $radiusMeter) {
                header('Content-Type: application/json');
                echo json_encode([
                    'error' => true,
                    'message' => 'Anda berada di luar jangkauan absen. Silakan mendekat ke kantor untuk melakukan absensi dengan status Hadir.'
                ]);
                exit;
            }
        }
    }

    $cek = mysqli_query($kon, "SELECT waktu_masuk, waktu_pulang FROM tbl_absensi WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'");
    $row = mysqli_fetch_assoc($cek);
    if ($jenis_absen == 'masuk' && isset($row['waktu_masuk']) && $row['waktu_masuk']) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Anda sudah absen masuk hari ini!'
        ]);
        exit;
    }
    if ($jenis_absen == 'pulang' && isset($row['waktu_pulang']) && $row['waktu_pulang']) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => 'Anda sudah absen pulang hari ini!'
        ]);
        exit;
    }

    if ($jenis_absen == 'masuk') {
        $foto_folder = '../../apps/data_absensi/foto_absen_masuk/';
    } else {
        $foto_folder = '../../apps/data_absensi/foto_absen_pulang/';
    }
    if (!file_exists($foto_folder)) {
        mkdir($foto_folder, 0777, true);
    }
    $nama_file = 'absen_' . $id_mahasiswa . '_' . $jenis_absen . '_' . $tanggal . '_' . time() . '.png';
    $path_file = $foto_folder . $nama_file;
    $foto_base64 = explode(',', $foto_data);
    $foto_binary = base64_decode(end($foto_base64));
    file_put_contents($path_file, $foto_binary);

    $cek = mysqli_query($kon, "SELECT * FROM tbl_absensi WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'");
    if (mysqli_num_rows($cek) > 0) {
        if ($jenis_absen == 'masuk') {
            $sql = "UPDATE tbl_absensi SET waktu_masuk='$waktu', foto_masuk='$nama_file', status=$status, status_gps='$status_gps', jenis_absensi='masuk' WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'";
        } else {
            $ijin_flag = $dengan_ijin_cepat ? 1 : 0;
            $sql = "UPDATE tbl_absensi SET waktu_pulang='$waktu', foto_pulang='$nama_file', status_gps='$status_gps', jenis_absensi='pulang', dengan_ijin_cepat=$ijin_flag WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'";
        }
        $result = mysqli_query($kon, $sql);
    } else {
        if ($jenis_absen == 'masuk') {
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal, waktu_masuk, foto_masuk, status_gps, jenis_absensi) VALUES ('$id_mahasiswa', $status, '$tanggal', '$waktu', '$nama_file', '$status_gps', 'masuk')";
        } else {
            $ijin_flag = $dengan_ijin_cepat ? 1 : 0;
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal, waktu_pulang, foto_pulang, status_gps, jenis_absensi, dengan_ijin_cepat) VALUES ('$id_mahasiswa', 1, '$tanggal', '$waktu', '$nama_file', '$status_gps', 'pulang', $ijin_flag)";
        }
        $result = mysqli_query($kon, $sql);
    }

    if ($jenis_absen == 'masuk' && $status == 2 && !empty($alasan)) {
        $cek_alasan = mysqli_query($kon, "SELECT * FROM tbl_alasan WHERE id_mahasiswa='$id_mahasiswa' AND tanggal='$tanggal'");
        if (mysqli_num_rows($cek_alasan) == 0) {
            mysqli_query($kon, "INSERT INTO tbl_alasan (id_mahasiswa, alasan, tanggal) VALUES ('$id_mahasiswa', '$alasan', '$tanggal')");
        }
    }

    if ($result) {
        include_once '../../config/logger.php';
        $logger = new Logger($kon);
        $user_id = $_SESSION["kode_pengguna"];
        $location_lat = isset($_POST['lat']) ? floatval($_POST['lat']) : null;
        $location_lng = isset($_POST['lng']) ? floatval($_POST['lng']) : null;
        $location_address = isset($_POST['address']) ? $_POST['address'] : '';
        $attendance_type = $jenis_absen;
        $attendance_time = date('Y-m-d H:i:s');
        $photo_filename = $nama_file;
        $status_str = ($status == 1) ? 'on_time' : (($status == 2) ? 'izin' : 'tidak_hadir');
        $logger->logAttendance(
            $user_id,
            $attendance_type,
            $attendance_time,
            $location_lat,
            $location_lng,
            $location_address,
            $photo_filename,
            $status_str,
            $alasan
        );
        $user_type = 'mahasiswa';
        $activity_type = 'absensi';
        $description = "Absen $jenis_absen pada $tanggal $waktu";
        $logger->logUserActivity($user_id, $user_type, $activity_type, $description);

        header('Content-Type: application/json');
        if (!empty($pesan_status)) {
            echo json_encode(['success' => true, 'late' => true, 'message' => $pesan_status]);
        } else {
            echo json_encode(['success' => true]);
        }
    } else {
        http_response_code(500);
        echo 'gagal';
    }
    exit;
}

header("Location:../../index.php?page=absen");
exit;
?>

<?php
    $id_mahasiswa=$_SESSION["id_mahasiswa"];
    $nama_mahasiswa=$_SESSION["nama_mahasiswa"];
    $tanggal= date("Y-m-d");
    include '../../config/database.php';
    $query = mysqli_query($kon, 
    "SELECT mulai_magang, akhir_magang FROM tbl_mahasiswa WHERE id_mahasiswa=$id_mahasiswa;");
    $periode= mysqli_fetch_array($query);
    $tanggal_masuk= $periode["mulai_magang"];
    $tanggal_keluar= $periode["akhir_magang"];
?>

<?php
    $tanggal_sekarang = date("Y-m-d");
    $query = "SELECT COUNT(*) FROM tbl_absensi WHERE tanggal = '$tanggal_sekarang' AND id_mahasiswa = '$id_mahasiswa'";
    $result = mysqli_query($kon, $query);
    $data = mysqli_fetch_assoc($result);
    if ($data['COUNT(*)'] > 0) {
        $absensi_sudah = "disabled";
    } else {
        $absensi_sudah = "";
    }  
?>

<form action="apps/pengguna/mulai_absensi.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status :</label>
                <select class="form-control" id="status" name="status"  required>
                <option>Pilih</option>
                    <option value="1">Hadir</option>
                    <option value="2">Izin</option>
                    <option value="3">Tidak Hadir</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan :</label>
                <input type="text" name="alasan" id="alasan" class="form-control"  value="" placeholder="Masukkan Alasan Kenapa Izin?">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit" id="tombol_hari" class="simpan_absensi btn btn-primary" <?php echo $absensi_sudah; ?>><i class="fa fa-clock-o"></i> Absensi</button>
            </div>
        </div>
    </div>
</form>

<script>
$(document).ready(function() {
    $("#status").change(function() {
      // Menampilkan input teks jika opsi "izin" dipilih
    if ($(this).val() == "2") {
        $("#text_alasan").show();
        $("#alasan").attr("required", true);
    } else {
        $("#text_alasan").hide();
        $("#alasan").attr("required", false);
    }
    });
});
</script>

<script>
    $(document).ready(function() {
        var hari = new Date().getDay(); 
        if (hari == 0 || hari == 6) {
    $('#tombol_hari').attr('disabled', true);
        }
    });
</script>

<script>
    $('.simpan_absensi').on('click',function(){
        konfirmasi=confirm("Konfirmasi sebelum absen?")
        if (konfirmasi){
            return true;
        }else {
            return false;
        }
    });
</script>