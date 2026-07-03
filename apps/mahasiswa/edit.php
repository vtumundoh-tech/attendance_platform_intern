<?php
session_start();
    if (isset($_POST['edit_mahasiswa'])) {
        include '../../config/database.php';
        function input($data) {
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return $data;
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id_mahasiswa=input($_POST["id_mahasiswa"]);
            $nama=input($_POST["nama"]);
            $universitas=input($_POST["universitas"]);
            $jurusan=input($_POST["jurusan"]);
            $nim=input($_POST["nim"]);
            $mulai_magang=input($_POST["mulai_magang"]);
            $akhir_magang=input($_POST["akhir_magang"]);
            $status_aktif = isset($_POST["status_aktif"]) && $_POST["status_aktif"] === 'tidak_aktif' ? 'tidak_aktif' : 'aktif';
            if (strtotime($mulai_magang) > strtotime($akhir_magang)) {
                header("Location:../../index.php?page=mahasiswa&edit=gagal&reason=tanggal_tidak_valid");
                exit;
            }
            if ($status_aktif === 'aktif' && strtotime($akhir_magang) < strtotime(date('Y-m-d'))) {
                header("Location:../../index.php?page=mahasiswa&edit=gagal&reason=akhir_magang");
                exit;
            }
            mysqli_query($kon,"START TRANSACTION");
            $no_telp=input($_POST["no_telp"]);
            $alamat=input($_POST["alamat"]);
            $ekstensi_diperbolehkan	= array('png','jpg','jpeg','gif');
            $foto = $_FILES['foto']['name'];
            $x = explode('.', $foto);
            $ekstensi = strtolower(end($x));
            $ukuran	= $_FILES['foto']['size'];
            $file_tmp = $_FILES['foto']['tmp_name'];
            $pengguna = isset($_POST['pengguna']) ? $_POST['pengguna'] : '';

            $foto_saat_ini = isset($_POST['foto_saat_ini']) ? $_POST['foto_saat_ini'] : '';
            $foto_baru = $_FILES['foto_baru']['name'];
            $ekstensi_diperbolehkan	= array('png','jpg','jpeg','gif');
            $x = explode('.', $foto_baru);
            $ekstensi = strtolower(end($x));
            $ukuran	= $_FILES['foto_baru']['size'];
            $file_tmp = $_FILES['foto_baru']['tmp_name'];


            $revisi_fields = isset($_POST['revisi_fields']) ? $_POST['revisi_fields'] : [];
            $catatan_revisi = isset($_POST['catatan_revisi']) ? input($_POST['catatan_revisi']) : '';

            if (!empty($revisi_fields)) {
                $revisi_berkas_json = json_encode($revisi_fields);
                $revisi_berkas_val = "'" . mysqli_real_escape_string($kon, $revisi_berkas_json) . "'";
                $catatan_revisi_val = "'" . mysqli_real_escape_string($kon, $catatan_revisi) . "'";
            } else {
                $revisi_berkas_val = "NULL";
                $catatan_revisi_val = "NULL";
            }

        if (!empty($foto_baru)){
            if(in_array($ekstensi, $ekstensi_diperbolehkan) === true){
                move_uploaded_file($file_tmp, '../../apps/mahasiswa/foto/'.$foto_baru);
                if ($foto_saat_ini!='foto_default.png'){
                    if (file_exists('../../apps/mahasiswa/foto/'.$foto_saat_ini)) {
                        unlink('../../apps/mahasiswa/foto/'.$foto_saat_ini);
                    }
                }
                $sql="UPDATE tbl_mahasiswa SET
                    nama='$nama',
                    universitas='$universitas',
                    jurusan='$jurusan',
                    nim='$nim',
                    mulai_magang='$mulai_magang',
                    akhir_magang='$akhir_magang',
                    status_aktif='$status_aktif',
                    alamat='$alamat',
                    no_telp='$no_telp',
                    foto='$foto_baru',
                    revisi_berkas=$revisi_berkas_val,
                    catatan_revisi=$catatan_revisi_val
                    WHERE id_mahasiswa='$id_mahasiswa'";
            }
        } else {
            $sql="UPDATE tbl_mahasiswa SET
                nama='$nama',
                universitas='$universitas',
                jurusan='$jurusan',
                nim='$nim',
                mulai_magang='$mulai_magang',
                akhir_magang='$akhir_magang',
                status_aktif='$status_aktif',
                no_telp='$no_telp',
                alamat='$alamat',
                revisi_berkas=$revisi_berkas_val,
                catatan_revisi=$catatan_revisi_val
                WHERE id_mahasiswa='$id_mahasiswa'";
        }

            $edit_mahasiswa=mysqli_query($kon,$sql);
            if ($edit_mahasiswa) {
                include_once '../../config/logger.php';
                $logger = new Logger($kon);

                // Logging untuk admin (yang melakukan update)
                $user_id_admin = $_SESSION["kode_pengguna"];
                $logger->logUserActivity($user_id_admin, 'admin', 'admin_action', 'Update profil mahasiswa: ' . $nama);

                // Logging untuk mahasiswa yang diedit
                $sql_kode = "SELECT kode_mahasiswa FROM tbl_mahasiswa WHERE id_mahasiswa='$id_mahasiswa' LIMIT 1";
                $hasil_kode = mysqli_query($kon, $sql_kode);
                $row_kode = mysqli_fetch_assoc($hasil_kode);
                $user_id_mahasiswa = $row_kode ? $row_kode['kode_mahasiswa'] : '';
                if ($user_id_mahasiswa) {
                    $logger->logUserActivity($user_id_mahasiswa, 'mahasiswa', 'profil', 'Profil mahasiswa diupdate oleh admin');
                }

                mysqli_query($kon,"COMMIT");
                header("Location:../../index.php?page=mahasiswa&edit=berhasil");
                exit;
            } else {
                mysqli_query($kon,"ROLLBACK");
                header("Location:../../index.php?page=mahasiswa&edit=gagal");
                exit;
            }
        }
    }
