<?php
    //Koneksi database
    include '../../config/database.php';
    //Mengambil username
    $username=$_POST['username'];

    //Kondisi jika usrname kosong
    if (empty($username)){
        echo "<p class='text-warning'>Username Tidak Boleh Kosong</p>";
        echo "<script>   document.getElementById('submit').disabled = true; </script>";
   
    } else {
        $query = mysqli_query($kon, "SELECT username FROM tbl_user where username='$username'");
        $jumlah = mysqli_num_rows($query);
        //Kondisi jika username sudah digunakan
        if ($jumlah>0){
            echo "<p class='text-danger'>Error: Username tidak dapat digunakan</p>";
            echo "<script>   document.getElementById('submit').disabled = true; </script>";
         
        }else {
            //Kondisi jika tersedia
            echo "<p class='text-success'>Username dapat digunakan</p>";
            echo "<script>   document.getElementById('submit').disabled = false; </script>";

        }
    }
    
?>