<?php
    session_start();
?>

<form id="form-cetak-absensi" method="GET" enctype="multipart/form-data">
    <div class="row">
    <div class="col-sm-6">
        <div class="form-group">
                <input type="hidden" name="id_mahasiswa" value="<?php echo $_POST['id_mahasiswa']; ?>">
                <label>Tanggal Awal :</label>
                <input type="date" name="tanggal_awal" class="form-control" value="" required>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label>Tanggal Akhir :</label>
                <input type="date" name="tanggal_akhir" class="form-control"  value="" required>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                <br>
                <button type="button" id="btn-cetak-pdf" class="btn btn-primary" ><i class="fa fa-print"></i> Cetak PDF</button>
                <button type="button" id="btn-cetak-excel" class="btn btn-success" ><i class="fa fa-file-excel-o"></i> Cetak Excel</button>
            </div>
        </div>
    </div>
</form>
<style>
.shake {
    animation: shake 0.4s ease-in-out;
}
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-8px); }
    40% { transform: translateX(8px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
}
</style>
<script>
function validateFormAbsensi(actionUrl) {
    var tanggalAwal = $('#form-cetak-absensi input[name="tanggal_awal"]');
    var tanggalAkhir = $('#form-cetak-absensi input[name="tanggal_akhir"]');
    var valid = true;

    tanggalAwal.removeClass('is-invalid');
    tanggalAkhir.removeClass('is-invalid');

    if (tanggalAwal.val() === '') {
        tanggalAwal.addClass('is-invalid');
        valid = false;
    }
    if (tanggalAkhir.val() === '') {
        tanggalAkhir.addClass('is-invalid');
        valid = false;
    }

    if (!valid) {
        tanggalAwal.add(tanggalAkhir).each(function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('shake');
                void this.offsetWidth;
                $(this).addClass('shake');
            }
        });
    } else {
        $('#form-cetak-absensi').attr('action', actionUrl).attr('method', 'GET').submit();
    }
}

$(document).on('click', '#btn-cetak-pdf', function() {
    validateFormAbsensi('apps/cetak/cetak_absensi.php');
});
$(document).on('click', '#btn-cetak-excel', function() {
    validateFormAbsensi('apps/cetak/cetak_absensi_html_excel.php');
});
$(document).on('change input', '#form-cetak-absensi input[name="tanggal_awal"], #form-cetak-absensi input[name="tanggal_akhir"]', function() {
    $(this).removeClass('is-invalid shake');
});
</script>