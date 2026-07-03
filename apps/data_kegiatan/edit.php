<?php
    session_start();
    if (isset($_POST['edit_kegiatan'])) {
        include '../../config/database.php';

        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $id_mahasiswa = input($_POST["id_mahasiswa"]);
        $id_kegiatan = input($_POST["id_kegiatan"]);
        $tanggal = input($_POST["tanggal"]);
        $waktu_awal = input($_POST["waktu_awal"]);
        $waktu_akhir = input($_POST["waktu_akhir"]);
        $kegiatan = input($_POST["kegiatan"]);
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Menggunakan Prepared Statement untuk keamanan maksimal dari SQL Injection
            $stmt = $kon->prepare("UPDATE tbl_kegiatan SET kegiatan = ?, waktu_awal = ?, waktu_akhir = ?, tanggal = ? WHERE id_kegiatan = ?");
            $stmt->bind_param("ssssi", $kegiatan, $waktu_awal, $waktu_akhir, $tanggal, $id_kegiatan);
            $edit_kegiatan = $stmt->execute();

            // validasi data
            if ($edit_kegiatan) {
                $stmt->close();
                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=data_kegiatan&edit=berhasil");
                exit();
            } else {
                $stmt->close();
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=data_kegiatan&edit=gagal");
                exit();
            }
        }
    }
?>

<?php
    // Mendapatkan data variable dari AJAX
    $id_kegiatan = $_POST['id_kegiatan'];

    //Include file koneksi, untuk koneksikan ke database
    include '../../config/database.php';

    //Seleksi data berdasarkan id_absensi dari AJAX untuk menampilkan ke form absensi
    $query = "SELECT id_kegiatan, kegiatan, waktu_awal, waktu_akhir, tanggal 
        FROM tbl_kegiatan WHERE id_kegiatan = '$id_kegiatan';";
    $result = $kon->query($query);
    $row = $result->fetch_assoc();
    $waktu_awal = $row['waktu_awal'];
    $waktu_akhir = $row['waktu_akhir'];
    $tanggal = $row['tanggal'];
    $kegiatan = $row['kegiatan'];
?>

<form action="apps/data_kegiatan/edit.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <input type="hidden" name="id_mahasiswa" value="<?php echo $_POST['id_mahasiswa']; ?>">
        <input type="hidden" name="id_kegiatan" value="<?php echo $_POST['id_kegiatan']; ?>">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Kegiatan :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control"  value="<?php echo $tanggal; ?>">
            </div>
        </div>
        <div class="col-sm-3">
        <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" id="waktu_awal" class="form-control"  value="<?php echo $waktu_awal; ?>">
            </div>
        </div>
        <div class="col-sm-3">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan:</label>
                <input type="time" name="waktu_akhir" id="waktu_akhir" class="form-control"  value="<?php echo $waktu_akhir; ?>">
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Kegiatan :</label>
                <input type="text" name="kegiatan" id="kegiatan" class="form-control"  value="<?php echo $kegiatan; ?>" placeholder="Masukkan Kegiatan Harian">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_kegiatan" id="edit_kegiatan" class="btn btn-primary" ><i class="fa fa-edit"></i> Edit</button>
                <button type="clear" class="btn btn-warning" ><i class="fa fa-trash"></i> Reset</button>
            </div>
        </div>
    </div>
</form>