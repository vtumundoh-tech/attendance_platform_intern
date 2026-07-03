<?php
date_default_timezone_set('Asia/Makassar');
if ($_SESSION["level"] != 'Mahasiswa' and $_SESSION["level"] != 'mahasiswa') {
    echo "<br><div class='alert alert-danger'>Tidak Memiliki Hak Akses</div>";
    exit;
}

// Mengambil data dari sessi login
include 'config/database.php';
include_once 'config/absensi_lokasi.php';
include_once 'config/absensi_helper.php';
// Integrasi logger
include_once 'config/logger.php';
$logger = new Logger($kon);
$id_mahasiswa = $_SESSION["id_mahasiswa"];
$sql = "select * from tbl_mahasiswa where id_mahasiswa=$id_mahasiswa limit 1";
$hasil = mysqli_query($kon, $sql);
$data = mysqli_fetch_array($hasil);
include_once 'config/mahasiswa_status.php';
$boleh_absen = $data && mahasiswa_boleh_fitur_magang_penuh($data);
$lok_absensi = absensi_lokasi_ambil($kon);
$nama = $data['nama'];
$universitas = $data['universitas'];
$nim = $data['nim'];
$mulai_magang = $data['mulai_magang'];
$akhir_magang = $data['akhir_magang'];
$foto = $data['foto'];

// Mengubah format tanggal ke bahasa Indonesia
setlocale(LC_TIME, 'id_ID');
$tanggal_sekarang = new DateTime();
$tanggal_masuk = date("d F Y", strtotime($mulai_magang));
$tanggal_keluar = date("d F Y", strtotime($akhir_magang));

// Query absen hari ini hanya sekali
$tanggal_hari_ini = date("Y-m-d");
$kueri_absen_hari_ini = "SELECT * FROM tbl_absensi WHERE id_mahasiswa = '$id_mahasiswa' AND tanggal = '$tanggal_hari_ini'";
$result_absen_hari_ini = mysqli_query($kon, $kueri_absen_hari_ini);
$data_absen_hari_ini = mysqli_fetch_assoc($result_absen_hari_ini);
$sudah_masuk = isset($data_absen_hari_ini['waktu_masuk']) && $data_absen_hari_ini['waktu_masuk'];
$sudah_pulang = isset($data_absen_hari_ini['waktu_pulang']) && $data_absen_hari_ini['waktu_pulang'];

$setting_absensi = getSettingAbsensi($kon);
$jam_mulai_pulang = $setting_absensi['jam_mulai_pulang'];
$batas_pulang = $setting_absensi['batas_pulang'];
$ijin_pulang = $sudah_pulang ? false : cekIjinPulangCepat($kon, $id_mahasiswa);
$ijin_pulang_expired = $sudah_pulang ? false : cekIjinPulangCepatKadaluarsa($kon, $id_mahasiswa);
$status_button_pulang = cekStatusButtonAbsenPulang(date('H:i'), $jam_mulai_pulang, $batas_pulang, $ijin_pulang !== false);
$sudah_pulang = isset($data_absen_hari_ini['waktu_pulang']) && $data_absen_hari_ini['waktu_pulang'];

// 1. DIBAWAH INI ADALAH QUERY UNTUK MENGECEK APAKAH DATA BERHASIL
// TERKIRIM ATAU TIDAK SEHINGGAH LEBIH MUDAH UNTUK TRACK DIMANA DATA YANG ERROR
// 2. Untuk query waktu dibiarkan aja dulu, mau finishing semua fitur dulu, nanti kalo sudah baru di remove dari db_magang
// kemudian di tbl_absensi lalu querynya

/* echo "<pre>";
 echo 'Tanggal hari ini (PHP): '.$tanggal_hari_ini."\n";
 echo 'ID Mahasiswa: '.$id_mahasiswa."\n";
 echo 'Query: '.$kueri_absen_hari_ini."\n";
 echo 'Result: ';
 print_r($data_absen_hari_ini);
 echo "</pre>";
 */
?>

<?php
// Mengambil data dari sessi login
include 'config/database.php';
$sql = "select * from tbl_setting_absensi limit 1";
$query = mysqli_query($kon, $sql);
$setting = mysqli_fetch_array($query);
$mulai_absen = $setting['mulai_absen'];
$akhir_absen = $setting['akhir_absen'];
?>

<div class="row">
    <ol class="breadcrumb">
        <li><a href="index.php?page=beranda">
                <em class="fa fa-home"></em>
            </a></li>
        <li class="active">Absensi</li>
    </ol>
</div>
<!--/.row-->

