<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION["level"]) || ($_SESSION["level"] != 'Admin' and $_SESSION["level"] != 'admin')) {
    echo "<br><div class='alert alert-danger'>Tidak memiliki Hak Akses</div>";
    exit;
}

include 'config/database.php';
$pengumuman = null;
$msg_pengumuman = null;
// Ambil pengumuman terbaru
$query_pengumuman = "SELECT * FROM tbl_pengumuman ORDER BY tanggal DESC LIMIT 1";
$result_pengumuman = mysqli_query($kon, $query_pengumuman);
$pengumuman = mysqli_fetch_assoc($result_pengumuman);
// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kirim_pengumuman'])) {
    $isi = mysqli_real_escape_string($kon, $_POST['isi']);
    $tanggal = date('Y-m-d H:i:s');

    $success_db = false;

    if ($pengumuman) {
        // Update pengumuman terbaru
        $update = "UPDATE tbl_pengumuman SET isi='$isi', tanggal='$tanggal' WHERE id=" . $pengumuman['id'];
        if (mysqli_query($kon, $update)) {
            $msg_pengumuman = "Pengumuman berhasil diperbarui.";
            $success_db = true;
        } else {
            $msg_pengumuman = "Gagal memperbarui pengumuman: " . mysqli_error($kon);
        }
    } else {
        // Insert pengumuman baru
        $insert = "INSERT INTO tbl_pengumuman (isi, tanggal) VALUES ('$isi', '$tanggal')";
        if (mysqli_query($kon, $insert)) {
            $msg_pengumuman = "Pengumuman berhasil dikirim.";
            $success_db = true;
        } else {
            $msg_pengumuman = "Gagal mengirim pengumuman: " . mysqli_error($kon);
        }
    }

    // Jika berhasil simpan ke database, kirim Notifikasi via Fonnte
    if ($success_db) {
        include_once 'config/fonnte_helper.php';

        // Format Pesan
        $pesan_fonnte = "*PENGUMUMAN PENTING*\n\n" . $_POST['isi'];

        // Eksekusi fungsi pengiriman (tanpa parameter waktu, jadi langsung kirim)
        $resp_fonnte = KirimFonnte($pesan_fonnte);
        $res_json = json_decode($resp_fonnte, true);

        // Tambahkan info kalau notifikasi dikirim
        if (isset($res_json['status']) && $res_json['status'] == true) {
            $msg_pengumuman .= " (Notifikasi WA telah masuk ke antrean Fonnte)";
        } else {
            $msg_pengumuman .= " (Gagal mengirim Notifikasi WA: Server Fonnte sibuk atau gangguan koneksi)";
        }
    }

    // Refresh data
    $result_pengumuman = mysqli_query($kon, $query_pengumuman);
    $pengumuman = mysqli_fetch_assoc($result_pengumuman);
}
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Administrator</li>
    </ol>
