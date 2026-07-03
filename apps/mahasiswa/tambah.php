<?php
    session_start();
    //Include file koneksi, untuk koneksikan ke database
    include '../../config/database.php';

    if (isset($_POST['tambah_mahasiswa'])) {
        
        //Fungsi untuk mencegah inputan karakter yang tidak sesuai
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        //Cek apakah ada kiriman form dari method post
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            //Memulai transaksi
            mysqli_query($kon,"START TRANSACTION");

            $nama=input($_POST["nama"]);
            $universitas=input($_POST["universitas"]);
            $jurusan=input($_POST["jurusan"]);
            $nim=input($_POST["nim"]);
            $mulai_magang=input($_POST["mulai_magang"]);
            $akhir_magang=input($_POST["akhir_magang"]);
            $no_telp=input($_POST["no_telp"]);
            $alamat=input($_POST["alamat"]);
            $ekstensi_diperbolehkan	= array('png','jpg','jpeg','gif');
            $foto = $_FILES['foto']['name'];
            $x = explode('.', $foto);
            $ekstensi = strtolower(end($x));
            $ukuran	= $_FILES['foto']['size'];
            $file_tmp = $_FILES['foto']['tmp_name'];

            // Mengambil kode_pengguna terbesar yang diawali dengan 'M' dari tbl_user
            $query = mysqli_query($kon, "SELECT MAX(CAST(SUBSTRING(kode_pengguna, 2) AS UNSIGNED)) as max_code FROM tbl_user WHERE kode_pengguna LIKE 'M%'");
            $ambil = mysqli_fetch_array($query);
            $last_num = $ambil['max_code'] ? $ambil['max_code'] : 0;
            $next_num = $last_num + 1;
            // Membuat kode mahasiswa
            $kode_mahasiswa = "M" . str_pad($next_num, 3, '0', STR_PAD_LEFT);

            $sql="insert into tbl_user (kode_pengguna) values
            ('$kode_mahasiswa')";

            //Menyimpan ke tabel pengguna
            $simpan_pengguna=mysqli_query($kon,$sql);

            if (!empty($foto)){
                if(in_array($ekstensi, $ekstensi_diperbolehkan) === true){
                    // Membuat nama file baru yang unik
                    $nama_file_baru = uniqid().'.'.$ekstensi;
                    //Mengupload gambar
                    move_uploaded_file($file_tmp, 'foto/'.$nama_file_baru);
                    //Sql jika menggunakan foto
                    $sql="insert into tbl_mahasiswa (kode_mahasiswa,nama,universitas,jurusan,nim,mulai_magang,akhir_magang,alamat,no_telp,foto) values
                    ('$kode_mahasiswa','$nama','$universitas','$jurusan','$nim','$mulai_magang','$akhir_magang','$alamat','$no_telp','$nama_file_baru')";
                }
            }else {
                //Sql jika tidak menggunakan foto, maka akan memakai gambar_default.png
                $foto="foto_default.png";
                $sql="insert into tbl_mahasiswa (kode_mahasiswa,nama,universitas,jurusan,nim,mulai_magang,akhir_magang,alamat,no_telp,foto) values
                ('$kode_mahasiswa','$nama','$universitas','$jurusan','$nim','$mulai_magang','$akhir_magang','$alamat','$no_telp','$foto')";
            }

            //Menyimpan ke tabel admin
            $simpan_mahasiswa=mysqli_query($kon,$sql);
            
            if ($simpan_pengguna and $simpan_mahasiswa) {
                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=mahasiswa&add=berhasil");
            }
            else {
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=mahasiswa&add=gagal");
            }
        }
    }

    // Query daftar universitas
    $universitas_list = [];
    $result_univ = mysqli_query($kon, "SELECT * FROM tbl_universitas ORDER BY nama_universitas ASC");
    while ($row_univ = mysqli_fetch_assoc($result_univ)) {
        $universitas_list[] = $row_univ;
    }
?>

<form action="apps/mahasiswa/tambah.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <!-- Kolom Kiri -->
        <div class="col-sm-6">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Nama Lengkap :</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Peserta Magang" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Nomor Induk Mahasiswa :</label>
                <input type="text" name="nim" class="form-control" placeholder="Masukkan Nomor Induk Peserta Magang" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Mulai Magang :</label>
                <input type="date" name="mulai_magang" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Alamat :</label>
                <textarea class="form-control" name="alamat" rows="4" id="alamat" placeholder="Masukkan Alamat"></textarea>
            </div>
        </div>
        
        <!-- Kolom Kanan -->
        <div class="col-sm-6">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Universitas :</label>
                <select name="universitas" class="form-control" required>
                    <option value="">-- Pilih Universitas/Sekolah --</option>
                    <?php foreach ($universitas_list as $univ): ?>
                        <option value="<?php echo htmlspecialchars($univ['nama_universitas']); ?>">
                            <?php echo htmlspecialchars($univ['nama_universitas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" placeholder="Masukkan Nama Jurusan" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Akhir Magang :</label>
                <input type="date" name="akhir_magang" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <div id="msg"></div>
                <label>Foto :</label>
                <input type="file" name="foto" class="file">
                <div class="input-group" style="margin-top: 5px; margin-bottom: 10px;">
                    <input type="text" class="form-control" disabled placeholder="Upload Foto" id="file">
                    <div class="input-group-append">
                        <button type="button" id="pilih_foto" class="browse btn btn-info"><i class="fa fa-search"></i> Pilih</button>
                    </div>
                </div>
                <img src="source/img/size.png" id="preview" class="img-thumbnail" style="max-height: 120px; display: block; margin-top: 5px;">
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 15px;">
        <div class="col-sm-12">
            <button type="submit" name="tambah_mahasiswa" id="Submit" class="btn btn-success"><i class="fa fa-plus"></i> Daftar</button>
            <button type="reset" class="btn btn-warning"><i class="fa fa-trash"></i> Reset</button>
        </div>
    </div>
</form>

<style>
    .file {
    visibility: hidden;
    position: absolute;
    }
</style>

<script>
    $(document).off("click", "#pilih_foto").on("click", "#pilih_foto", function() {
    var file = $(this).parents().find(".file");
    file.trigger("click");
    });
    $('input[type="file"]').change(function(e) {
    var fileName = e.target.files[0].name;
    $("#file").val(fileName);

    var reader = new FileReader();
    reader.onload = function(e) {
        // get loaded data and render thumbnail.
        document.getElementById("preview").src = e.target.result;
    };
    // read the image file as a data URL.
    reader.readAsDataURL(this.files[0]);
    });
</script>