<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading bg-info text-white">
                DATA PRESENSI HARI INI
                <span class="pull-right clickable panel-toggle panel-button-tab-left"><em
                        class="fa fa-toggle-up"></em></span>
            </div>
            <div class="panel-body">
                <?php if ($sudah_masuk && !$sudah_pulang): ?>
                    <div class="alert alert-info">Anda telah melakukan presensi masuk. <b>Belum melakukan presensi pulang.</b>
                    </div>
                <?php elseif ($sudah_masuk && $sudah_pulang): ?>
                    <div class="alert alert-success"><b>Terima kasih sudah melakukan presensi hari ini.</b></div>
                <?php endif; ?>
                <div id="div_periode" class='alert alert-warning'
                    style="<?php echo $boleh_absen ? 'display:none;' : ''; ?>"><strong>Presensi tidak tersedia.</strong>
                    Masa magang Anda belum dimulai atau sudah berakhir. Anda tetap dapat melihat data di bawah dan
                    mengunduh riwayat presensi dari menu Riwayat.</div>

                <?php
                // Validasi untuk menampilkan pesan pemberitahuan saat user update pengaturan aplikasi                
                if (isset($_GET['mulai'])) {
                    if ($_GET['mulai'] == 'berhasil') {
                        echo "<div class='alert alert-success'><strong>Berhasil!</strong> Presensi</div>";
                    } else if ($_GET['mulai'] == 'gagal') {
                        echo "<div class='alert alert-warning'><strong>Maaf!</strong> Rentang Waktu Presensi Anda Belum Atau Lewat</div>";
                    }
                }
                ?>

                <div class="row" style="margin: 0;">
                    <div class="table-responsive" style="border: none; overflow-x: auto;">
                        <table class="table" style="min-width: 300px; margin-bottom: 0;">
                            <tbody>
                                <tr>
                                    <td>Nama Peserta Magang</td>
                                    <td width="80%">: <?php echo $nama; ?></td>
                                </tr>
                                <tr>
                                    <td>Nomor Induk Peserta Magang</td>
                                    <td width="80%">: <?php echo $nim; ?></td>
                                </tr>
                                <tr>
                                    <td>Universitas</td>
                                    <td width="80%">: <?php echo $universitas; ?></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td width="80%">:
                                        <?php
                                        include 'config/function.php';
                                        $tanggal_sekarang = date("d-m-Y");
                                        $tgl = date("d", strtotime($tanggal_sekarang));
                                        $bulan = date("m", strtotime($tanggal_sekarang));
                                        $tahun = date("Y", strtotime($tanggal_sekarang));
                                        echo $tgl . ' ' . MendapatkanBulan($bulan) . ' ' . $tahun
                                            ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Waktu Masuk</td>
                                    <td width="80%">:
                                        <?php
                                        echo $sudah_masuk ? $data_absen_hari_ini['waktu_masuk'] : 'Belum Presensi Masuk';
                                        if ($sudah_masuk && isset($data_absen_hari_ini['foto_masuk']) && $data_absen_hari_ini['foto_masuk']) {
                                            echo '<br><img src="/valendy_presensi/apps/data_absensi/foto_absen_masuk/' . $data_absen_hari_ini['foto_masuk'] . '" width="100"/>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Waktu Pulang</td>
                                    <td width="80%">:
                                        <?php
                                        echo $sudah_pulang ? $data_absen_hari_ini['waktu_pulang'] : 'Belum Presensi Pulang';
                                        if ($sudah_pulang && isset($data_absen_hari_ini['foto_pulang']) && $data_absen_hari_ini['foto_pulang']) {
                                            echo '<br><img src="/valendy_presensi/apps/data_absensi/foto_absen_pulang/' . $data_absen_hari_ini['foto_pulang'] . '" width="100"/>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <?php if ($boleh_absen): ?>
                                    <tr>
                                        <td>Status</td>
                                        <td width="80%">
                                            <select class="form-control" id="status_absen" name="status_absen"
                                                style="width:200px; display:inline-block;">
                                                <option value="1">Hadir</option>
                                                <option value="2">Izin</option>
                                                <option value="3">Tidak Hadir</option>
                                            </select>
                                        </td>
                                    </tr>
                                    <tr id="row_alasan" style="display:none;">
                                        <td>Keterangan (Alasan Izin)</td>
                                        <td width="80%">
                                            <input type="text" class="form-control" id="alasan_absen" name="alasan_absen"
                                                placeholder="Masukkan alasan jika izin"
                                                style="width:300px; display:inline-block;">
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if ($boleh_absen): ?>
                    <div id="map" style="height: 300px; border-radius: 10px; margin: 16px 0 8px 0; width:100%;">
                    </div>
                    <div id="locationInfo"
                        style="margin: 8px 0; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff;">
                        <div id="distanceInfo" style="font-weight: bold; margin-bottom: 5px;">Mengambil
                            lokasi...</div>
                        <div id="accuracyInfo" style="font-size: 12px; color: #666;">Akurasi: -</div>
                    </div>
                    <div class="row" style="margin-bottom:16px;">
                        <div class="col-xs-6" style="padding-right:5px;">
                            <button id="refreshLocation" class="btn btn-info btn-block"><i class="fa fa-refresh"></i>
                                Refresh Lokasi</button>
                        </div>
                        <div class="col-xs-6" style="padding-left:5px;">
                            <button id="accurateLocation" class="btn btn-success btn-block"><i class="fa fa-crosshairs"></i>
                                Lokasi Akurat</button>
                        </div>
                    </div>
                    <div class="row" style="margin:0;">
                        <div class="col-xs-4 text-center" style="padding:2px;">
                            <button id="btnAbsenMasuk" class="btn btn-success btn-block" <?php if ($sudah_masuk)
                                echo 'disabled'; ?>><i class="fa fa-sign-in"></i> Presensi
                                Masuk</button>
                        </div>
                        <div class="col-xs-4 text-center" style="padding:2px;">
                            <button id="btnSubmitAbsen" class="btn btn-primary btn-block" disabled><i
                                    class="fa fa-check"></i> Submit</button>
                        </div>
                        <div class="col-xs-4 text-center" style="padding:2px;">
                            <button id="btnAbsenPulang" class="btn btn-info btn-block" <?php if (!$sudah_masuk || $sudah_pulang || $status_button_pulang['disabled'])
                                echo 'disabled'; ?>><i class="fa fa-sign-out"></i> Presensi Pulang</button>
                        </div>
                    </div>
                    <div class="row" style="margin-top:10px;">
                        <div class="col-md-12">
                            <div id="infoPulang" class="alert alert-info" style="margin:0;">
                                <?php
                                if ($sudah_pulang) {
                                    echo 'Anda sudah melakukan presensi pulang hari ini.';
                                } else {
                                    echo htmlspecialchars($status_button_pulang['pesan']);
                                    if ($ijin_pulang) {
                                        echo '<br><strong>Izin pulang cepat disetujui oleh admin. Berlaku 10 menit sejak diberikan.</strong>';
                                    } elseif ($ijin_pulang_expired) {
                                        echo '<br><strong>Izin pulang cepat sudah kedaluwarsa. Minta admin memberikan izin lagi agar tombol Pulang dapat dibuka kembali.</strong>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div id="previewAbsen" class="text-center" style="margin-top:10px;"></div>
                <?php else: ?>
                    <p class="text-muted" style="margin-top: 15px;">Form absensi disembunyikan karena masa magang tidak
                        aktif
                        untuk presensi.</p>
                <?php endif; ?>
                <?php if ($boleh_absen): ?>
                    <p> 1. PERHATIAN!!! PENGAMBILAN PRESENSI TETAP BERLAKU MESKIPUN TIDAK MASUK/IJIN CTH: DEMAM, KE KAMPUS,
                        DSB. DAN WAJIB UNTUK MENGAMBIL FOTO JUGA </p>
                    <p> 2. HARAP MELAKUKAN PRESENSI SEBELUM PUKUL 08:15
                    <p> 3. BAGI YANG STATUS KEHADIRANNYA 'IZIN', TETAP DIWAJIBKAN MENGAMBIL FOTO SEBANYAK 2 KALI UNTUK PRESENSI
                        MASUK DAN PULANG. KEDUA FOTO DAPAT DIAMBIL SEKALIGUS PADA PAGI HARI, SILAKAN HUBUNGI ADMIN UNTUK MEMBUKA AKSES PRESENSI PULANG</p>
                    <div class="alert alert-info" style="margin-top: 15px;">
                        <strong><i class="fa fa-info-circle"></i> Tips Lokasi Akurat:</strong>
                        <ul style="margin-bottom: 0; padding-left: 20px;">
                            <li>Pastikan GPS aktif di perangkat Anda</li>
                            <li>Berada di area terbuka untuk sinyal GPS yang lebih baik</li>
                            <li>Gunakan tombol "Lokasi Akurat" untuk hasil terbaik</li>
                            <li>Jika akurasi masih kurang, coba refresh beberapa kali</li>
                            <li>Pastikan browser mengizinkan akses lokasi</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

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
                        <!-- Data akan di load menggunakan AJAX -->
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fa fa-times"></i>
                        Close</button>
                </div>

            </div>
        </div>
    </div>
    <!-- Model AJAX -->

    <!-- Modal Kamera -->
    <div class="modal fade" id="modalKamera" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKameraLabel">Ambil Foto Presensi</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <video id="video" width="320" height="240" autoplay></video>
                    <canvas id="canvas" width="320" height="240" style="display:none;"></canvas>
                    <br>
                    <button id="ambilFoto" class="btn btn-warning">Ambil Foto</button>
                    <form id="formAbsenFoto" method="post" enctype="multipart/form-data" style="display:none;">
                        <input type="hidden" name="jenis_absen" id="jenis_absen" value="">
                        <input type="hidden" name="foto_data" id="foto_data" value="">
                        <button type="submit" class="btn btn-success">Kirim Presensi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Notifikasi Absen Lewat Waktu -->
    <div class="modal fade" id="modalAbsenTerlambat" tabindex="-1" role="dialog"
        aria-labelledby="modalAbsenTerlambatLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAbsenTerlambatLabel">Pemberitahuan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="isiModalAbsenTerlambat">
                    <!-- Pesan akan diisi via JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Setting pengguna
        $('.mulai_absensi').on('click', function () {
            var id_mahasiswa = $(this).attr("id_mahasiswa");
            $.ajax({
                url: 'apps/pengguna/mulai_absensi.php',
                method: 'post',
                data: { id_mahasiswa: id_mahasiswa },
                success: function (data) {
                    $('#tampil_data').html(data);
                    document.getElementById("judul").innerHTML = 'Mulai Absensi';
                }
            });
            // Membuka modal
            $('#modal').modal('show');
        });
    </script>

    <script>
        $(document).ready(function () {
            var bolehAbsenPenuh = <?php echo $boleh_absen ? 'true' : 'false'; ?>;
            if (!bolehAbsenPenuh) {
                $("#div_periode").show();
            }
        });
    </script>

    <?php if ($boleh_absen): ?>
        <script>
            let locationObtained = false; // Flag untuk menandai apakah lokasi sudah didapat
            // Variabel penampung data absen sementara
            let absenData = {
                foto: '',
                status: '',
                keterangan: ''
            };

            let absenLokasi = { lat: null, lng: null, address: '', status_gps: 'valid' };

            // Ambil foto absen masuk
            $('#btnAbsenMasuk').on('click', function () {
                console.log('[DEBUG] Klik Presensi Masuk, locationObtained:', locationObtained);
                if (!locationObtained) {
                    alert('Lokasi belum terbaca. Silakan refresh lokasi terlebih dahulu.');
                    return;
                }

                absenData.foto = '';
                absenData.status = '';
                absenData.keterangan = '';
                $('#status_absen').val('1');
                $('#alasan_absen').val('');
                $('#row_alasan').hide();
                $('#status_absen').prop('disabled', false); // Aktifkan status
                $('#alasan_absen').prop('disabled', false); // Aktifkan alasan
                $('#previewAbsen').html('');
                $('#btnSubmitAbsen').prop('disabled', true);
                // Re-validasi radius setelah reset status (karena default status = Hadir)
                if (absenLokasi.lat && absenLokasi.lng) {
                    tampilkanPeta(absenLokasi.lat, absenLokasi.lng);
                }
                // Buka modal kamera
                $('#jenis_absen').val('masuk');
                $('#modalKamera').modal('show');
                startCamera();
            });

            // Data status dan keterangan absen masuk hari ini dari PHP ke JS
            var statusMasukHariIni = '';
            var keteranganMasukHariIni = '';
            <?php
            // status: 1=Hadir, 2=Izin, 3=Tidak Hadir
            if (isset($data_absen_hari_ini['status'])) {
                echo "statusMasukHariIni = '" . $data_absen_hari_ini['status'] . "';\n";
            }
            // Ambil keterangan dari tbl_alasan jika status izin
            $keterangan_izin = '';
            if (isset($data_absen_hari_ini['status']) && $data_absen_hari_ini['status'] == 2) {
                $id_mhs = $id_mahasiswa;
                $tgl = $tanggal_hari_ini;
                $q_izin = mysqli_query($kon, "SELECT alasan FROM tbl_alasan WHERE id_mahasiswa='$id_mhs' AND tanggal='$tgl' LIMIT 1");
                if ($row_izin = mysqli_fetch_assoc($q_izin)) {
                    $keterangan_izin = $row_izin['alasan'];
                }
            }
            echo "keteranganMasukHariIni = '" . addslashes($keterangan_izin) . "';\n";
            ?>

            // Ambil foto absen pulang
            $('#btnAbsenPulang').on('click', function () {
                console.log('[DEBUG] Klik Presensi Pulang, locationObtained:', locationObtained);
                if (!locationObtained) {
                    alert('Lokasi belum terbaca. Silakan refresh lokasi terlebih dahulu.');
                    return;
                }

                absenData.foto = '';
                // Set status dan keterangan dari absen masuk hari ini
                absenData.status = statusMasukHariIni;
                absenData.keterangan = keteranganMasukHariIni;
                $('#status_absen').val('1');
                $('#alasan_absen').val('');
                $('#row_alasan').hide();
                $('#status_absen').prop('disabled', true); // Nonaktifkan status
                $('#alasan_absen').prop('disabled', true); // Nonaktifkan alasan
                $('#previewAbsen').html('');
                $('#btnSubmitAbsen').prop('disabled', true);
                // Buka modal kamera
                $('#jenis_absen').val('pulang');
                $('#modalKamera').modal('show');
                startCamera();
            });

            // Setelah ambil foto, simpan ke absenData dan tampilkan preview
            let ambilFotoBtn = document.getElementById('ambilFoto');
            let formAbsenFoto = document.getElementById('formAbsenFoto');
            let fotoDataInput = document.getElementById('foto_data');
            let jenisAbsenInput = document.getElementById('jenis_absen');

            function startCamera() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ video: true }).then(function (stream) {
                        video.srcObject = stream;
                        video.play();
                    });
                }
            }

            ambilFotoBtn.onclick = function () {
                canvas.style.display = 'block';
                canvas.getContext('2d').drawImage(video, 0, 0, 320, 240);
                let dataURL = canvas.toDataURL('image/png');
                absenData.foto = dataURL;
                fotoDataInput.value = dataURL;
                formAbsenFoto.style.display = 'none'; // Hide form lama
                // Stop kamera
                video.srcObject.getTracks().forEach(track => track.stop());
                video.style.display = 'none';
                ambilFotoBtn.style.display = 'none';
                // Tampilkan preview dan cek validasi
                updatePreviewAbsen();
                checkSubmitReady();
                $('#modalKamera').modal('hide');
            };

            // Status dan alasan
            $('#status_absen').on('change', function () {
                absenData.status = $(this).val();
                if ($(this).val() == '2') {
                    $('#row_alasan').show();
                } else {
                    $('#row_alasan').hide();
                    absenData.keterangan = '';
                    $('#alasan_absen').val('');
                }
                updatePreviewAbsen();
                checkSubmitReady();
                // Re-validasi radius berdasarkan status baru
                if (locationObtained && absenLokasi.lat && absenLokasi.lng) {
                    tampilkanPeta(absenLokasi.lat, absenLokasi.lng);
                }
            });
            $('#alasan_absen').on('input', function () {
                absenData.keterangan = $(this).val();
                updatePreviewAbsen();
                checkSubmitReady();
            });

            function updatePreviewAbsen() {
                let html = '';
                if (absenData.foto) html += '<img src="' + absenData.foto + '" width="120" class="img-thumbnail"/><br>';
                // Tampilkan status/keterangan sesuai absen masuk jika absen pulang
                let jenis_absen = $('#jenis_absen').val();
                if (jenis_absen === 'pulang') {
                    if (absenData.status) {
                        let statusText = '';
                        if (absenData.status == '1') statusText = 'Hadir';
                        else if (absenData.status == '2') statusText = 'Izin';
                        else if (absenData.status == '3') statusText = 'Tidak Hadir';
                        html += '<b>Status:</b> ' + statusText + '<br>';
                    }
                    if (absenData.status == '2' && absenData.keterangan) {
                        html += '<b>Keterangan:</b> ' + absenData.keterangan + '<br>';
                    }
                } else {
                    if (absenData.status) {
                        let statusText = $('#status_absen option:selected').text();
                        html += '<b>Status:</b> ' + statusText + '<br>';
                    }
                    if (absenData.status == '2' && absenData.keterangan) {
                        html += '<b>Keterangan:</b> ' + absenData.keterangan + '<br>';
                    }
                }
                $('#previewAbsen').html(html);
            }

            function checkSubmitReady() {
                let ready = absenData.foto && absenData.status;
                if (absenData.status == '2') ready = ready && absenData.keterangan;
                $('#btnSubmitAbsen').prop('disabled', !ready);
            }

            // Submit absen
            $('#btnSubmitAbsen').on('click', function (e) {
                e.preventDefault();
                let jenis_absen = $('#jenis_absen').val();
                if (jenis_absen === 'masuk') {
                    if (!absenData.foto || !absenData.status || (absenData.status == '2' && !absenData.keterangan)) return;
                } else {
                    if (!absenData.foto) return;
                }
                let dataAjax = {
                    foto_data: absenData.foto,
                    jenis_absen: jenis_absen,
                    lat: absenLokasi.lat,
                    lng: absenLokasi.lng,
                    address: absenLokasi.address || '',
                    status_gps: absenLokasi.status_gps || 'valid'
                };
                if (jenis_absen === 'masuk') {
                    dataAjax.status = absenData.status;
                    dataAjax.alasan = absenData.keterangan;
                }
                $.ajax({
                    url: 'apps/pengguna/mulai_absensi.php',
                    type: 'POST',
                    data: dataAjax,
                    success: function (response) {
                        let res = null;
                        try {
                            res = typeof response === 'string' ? JSON.parse(response) : response;
                        } catch (e) { }
                        if (res && (res.late || res.error)) {
                            $('#isiModalAbsenTerlambat').text(res.message);
                            $('#modalAbsenTerlambat').modal('show');
                            if (res.success) {
                                $('#modalAbsenTerlambat').off('hidden.bs.modal').on('hidden.bs.modal', function () {
                                    location.reload();
                                });
                            }
                            return;
                        }
                        if (jenis_absen === 'pulang') {
                            $('#btnAbsenPulang').prop('disabled', true);
                        }
                        location.reload();
                    },
                    error: function () {
                        alert('Gagal mengirim presensi!');
                    }
                });
            });
        </script>

        <script>
            // Tampilkan input alasan jika status izin dipilih
            $(document).ready(function () {
                $('#status_absen').change(function () {
                    if ($(this).val() == '2') {
                        $('#row_alasan').show();
                        $('#alasan_absen').attr('required', true);
                    } else {
                        $('#row_alasan').hide();
                        $('#alasan_absen').attr('required', false);
                    }
                    // Re-validasi radius berdasarkan status baru
                    if (locationObtained && absenLokasi.lat && absenLokasi.lng) {
                        tampilkanPeta(absenLokasi.lat, absenLokasi.lng);
                    }
                });
            });
        </script>

        <script>
            // Sabtu/Minggu: pembatasan di kode frontend (sama dengan backend mulai_absensi.php), bukan dari pengaturan admin.
            $(document).ready(function () {
                var hari = new Date().getDay(); // 0 = Minggu, 6 = Sabtu
                if (hari == 0 || hari == 6) {
                    $('#btnAbsenMasuk').prop('disabled', true);
                    $('#btnAbsenPulang').prop('disabled', true);
                    $('#btnSubmitAbsen').prop('disabled', true);
                    // Tampilkan pesan peringatan di atas tombol
                    if ($('#peringatanWeekend').length == 0) {
                        $("<div id='peringatanWeekend' class='alert alert-warning text-center'><b>Presensi tidak dapat dilakukan pada hari Sabtu atau Minggu.</b></div>").insertBefore('.row table');
                    }
                }
            });
        </script>

        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

        <script>
            // Titik kantor & radius dari Pengaturan Absensi (admin). Backend memakai nilai yang sama di mulai_absensi.php.
            const kantorLat = <?php echo json_encode((float) $lok_absensi['kantor_latitude']); ?>;
            const kantorLng = <?php echo json_encode((float) $lok_absensi['kantor_longitude']); ?>;
            const radiusMeter = <?php echo (int) $lok_absensi['radius_meter']; ?>;
            let map, userMarker, kantorMarker, kantorCircle;
            let accuracyCircle = null; // Tambahan: simpan circle akurasi

            function tampilkanPeta(userLat, userLng, accuracy = null) {
                console.log('[DEBUG] Lokasi didapat:', userLat, userLng, accuracy);
                locationObtained = true;
                // Simpan lokasi ke absenLokasi
                absenLokasi.lat = userLat;
                absenLokasi.lng = userLng;
                if (!map) {
                    map = L.map('map').setView([userLat, userLng], 16);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '© OpenStreetMap contributors'
                    }).addTo(map);
                    kantorMarker = L.marker([kantorLat, kantorLng]).addTo(map).bindPopup('Kantor');
                    kantorCircle = L.circle([kantorLat, kantorLng], { radius: radiusMeter, color: 'red', fillOpacity: 0.1 }).addTo(map);
                } else {
                    map.setView([userLat, userLng], 16);
                }
                if (userMarker) {
                    map.removeLayer(userMarker);
                }
                if (accuracyCircle) {
                    map.removeLayer(accuracyCircle);
                    accuracyCircle = null;
                }

                // Tampilkan marker user dengan informasi akurasi
                let popupContent = 'Lokasi Anda';
                if (accuracy) {
                    popupContent += `<br><small>Akurasi: ${accuracy.toFixed(0)} meter</small>`;
                }
                userMarker = L.marker([userLat, userLng]).addTo(map).bindPopup(popupContent).openPopup();

                // Tambahkan circle akurasi jika tersedia
                if (accuracy && accuracy > 0) {
                    accuracyCircle = L.circle([userLat, userLng], {
                        radius: accuracy,
                        color: 'blue',
                        fillColor: '#cacaca',
                        fillOpacity: 0.2,
                        weight: 1
                    }).addTo(map);
                }

                setTimeout(function () { map.invalidateSize(); }, 350);

                // Validasi radius - hanya berlaku jika status = Hadir (1)
                var btnAbsen = document.getElementById('btnAbsenMasuk');
                var userLatLng = L.latLng(userLat, userLng);
                var kantorLatLng = L.latLng(kantorLat, kantorLng);
                var distance = userLatLng.distanceTo(kantorLatLng);

                // Ambil status absen saat ini
                var statusAbsen = $('#status_absen').val() || '1';

                // Tampilkan informasi jarak
                const distanceInfo = document.getElementById('distanceInfo');
                if (distanceInfo) {
                    distanceInfo.innerHTML = `Jarak ke kantor: ${distance.toFixed(0)} meter`;
                    if (distance <= radiusMeter) {
                        distanceInfo.style.color = 'green';
                        distanceInfo.innerHTML += ' ✓ (Dalam jangkauan)';
                    } else {
                        distanceInfo.style.color = 'red';
                        distanceInfo.innerHTML += ' ✗ (Di luar jangkauan)';
                    }
                }

                // Set flag lokasi sudah didapat
                locationObtained = true;

                if (btnAbsen) {
                    // Jika status Izin (2) atau Tidak Hadir (3), abaikan validasi radius
                    if (statusAbsen == '2' || statusAbsen == '3') {
                        btnAbsen.disabled = false;
                        if (distanceInfo) {
                            distanceInfo.innerHTML += ' <span style="color: blue;">(Validasi radius dinonaktifkan untuk Izin/Tidak Hadir silakan lakukan presensi)</span>';
                        }
                    } else {
                        // Jika status Hadir (1), validasi radius tetap berlaku
                        if (distance <= radiusMeter) {
                            btnAbsen.disabled = false;
                        } else {
                            btnAbsen.disabled = true;
                            console.log('Anda diluar jangkauan presensi, silakan mendekat ke kantor untuk melakukan presensi.');
                        }
                    }
                }
            }

            function cekStatusGPS() {
                return new Promise((resolve) => {
                    if (!navigator.geolocation) {
                        resolve({ available: false, message: 'Browser tidak mendukung geolocation' });
                        return;
                    }

                    // Cek permission status
                    if (navigator.permissions && navigator.permissions.query) {
                        navigator.permissions.query({ name: 'geolocation' }).then(function (result) {
                            if (result.state === 'denied') {
                                resolve({ available: false, message: 'Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser.' });
                            } else if (result.state === 'prompt') {
                                resolve({ available: true, message: 'Silakan izinkan akses lokasi saat diminta.' });
                            } else {
                                resolve({ available: true, message: 'GPS tersedia' });
                            }
                        });
                    } else {
                        // Fallback untuk browser yang tidak mendukung permissions API
                        resolve({ available: true, message: 'GPS tersedia' });
                    }
                });
            }

            function ambilLokasiDanTampilkan() {
                // Tampilkan loading state
                const refreshBtn = document.getElementById('refreshLocation');
                const originalText = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengambil Lokasi...';
                refreshBtn.disabled = true;

                // Reset info panel
                const locationInfo = document.getElementById('locationInfo');
                if (locationInfo) {
                    locationInfo.style.borderLeftColor = '#007bff';
                    locationInfo.style.backgroundColor = '#f8f9fa';
                }

                // Reset flag lokasi
                locationObtained = false;

                // Cek status GPS terlebih dahulu
                cekStatusGPS().then(function (gpsStatus) {
                    if (!gpsStatus.available) {
                        // Tampilkan peringatan GPS
                        document.getElementById('map').innerHTML = `<div style='color:red; padding:20px; text-align:center;'>
                <i class='fa fa-exclamation-triangle'></i><br>
                <strong>GPS Tidak Tersedia:</strong><br>
                ${gpsStatus.message}<br><br>
                <div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>
                    <strong>Cara mengaktifkan GPS:</strong><br>
                    • Android: Settings > Location > Turn On<br>
                    • iOS: Settings > Privacy > Location Services > Turn On<br>
                    • Browser: Izinkan akses lokasi saat diminta
                </div>
                <button onclick='ambilLokasiDanTampilkan()' class='btn btn-primary'>Coba Lagi</button>
            </div>`;

                        refreshBtn.innerHTML = originalText;
                        refreshBtn.disabled = false;
                        return;
                    }

                    if (navigator.geolocation) {
                        // Opsi geolocation yang lebih cepat
                        const options = {
                            enableHighAccuracy: false,  // Gunakan akurasi rendah dulu untuk kecepatan
                            timeout: 10000,            // Timeout 10 detik (lebih cepat)
                            maximumAge: 60000          // Gunakan cache 1 menit untuk kecepatan
                        };

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const accuracy = position.coords.accuracy;
                                // Analisa Fake GPS
                                let status_gps = 'valid';
                                let debug_reason = '';

                                if (position.mocked === true) {
                                    status_gps = 'fake';
                                    debug_reason = 'OS Mock Location Terdeteksi';
                                } else if ((accuracy === 150 || accuracy === 10) && position.coords.altitude === null && position.coords.speed === null) {
                                    // Ciri khas emulator atau Ekstensi Fake GPS Chrome (seperti buatan sarah82529)
                                    status_gps = 'fake';
                                    debug_reason = 'Terdeteksi Emulator / Ekstensi Fake GPS Chrome';
                                } else if (accuracy <= 4) {
                                    status_gps = 'suspicious';
                                    debug_reason = 'Akurasi terlalu sempurna tidak wajar (<5m)';
                                } else if (position.coords.altitude === 0 && position.coords.speed === 0) {
                                    status_gps = 'suspicious';
                                    debug_reason = 'Terdeteksi Aplikasi Fake GPS (Altitude & Speed 0 mutlak)';
                                } else if (position.coords.altitude === 0 && position.coords.heading === 0) {
                                    status_gps = 'suspicious';
                                    debug_reason = 'Terdeteksi Aplikasi Fake GPS (Altitude & Heading 0 mutlak)';
                                } else if (position.coords.altitude !== null && position.coords.altitude === 0) {
                                    status_gps = 'suspicious';
                                    debug_reason = 'Ketinggian (Altitude) 0 mutlak tidak wajar';
                                } else if (accuracy > 1000) {
                                    status_gps = 'suspicious';
                                    debug_reason = 'Jangkauan lokasi sangat melenceng (>1Km)';
                                }

                                absenLokasi.status_gps = status_gps;

                                // Tampilkan informasi akurasi
                                const accuracyText = accuracy <= 10 ? 'Sangat Akurat' :
                                    accuracy <= 50 ? 'Akurat' :
                                        accuracy <= 100 ? 'Cukup Akurat' : 'Kurang Akurat';

                                // Update button dengan informasi akurasi
                                refreshBtn.innerHTML = `<i class="fa fa-refresh"></i> Refresh Lokasi`;
                                refreshBtn.disabled = false;

                                // Update informasi akurasi di HTML
                                const accuracyInfo = document.getElementById('accuracyInfo');
                                if (accuracyInfo) {
                                    let additionalText = '';
                                    if (status_gps === 'suspicious') {
                                        additionalText = ` <br><span style="color:#fd7e14; font-size:13px;"><i class="fa fa-warning"></i> <b>Status GPS: Mencurigakan</b> (${debug_reason})</span>`;
                                    } else if (status_gps === 'fake') {
                                        additionalText = ` <br><span style="color:#dc3545; font-size:13px;"><i class="fa fa-ban"></i> <b>Status GPS: Fake GPS!</b> (${debug_reason})</span>`;
                                    } else {
                                        additionalText = ` <br><span style="color:#28a745; font-size:13px;"><i class="fa fa-check-circle"></i> <b>Status GPS: Valid (Aman)</b></span>`;
                                    }

                                    accuracyInfo.innerHTML = `Akurasi: ${accuracy.toFixed(0)} meter (${accuracyText})${additionalText}`;
                                    if (status_gps === 'fake') {
                                        accuracyInfo.style.color = '#dc3545';
                                    } else if (status_gps === 'suspicious') {
                                        accuracyInfo.style.color = '#fd7e14';
                                    } else if (accuracy <= 10) {
                                        accuracyInfo.style.color = '#28a745';
                                    } else if (accuracy <= 50) {
                                        accuracyInfo.style.color = '#17a2b8';
                                    } else if (accuracy <= 100) {
                                        accuracyInfo.style.color = '#ffc107';
                                    } else {
                                        accuracyInfo.style.color = '#dc3545';
                                    }
                                }

                                if (status_gps === 'fake') {
                                    document.getElementById('map').innerHTML = `<div style='color:red; padding:20px; text-align:center;'>
                            <i class='fa fa-ban fa-3x'></i><br>
                            <strong>FAKE GPS TERDETEKSI</strong><br>
                            ${debug_reason}. Matikan alat pemalsu lokasi Anda untuk dapat melakukan presensi.<br><br>
                            <button onclick='ambilLokasiDanTampilkan()' class='btn btn-primary'>Coba Lagi</button>
                        </div>`;
                                    return;
                                }

                                // Tampilkan peta dengan lokasi baru
                                tampilkanPeta(position.coords.latitude, position.coords.longitude, accuracy);

                                // Tampilkan notifikasi akurasi jika kurang akurat
                                if (accuracy > 100) {
                                    // Ganti alert dengan notifikasi yang lebih user-friendly
                                    const locationInfo = document.getElementById('locationInfo');
                                    if (locationInfo) {
                                        locationInfo.style.borderLeftColor = '#dc3545';
                                        locationInfo.style.backgroundColor = '#f8d7da';
                                    }
                                } else {
                                    const locationInfo = document.getElementById('locationInfo');
                                    if (locationInfo) {
                                        locationInfo.style.borderLeftColor = '#28a745';
                                        locationInfo.style.backgroundColor = '#d4edda';
                                    }
                                }
                            },
                            function (error) {
                                console.log('[DEBUG] Gagal mendapatkan lokasi:', error);
                                let errorMessage = '';
                                switch (error.code) {
                                    case error.PERMISSION_DENIED:
                                        errorMessage = 'Akses lokasi ditolak. Silakan izinkan akses lokasi di pengaturan browser Anda.';
                                        break;
                                    case error.POSITION_UNAVAILABLE:
                                        errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif dan berada di area terbuka.';
                                        break;
                                    case error.TIMEOUT:
                                        errorMessage = 'Waktu tunggu habis. Silakan coba lagi atau pastikan koneksi internet stabil.';
                                        break;
                                    default:
                                        errorMessage = 'Terjadi kesalahan saat mengambil lokasi: ' + error.message;
                                }

                                document.getElementById('map').innerHTML = `<div style='color:red; padding:20px; text-align:center;'>
                        <i class='fa fa-exclamation-triangle'></i><br>
                        <strong>Gagal mendapatkan lokasi:</strong><br>
                        ${errorMessage}<br><br>
                        <div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>
                            <strong>Solusi:</strong><br>
                            • Pastikan GPS aktif di perangkat<br>
                            • Berada di area terbuka<br>
                            • Izinkan akses lokasi di browser
                        </div>
                        <button onclick='ambilLokasiDanTampilkan()' class='btn btn-primary'>Coba Lagi</button>
                    </div>`;

                                // Reset button
                                refreshBtn.innerHTML = originalText;
                                refreshBtn.disabled = false;
                            },
                            options
                        );
                    } else {
                        document.getElementById('map').innerHTML = `<div style='color:red; padding:20px; text-align:center;'>
                <i class='fa fa-exclamation-triangle'></i><br>
                <strong>Browser tidak mendukung geolocation.</strong><br>
                Silakan gunakan browser yang lebih baru atau aktifkan fitur lokasi.
            </div>`;
                        refreshBtn.innerHTML = originalText;
                        refreshBtn.disabled = false;
                    }
                });
            }

            // Fungsi untuk mencoba mendapatkan lokasi dengan akurasi yang lebih baik
            function cobaLokasiAkurat() {
                return new Promise((resolve, reject) => {
                    let attempts = 0;
                    const maxAttempts = 3; // Kurangi jumlah attempts untuk kecepatan
                    let bestPosition = null;
                    let bestAccuracy = Infinity;

                    function tryGetLocation() {
                        attempts++;
                        const options = {
                            enableHighAccuracy: true,
                            timeout: 15000, // Kurangi timeout untuk kecepatan
                            maximumAge: 0
                        };

                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const accuracy = position.coords.accuracy;

                                // Jika akurasi lebih baik dari sebelumnya, simpan
                                if (accuracy < bestAccuracy) {
                                    bestPosition = position;
                                    bestAccuracy = accuracy;
                                }

                                // Jika akurasi sudah cukup baik atau sudah mencoba maksimal, selesai
                                if (accuracy <= 20 || attempts >= maxAttempts) { // Kurangi threshold akurasi
                                    resolve(bestPosition);
                                } else {
                                    // Coba lagi setelah delay yang lebih pendek
                                    setTimeout(tryGetLocation, 2000);
                                }
                            },
                            function (error) {
                                if (attempts >= maxAttempts) {
                                    reject(error);
                                } else {
                                    // Coba lagi dengan akurasi rendah
                                    setTimeout(() => {
                                        navigator.geolocation.getCurrentPosition(
                                            function (position) {
                                                resolve(position);
                                            },
                                            function (err) {
                                                reject(err);
                                            },
                                            { enableHighAccuracy: false, timeout: 10000 }
                                        );
                                    }, 1000);
                                }
                            },
                            options
                        );
                    }

                    tryGetLocation();
                });
            }

            // Fungsi untuk memantau perubahan lokasi secara real-time (opsional)
            function mulaiMonitoringLokasi() {
                if (navigator.geolocation) {
                    const options = {
                        enableHighAccuracy: true,
                        timeout: 30000,
                        maximumAge: 0
                    };

                    navigator.geolocation.watchPosition(
                        function (position) {
                            // Update lokasi tanpa refresh manual
                            tampilkanPeta(position.coords.latitude, position.coords.longitude, position.coords.accuracy);
                        },
                        function (error) {
                            console.log('Error monitoring lokasi:', error);
                        },
                        options
                    );
                }
            }

            document.addEventListener("DOMContentLoaded", function () {
                var btnAbsen = document.getElementById('btnAbsenMasuk');
                if (btnAbsen) btnAbsen.disabled = true;

                // Ambil lokasi saat halaman dimuat
                ambilLokasiDanTampilkan();

                // Event listener untuk tombol refresh
                document.getElementById('refreshLocation').onclick = function () {
                    ambilLokasiDanTampilkan();
                };

                // Event listener untuk tombol lokasi akurat
                document.getElementById('accurateLocation').onclick = function () {
                    const accurateBtn = this;
                    const originalText = accurateBtn.innerHTML;
                    accurateBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mencari Lokasi Akurat...';
                    accurateBtn.disabled = true;

                    // Reset info panel
                    const locationInfo = document.getElementById('locationInfo');
                    if (locationInfo) {
                        locationInfo.style.borderLeftColor = '#007bff';
                        locationInfo.style.backgroundColor = '#f8f9fa';
                    }

                    cobaLokasiAkurat()
                        .then(function (position) {
                            const accuracy = position.coords.accuracy;
                            const accuracyText = accuracy <= 10 ? 'Sangat Akurat' :
                                accuracy <= 50 ? 'Akurat' :
                                    accuracy <= 100 ? 'Cukup Akurat' : 'Kurang Akurat';

                            // Analisa Fake GPS
                            let status_gps = 'valid';
                            let debug_reason = '';

                            if (position.mocked === true) {
                                status_gps = 'fake';
                                debug_reason = 'OS Mock Location Terdeteksi';
                            } else if ((accuracy === 150 || accuracy === 10) && position.coords.altitude === null && position.coords.speed === null) {
                                status_gps = 'fake';
                                debug_reason = 'Terdeteksi Emulator / Ekstensi Fake GPS Chrome';
                            } else if (accuracy <= 4) {
                                status_gps = 'suspicious';
                                debug_reason = 'Akurasi terlalu sempurna tidak wajar (<5m)';
                            } else if (position.coords.altitude === 0 && position.coords.speed === 0) {
                                status_gps = 'suspicious';
                                debug_reason = 'Terdeteksi Aplikasi Fake GPS (Altitude & Speed 0 mutlak)';
                            } else if (position.coords.altitude === 0 && position.coords.heading === 0) {
                                status_gps = 'suspicious';
                                debug_reason = 'Terdeteksi Aplikasi Fake GPS (Altitude & Heading 0 mutlak)';
                            } else if (position.coords.altitude !== null && position.coords.altitude === 0) {
                                status_gps = 'suspicious';
                                debug_reason = 'Ketinggian (Altitude) 0 mutlak tidak wajar';
                            } else if (accuracy > 1000) {
                                status_gps = 'suspicious';
                                debug_reason = 'Jangkauan lokasi sangat melenceng (>1Km)';
                            }

                            absenLokasi.status_gps = status_gps;

                            // Update informasi akurasi di HTML
                            const accuracyInfo = document.getElementById('accuracyInfo');
                            if (accuracyInfo) {
                                let additionalText = '';
                                if (status_gps === 'suspicious') {
                                    additionalText = ` <br><span style="color:#fd7e14; font-size:13px;"><i class="fa fa-warning"></i> <b>Status GPS: Mencurigakan</b> (${debug_reason})</span>`;
                                } else if (status_gps === 'fake') {
                                    additionalText = ` <br><span style="color:#dc3545; font-size:13px;"><i class="fa fa-ban"></i> <b>Status GPS: Fake GPS!</b> (${debug_reason})</span>`;
                                } else {
                                    additionalText = ` <br><span style="color:#28a745; font-size:13px;"><i class="fa fa-check-circle"></i> <b>Status GPS: Valid (Aman)</b></span>`;
                                }

                                accuracyInfo.innerHTML = `Akurasi: ${accuracy.toFixed(0)} meter (${accuracyText})${additionalText}`;
                                if (status_gps === 'fake') {
                                    accuracyInfo.style.color = '#dc3545';
                                } else if (status_gps === 'suspicious') {
                                    accuracyInfo.style.color = '#fd7e14';
                                } else if (accuracy <= 10) {
                                    accuracyInfo.style.color = '#28a745';
                                } else if (accuracy <= 50) {
                                    accuracyInfo.style.color = '#17a2b8';
                                } else if (accuracy <= 100) {
                                    accuracyInfo.style.color = '#ffc107';
                                } else {
                                    accuracyInfo.style.color = '#dc3545';
                                }
                            }

                            if (status_gps === 'fake') {
                                document.getElementById('map').innerHTML = `<div style='color:red; padding:20px; text-align:center;'>
                        <i class='fa fa-ban fa-3x'></i><br>
                        <strong>FAKE GPS TERDETEKSI</strong><br>
                        ${debug_reason}. Matikan alat pemalsu lokasi Anda untuk dapat melakukan presensi.<br><br>
                        <button onclick='cobaLokasiAkurat()' class='btn btn-primary'>Coba Lagi</button>
                    </div>`;
                                accurateBtn.innerHTML = originalText;
                                accurateBtn.disabled = false;
                                return;
                            }

                            // Tampilkan peta dengan lokasi baru
                            tampilkanPeta(position.coords.latitude, position.coords.longitude, accuracy);

                            // Update status panel
                            if (accuracy > 100) {
                                if (locationInfo) {
                                    locationInfo.style.borderLeftColor = '#dc3545';
                                    locationInfo.style.backgroundColor = '#f8d7da';
                                }
                            } else {
                                if (locationInfo) {
                                    locationInfo.style.borderLeftColor = '#28a745';
                                    locationInfo.style.backgroundColor = '#d4edda';
                                }
                            }

                            // Reset button
                            accurateBtn.innerHTML = originalText;
                            accurateBtn.disabled = false;
                        })
                        .catch(function (error) {
                            console.error('Error getting accurate location:', error);
                            alert('Gagal mendapatkan lokasi akurat. Silakan coba lagi atau gunakan refresh lokasi biasa.');

                            // Reset button
                            accurateBtn.innerHTML = originalText;
                            accurateBtn.disabled = false;
                        });
                };

                // Monitoring status GPS secara berkala
                setInterval(function () {
                    if (locationObtained) {
                        cekStatusGPS().then(function (gpsStatus) {
                            if (!gpsStatus.available) {
                                // Tampilkan peringatan jika GPS dimatikan setelah sebelumnya aktif
                                const warningDiv = document.getElementById('gpsWarning');
                                if (!warningDiv) {
                                    const warning = document.createElement('div');
                                    warning.id = 'gpsWarning';
                                    warning.className = 'alert alert-warning';
                                    warning.style.position = 'fixed';
                                    warning.style.top = '10px';
                                    warning.style.right = '10px';
                                    warning.style.zIndex = '9999';
                                    warning.style.maxWidth = '300px';
                                    warning.innerHTML = `
                            <strong><i class="fa fa-exclamation-triangle"></i> GPS Dimatikan!</strong><br>
                            Lokasi Anda mungkin tidak akurat. Silakan aktifkan GPS kembali.
                            <button onclick="this.parentElement.remove()" class="btn btn-sm btn-warning" style="margin-top: 5px;">Tutup</button>
                        `;
                                    document.body.appendChild(warning);
                                }
                            }
                        });
                    }
                }, 10000); // Cek setiap 10 detik

                // Opsional: Mulai monitoring lokasi untuk update otomatis
                // mulaiMonitoringLokasi();
            });
        </script>

        <style>
            #map {
                height: 300px !important;
                min-width: 100%;
                border-radius: 10px;
                margin-bottom: 20px;
                display: block;
            }
        </style>
    <?php endif; ?>