</div><!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Administrator

                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">

                <?php

                // Validasi untuk menampilkan pesan pemberitahuan saat user menambah admin
                if (isset($_GET['add'])) {
                    if ($_GET['add'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Administrator Telah Disimpan</div>";
                    } else if ($_GET['add'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Administrator Gagal Disimpan</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user mengedit admin
                if (isset($_GET['edit'])) {
                    if ($_GET['edit'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Administrator Telah Diupdate</div>";
                    } else if ($_GET['edit'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Administrator Gagal Diupdate</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user update admin
                if (isset($_GET['pengguna'])) {
                    if ($_GET['pengguna'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Administrator Berhasil Diatur</div>";
                    } else if ($_GET['pengguna'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Administrator Gagal</div>";
                    }
                }

                // Validasi untuk menampilkan pesan pemberitahuan saat user menghapus admin
                if (isset($_GET['hapus'])) {
                    if ($_GET['hapus'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Administrator Telah Dihapus</div>";
                    } else if ($_GET['hapus'] == 'gagal') {
                        echo "<div class='alert alert-danger'><strong>Gagal!</strong> Administrator Gagal Dihapus</div>";
                    }
                }
                ?>

                <div class="form-group">
                    <button type="button" class="btn btn-success" id="tombol_tambah"><i class="fa fa-plus"></i> Tambah</button>
                </div>
                <div class="alert alert-info">Anda dapat menambah admin baru. Anda hanya bisa mengedit data Anda sendiri dan tidak bisa menghapus akun sendiri.</div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php
                            $kode_admin_saya = mysqli_real_escape_string($kon, $_SESSION["kode_pengguna"]);
                            $sql = "select * from tbl_admin";
                            $hasil = mysqli_query($kon, $sql);
                            $no = 0;
                            // Menampilkan semua admin, tetapi edit hanya untuk akun sendiri dan hapus hanya untuk akun lain
                            while ($data = mysqli_fetch_array($hasil)):
                                $no++;
                                ?>
                                <tr>
                                    <td><?php echo $no; ?></td>
                                    <td><?php echo $data['nip']; ?></td>
                                    <td><?php echo $data['nama']; ?></td>
                                    <td><?php echo $data['email']; ?></td>
                                    <td>
                                        <?php if ($data['kode_admin'] === $_SESSION['kode_pengguna']): ?>
                                            <button kode_admin="<?php echo $data['kode_admin']; ?>"
                                                class="tombol_setting_pengguna btn btn-primary btn-circle"
                                                title="Setting Administrator" data-toggle="tooltip" data-placement="top"><i
                                                    class="fa fa-user"></i></button>
                                            <button id_admin="<?php echo $data['id_admin']; ?>"
                                                class="tombol_edit btn btn-warning btn-circle" title="Edit Data Administrator"
                                                data-toggle="tooltip" data-placement="top"><i class="fa fa-edit"></i></button>
                                        <?php endif; ?>
                                        <?php if ($data['kode_admin'] !== $_SESSION['kode_pengguna']): ?>
                                            <a href="apps/admin/hapus.php?id_admin=<?php echo $data['id_admin']; ?>&kode_admin=<?php echo $data['kode_admin']; ?>"
                                                class="btn-hapus-admin btn btn-danger btn-circle"
                                                title="Hapus Data Administrator" data-toggle="tooltip" data-placement="top"><i
                                                    class="fa fa-trash"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <!-- bagian akhir (penutup) while -->
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div><!--/.row-->

<!-- Form Pengumuman Admin -->
<div class="panel panel-info">
    <div class="panel-heading"><b>Kirim Pengumuman ke Mahasiswa</b></div>
    <div class="panel-body">
        <?php
        if (!empty($msg_pengumuman)) {
            $isSuccess = strpos(strtolower($msg_pengumuman), 'berhasil') !== false;
            $alertClass = $isSuccess ? 'alert-success' : 'alert-danger';
            echo '<div class="alert ' . $alertClass . '">' . $msg_pengumuman . '</div>';
        }
        ?>
        <form method="post"
            onsubmit="document.getElementById('btnKirimPengumuman').innerHTML = '<i class=\'fa fa-spinner fa-spin\'></i> Mengirim...'; document.getElementById('btnKirimPengumuman').classList.add('disabled');">
            <div class="form-group">
                <label for="isi">Isi Pengumuman:</label>
                <textarea name="isi" id="isi" class="form-control" rows="3"
                    required><?php echo isset($pengumuman['isi']) ? htmlspecialchars($pengumuman['isi']) : ''; ?></textarea>
            </div>
            <button type="submit" name="kirim_pengumuman" id="btnKirimPengumuman" class="btn btn-primary">Kirim/Perbarui
                Pengumuman</button>
        </form>
        <?php if ($pengumuman) { ?>
            <div class="alert alert-info mt-3" style="margin-top:10px;">
                <b>Pengumuman Terbaru:</b><br>
                <?php echo nl2br(htmlspecialchars($pengumuman['isi'])); ?><br>
                <small class="text-muted">Dikirim: <?php echo $pengumuman['tanggal']; ?></small>
            </div>
        <?php } ?>
    </div>
</div>
<!-- Akhir Form Pengumuman Admin -->

<!-- Modal -->
<div class="modal fade" id="modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h4 class="modal-title" id="judul"></h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body">
                <div id="tampil_data">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                    Close</button>
            </div>

        </div>
    </div>
</div>

<!-- Data akan di load menggunakan AJAX -->
<script>
    // Tambah admin
    $('#tombol_tambah').on('click', function () {
        $.ajax({
            url: 'apps/admin/tambah.php',
            method: 'post',
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Tambah Administrator';
            }
        });
        $('#modal').modal('show');
    });
</script>

<script>
    // Setting admin
    $('.tombol_setting_pengguna').on('click', function () {
        var kode_admin = $(this).attr("kode_admin");
        $.ajax({
            url: 'apps/admin/pengguna.php',
            method: 'post',
            data: { kode_admin: kode_admin },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Setting Pengguna';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>


<script>
    // Edit admin
    $('.tombol_edit').on('click', function () {
        var id_admin = $(this).attr("id_admin");
        $.ajax({
            url: 'apps/admin/edit.php',
            method: 'post',
            data: { id_admin: id_admin },
            success: function (data) {
                $('#tampil_data').html(data);
                document.getElementById("judul").innerHTML = 'Edit Administator';
            }
        });
        // Membuka modal
        $('#modal').modal('show');
    });
</script>

<script>
    // Hapus admin
    $('.btn-hapus-admin').on('click', function () {
        konfirmasi = confirm("Konfirmasi Sebelum Menghapus Administator?")
        if (konfirmasi) {
            return true;
        } else {
            return false;
        }
    });
</script>

<script>
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
<!-- Data akan di load menggunakan AJAX -->