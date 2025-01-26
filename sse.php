<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Cek apakah tombol "Start" telah ditekan
if (isset($_POST['start']) && $_POST['start'] == 1) {
    
    // Cek apakah ada entry dengan session_completed = 0
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0";
    $check_result = $conn->query($check_sql);

    // Jika tidak ada pasangan dengan session_completed = 0
    if ($check_result->num_rows == 0) {
        // Eksekusi generate_match.php jika tidak ada pasangan dengan session_completed = 0
        include('generate_match.php');
    } else {
        // Jika ada pasangan dengan session_completed = 0, tampilkan pesan
        echo "Ada pasangan dengan session_completed = 0, tidak perlu membuat pasangan baru.";
    }
}

$conn->close();
?>