?>

<?php 
    include '../../config/database.php';
    include_once '../../config/mahasiswa_status.php';
    $id_mahasiswa=isset($_POST["id_mahasiswa"]) ? $_POST["id_mahasiswa"] : null;
    $data = null;
    if ($id_mahasiswa !== null) {
        $sql="select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa limit 1";
        $hasil=mysqli_query($kon,$sql);
        $data = mysqli_fetch_array($hasil); 
    }
    // Query daftar universitas
    $universitas_list = [];
    $result_univ = mysqli_query($kon, "SELECT * FROM tbl_universitas ORDER BY nama_universitas ASC");
    while ($row_univ = mysqli_fetch_assoc($result_univ)) {
        $universitas_list[] = $row_univ;
    }
?>

<?php if ($data): ?>
<form action="apps/mahasiswa/edit.php" method="post" enctype="multipart/form-data">
    <div class="row">
        <!-- Kolom Kiri -->
        <div class="col-sm-6">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Nama Lengkap :</label>
                <input type="hidden" name="id_mahasiswa" class="form-control" value="<?php echo isset($data['id_mahasiswa']) ? $data['id_mahasiswa'] : ''; ?>">
                <input type="text" name="nama" class="form-control" value="<?php echo isset($data['nama']) ? $data['nama'] : ''; ?>" placeholder="Masukkan Nama Mahasiswa" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Jurusan :</label>
                <input type="text" name="jurusan" class="form-control" value="<?php echo isset($data['jurusan']) ? $data['jurusan'] : ''; ?>" placeholder="Masukan Nama Jurusan" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Mulai Magang :</label>
                <input type="date" name="mulai_magang" class="form-control" value="<?php echo isset($data['mulai_magang']) ? $data['mulai_magang'] : ''; ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>No Telp :</label>
                <input type="text" name="no_telp" class="form-control" placeholder="Masukan No Telp" value="<?php echo isset($data['no_telp']) ? $data['no_telp'] : ''; ?>" required>
            </div>
        </div>
        
        <!-- Kolom Kanan -->
        <div class="col-sm-6">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Universitas :</label>
                <select name="universitas" class="form-control" required>
                    <option value="">-- Pilih Universitas/Sekolah --</option>
                    <?php foreach ($universitas_list as $univ): ?>
                        <option value="<?php echo htmlspecialchars($univ['nama_universitas']); ?>" <?php echo (isset($data['universitas']) && $data['universitas'] === $univ['nama_universitas']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($univ['nama_universitas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Nomor Induk Mahasiswa :</label>
                <input type="text" name="nim" class="form-control" value="<?php echo isset($data['nim']) ? $data['nim'] : ''; ?>" placeholder="Masukan Nomor Induk Mahasiswa" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Akhir Magang :</label>
                <input type="date" name="akhir_magang" class="form-control" value="<?php echo isset($data['akhir_magang']) ? $data['akhir_magang'] : ''; ?>" required>
            </div>
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Status Aktif Mahasiswa (kebijakan admin) :</label>
                <select name="status_aktif" class="form-control" required>
                    <option value="aktif" <?php echo (isset($data['status_aktif']) ? $data['status_aktif'] : 'aktif') === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="tidak_aktif" <?php echo (isset($data['status_aktif']) && $data['status_aktif'] === 'tidak_aktif') ? 'selected' : ''; ?>>Tidak Aktif</option>
                </select>
                <small class="text-muted">Masa magang otomatis menonaktifkan absensi/logbook setelah tanggal akhir.</small>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-info" style="margin-bottom:12px; margin-top: 10px;">
                <?php
                $st = $data ? mahasiswa_status_tampilan_admin($data) : 'aktif';
                if ($st === 'aktif') {
                    echo '<strong>Status efektif saat ini:</strong> <span class="label label-success">Aktif</span> (dalam periode magang dan tidak dinonaktifkan admin).';
                } elseif ($st === 'tidak_aktif_admin') {
                    echo '<strong>Status efektif saat ini:</strong> <span class="label label-danger">Tidak aktif</span> (dinonaktifkan admin — login mahasiswa diblokir).';
                } else {
                    echo '<strong>Status efektif saat ini:</strong> <span class="label label-default">Tidak aktif</span> (di luar periode mulai–akhir magang — login tetap, absensi/logbook dinonaktifkan).';
                }
                ?>
            </div>
        </div>
    </div>
    <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
            <div class="panel panel-danger">
                <div class="panel-heading" style="background-color: #d9534f; color: #fff; font-weight: bold;">
                    <i class="fa fa-warning"></i> Permintaan Revisi Data / Berkas
                </div>
                <div class="panel-body">
                    <p class="text-muted">Centang data/berkas di bawah ini jika ada data yang kurang lengkap atau gambar berkas buram agar peserta magang melakukan upload ulang/revisi melalui profil mereka.</p>
                    <div class="row">
                        <?php
                        $fields_revisi_list = [
                            'nama' => 'Nama Lengkap',
                            'universitas' => 'Universitas',
                            'departemen_unitkerja' => 'Departemen / Unit Kerja',
                            'jurusan' => 'Jurusan',
                            'nim' => 'Nomor Induk Mahasiswa (NIM)',
                            'alamat' => 'Alamat',
                            'no_telp' => 'Nomor Telepon',
                            'tempat_lahir' => 'Tempat Lahir',
                            'tanggal_lahir' => 'Tanggal Lahir',
                            'agama' => 'Agama',
                            'no_hp_ortu' => 'Nomor HP Orang Tua',
                            'foto' => 'Foto Profil',
                            'scan_ktp_kk' => 'Scan KTP / KK',
                            'scan_bpjs' => 'Scan BPJS Kesehatan / KIS / Asuransi'
                        ];
                        $revisi_active = [];
                        if (!empty($data['revisi_berkas'])) {
                            $revisi_active = json_decode($data['revisi_berkas'], true);
                            if (!is_array($revisi_active)) {
                                $revisi_active = [];
                            }
                        }
                        $catatan_revisi_val = isset($data['catatan_revisi']) ? $data['catatan_revisi'] : '';

                        foreach ($fields_revisi_list as $key => $label):
                            $checked = in_array($key, $revisi_active) ? 'checked' : '';
                        ?>
                            <div class="col-sm-4" style="margin-bottom: 10px;">
                                <div class="checkbox" style="margin-top: 5px; margin-bottom: 5px;">
                                    <label style="font-weight: normal; cursor: pointer;">
                                        <input type="checkbox" name="revisi_fields[]" value="<?php echo $key; ?>" <?php echo $checked; ?>> 
                                        <?php echo $label; ?>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group" style="margin-top: 15px;">
                        <label>Catatan / Alasan Revisi :</label>
                        <textarea class="form-control" name="catatan_revisi" rows="3" placeholder="Contoh: Scan KTP buram, mohon upload ulang dengan resolusi lebih tinggi."><?php echo htmlspecialchars($catatan_revisi_val); ?></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="submit" name="edit_mahasiswa" id="Submit" class="btn btn-warning" ><i class="fa fa-edit"></i> Update</button>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>

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
        document.getElementById("preview").src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
    });
</script>