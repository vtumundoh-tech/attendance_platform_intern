<?php
include 'config/database.php';
$query = "ALTER TABLE tbl_absensi ADD COLUMN status_gps ENUM('valid', 'suspicious', 'fake') DEFAULT 'valid'";
if(mysqli_query($kon, $query)) {
    echo "Success adding column\n";
} else {
    echo "Error: " . mysqli_error($kon) . "\n";
}
?>
