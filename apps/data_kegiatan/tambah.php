<?php
    session_start();
    if (isset($_POST['simpan_kegiatan'])) {

        include '../../config/database.php';
        
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        $id_mahasiswa = input($_POST["mahasiswa"]);
        $tanggal = input($_POST["tanggal"]);
        $waktu_awal = input($_POST["waktu_awal"]);
        $waktu_akhir = input($_POST["waktu_akhir"]);
        $kegiatan = input($_POST["kegiatan"]);
        
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Menggunakan Prepared Statement untuk keamanan maksimal dari SQL Injection
            $stmt = $kon->prepare("INSERT INTO tbl_kegiatan (id_mahasiswa, kegiatan, waktu_awal, waktu_akhir, tanggal) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $id_mahasiswa, $kegiatan, $waktu_awal, $waktu_akhir, $tanggal);
            $simpan_kegiatan = $stmt->execute();

            // validasi data
            if ($simpan_kegiatan) {
                $stmt->close();
                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=data_kegiatan&tambah=berhasil");
                exit();
            } else {
                $stmt->close();
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=data_kegiatan&tambah=gagal");
                exit();
            }
        }
    }
?>

<form action="apps/data_kegiatan/tambah.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label>Nama Mahasiswa :</label>
                <select class="form-control" id="mahasiswa" name="mahasiswa"  required>
                <?php
                    // Tampilkan data nama dan id_mahasiswa pada elemen select option
                    include '../../config/database.php';
                    $query = "SELECT id_mahasiswa, nama FROM tbl_mahasiswa";
                    $result = mysqli_query($kon, $query);
                    while ($data = mysqli_fetch_assoc($result)) {
                    echo "<option value='" . $data['id_mahasiswa'] . "'>" . $data['nama'] . "</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Kegiatan :</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control"  value="">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Awal Kegiatan :</label>
                <input type="time" name="waktu_awal" id="waktu_awal" class="form-control"  value="">
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Waktu Akhir Kegiatan:</label>
                <input type="time" name="waktu_akhir" id="waktu_akhir" class="form-control"  value="">
            </div>
        </div>
        <div class="col-sm-12">
            <div class="form-group">
                <label>Kegiatan :</label>
                <textarea name="kegiatan" id="kegiatan" class="form-control" rows="3" placeholder="Masukkan Kegiatan Harian"></textarea>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="simpan_kegiatan" id="simpan_kegiatan" class="btn btn-success" ><i class="fa fa-plus"></i> Simpan</button>
                <button type="clear" class="btn btn-warning" ><i class="fa fa-trash"></i> Hapus</button>
            </div>
        </div>
    </div>
</form>