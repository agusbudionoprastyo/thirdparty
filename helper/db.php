<?php
$servername = "localhost";
$username = "dafm5634_ag";  // Ganti dengan username MySQL Anda
$password = "Ag7us777__";      // Ganti dengan password MySQL Anda
$dbname = "dafm5634_thirdparty";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
