<?php
    session_start();
    if (isset($_POST['submit_absensi'])) {
        include '../../config/database.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
        mysqli_query($kon,"START TRANSACTION");
        
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }
            
        $id_mahasiswa = $_POST['id_mahasiswa'];
        $id_absensi = $_POST['id_absensi'];
        $id_alasan = $_POST['id_alasan'];
        $status = $_POST["status"];
        $tanggal= $_POST["tanggal"];
        $waktu_masuk = $_POST["waktu_masuk"] ?? '';
        $waktu_pulang = $_POST["waktu_pulang"] ?? '';
        $status_gps = $_POST["status_gps"] ?? 'valid';
        $alasan = $_POST["alasan"] ?? '';
        
        // Validate status_gps value
        $valid_gps_status = ['valid', 'suspicious', 'fake'];
        if (!in_array($status_gps, $valid_gps_status)) {
            $status_gps = 'valid';
        }
        
        // Escape values for database
        $id_mahasiswa = mysqli_real_escape_string($kon, $id_mahasiswa);
        $id_absensi = mysqli_real_escape_string($kon, $id_absensi);
        $id_alasan = mysqli_real_escape_string($kon, $id_alasan);
        $status = mysqli_real_escape_string($kon, $status);
        $tanggal = mysqli_real_escape_string($kon, $tanggal);
        $waktu_masuk = mysqli_real_escape_string($kon, $waktu_masuk);
        $waktu_pulang = mysqli_real_escape_string($kon, $waktu_pulang);
        $status_gps = mysqli_real_escape_string($kon, $status_gps);
        $alasan = mysqli_real_escape_string($kon, $alasan);
        
            // Validate id_mahasiswa: required and must be integer
            if (empty($id_mahasiswa) || !ctype_digit(strval($id_mahasiswa))) {
                mysqli_query($kon, "ROLLBACK");
                error_log("Missing or invalid id_mahasiswa in attendance submit. POST: " . print_r($_POST, true));
                header("Location:../../index.php?page=data_absensi&mulai=gagal&reason=missing_id_mahasiswa");
                exit;
            }

        if (empty($id_absensi)) {
            $sql = "INSERT INTO tbl_absensi (id_mahasiswa, status, tanggal, waktu_masuk, waktu_pulang, status_gps)
            VALUES ('$id_mahasiswa', '$status', '$tanggal', '$waktu_masuk', '$waktu_pulang', '$status_gps')";
        } else {
            $sql = "UPDATE tbl_absensi SET 
            id_mahasiswa = '$id_mahasiswa', 
            status = '$status', 
            tanggal = '$tanggal', 
            waktu_masuk = '$waktu_masuk',
            waktu_pulang = '$waktu_pulang',
            status_gps = '$status_gps'
            WHERE id_absensi = '$id_absensi'";
        }
        $simpan_absensi = mysqli_query($kon, $sql);
        
        if (empty($id_alasan)) {
            if (!empty($alasan)) {
                $sql = "INSERT INTO tbl_alasan (id_mahasiswa,alasan,tanggal) 
                VALUES ('$id_mahasiswa', '$alasan', '$tanggal')";
                $simpan_izin=mysqli_query($kon,$sql);
            } else {
                $simpan_izin = true;
            }
        } else {
            if (!empty($alasan)) {
                $sql = "UPDATE tbl_alasan SET
                id_mahasiswa = '$id_mahasiswa', 
                alasan = '$alasan', 
                tanggal = '$tanggal' 
                WHERE id_alasan = '$id_alasan'";
                $simpan_izin=mysqli_query($kon,$sql);
            } else {
                $sql = "DELETE FROM tbl_alasan WHERE id_alasan = '$id_alasan'";
                $simpan_izin=mysqli_query($kon,$sql);
            }
        }

        if ($simpan_absensi AND $simpan_izin) {
            mysqli_query($kon,"COMMIT");
            // Debug: Log what was saved
            error_log("Updated attendance ID: $id_absensi with status_gps: $status_gps");
            header("Location:../../index.php?page=data_absensi&mulai=berhasil");
        } else {
            mysqli_query($kon,"ROLLBACK");
            $error = mysqli_error($kon);
            error_log("Failed to update attendance: $error");
            header("Location:../../index.php?page=data_absensi&mulai=gagal");
        }
        }
    }
?>

<?php
    $id_absensi = $_POST['id_absensi'] ?? '';
    // Jika dipanggil via AJAX dari tabel, id_mahasiswa dikirimkan dalam POST — pakai itu
    $id_mahasiswa = $_POST['id_mahasiswa'] ?? '';
    include '../../config/database.php';
    include '../../config/function.php';

    // Jika $id_mahasiswa belum ditentukan, inisialisasi kosong
    $id_mahasiswa = $id_mahasiswa ?? '';
    $status = "";
    $tanggal = "";
    $waktu_masuk = "";
    $waktu_pulang = "";
    $status_gps = "valid";
    $id_alasan = "";
    $alasan = "";

    if (!empty($id_absensi)) {
        $sql = EditAbsensi($id_absensi);
        $result = $kon->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id_mahasiswa = $row['id_mahasiswa'];
            $status = $row['status'];
            $tanggal = $row['tanggal'];
            $waktu_masuk = $row['waktu_masuk'] ?? '';
            $waktu_pulang = $row['waktu_pulang'] ?? '';
            $status_gps = $row['status_gps'] ?? 'valid';
            $id_alasan = $row['id_alasan'] ?? '';
            $alasan = $row['alasan'] ?? '';
        }
    } else {
        date_default_timezone_set("Asia/Jakarta");
        $tanggal= date("Y-m-d");
        $waktu_masuk = "";
        $waktu_pulang = "";
    }
?>

<form action="apps/data_absensi/absensi.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <input type="hidden" name="id_mahasiswa" value="<?php echo $id_mahasiswa; ?>">
                <input type="hidden" name="id_absensi" value="<?php echo $id_absensi; ?>">
                <input type="hidden" name="id_alasan" value="<?php echo $id_alasan; ?>">

                <label>Tanggal Absensi :</label>
                <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Masuk :</label>
                <input type="time" name="waktu_masuk" class="form-control" value="<?php echo $waktu_masuk; ?>">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Pulang :</label>
                <input type="time" name="waktu_pulang" class="form-control" value="<?php echo $waktu_pulang; ?>">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status Absensi :</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="" <?php if (empty($status)) echo 'selected'; ?>>Pilih Status</option>
                    <option value="1" <?php if ($status == 1) echo 'selected'; ?>>Hadir</option>
                    <option value="2" <?php if ($status == 2) echo 'selected'; ?>>Izin</option>
                    <option value="3" <?php if ($status == 3) echo 'selected'; ?>>Tidak Hadir</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Status GPS :</label>
                <select class="form-control" id="status_gps" name="status_gps" required>
                    <option value="valid" <?php if ($status_gps == 'valid') echo 'selected'; ?>>Valid</option>
                    <option value="suspicious" <?php if ($status_gps == 'suspicious') echo 'selected'; ?>>Mencurigakan</option>
                    <option value="fake" <?php if ($status_gps == 'fake') echo 'selected'; ?>>Fake GPS</option>
                </select>
            </div>
        </div>
        <div class="col-sm-6" id="text_alasan" style="display:none;">
            <div class="form-group">
                <label>Alasan Izin :</label>
                <input type="text" name="alasan" id="alasan" class="form-control" value="<?php echo $alasan; ?>" placeholder="Masukkan alasan izin">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="submit_absensi" id="submit_absensi" class="btn btn-success" ><i class="fa fa-clock-o"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>

<script>
// Debug: Verify form values before submit
document.getElementById('submit_absensi')?.addEventListener('click', function(e) {
    const statusGps = document.getElementById('status_gps').value;
    console.log('Status GPS akan disimpan:', statusGps);
});
</script>

<script>
$(document).ready(function() {
    // Handle status change untuk menampilkan/menyembunyikan input alasan
    $("#status").change(function() {
        if ($(this).val() == "2") {
            $("#text_alasan").show();
            $("#alasan").attr("required", true);
        } else {
            $("#text_alasan").hide();
            $("#alasan").attr("required", false);
        }
    });
    
    // Pada saat form load, cek apakah status = 2 (Izin)
    if('<?php echo $status; ?>' == "2"){
        $('#text_alasan').show();
        $("#alasan").attr("required", true);
    } else {
        $('#text_alasan').hide();
        $("#alasan").attr("required", false);
    }
});
</script>