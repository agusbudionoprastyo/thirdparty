<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Variabel untuk menampilkan pesan di index.php
$message = '';

if (isset($_POST['start']) && $_POST['start'] == 1) {
    
    // Cek apakah ada entry dengan session_completed = 0
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0";
    $check_result = $conn->query($check_sql);

    // Jika tidak ada pasangan dengan session_completed = 0
    if ($check_result->num_rows == 0) {
        // Eksekusi generate_match.php jika tidak ada pasangan dengan session_completed = 0
        include('generate_match.php');
        $message = "Tidak ada pasangan dengan session_completed = 0, jadi generate match baru dilakukan.";
    } else {
        // Jika ada pasangan dengan session_completed = 0, tampilkan pesan
        $message = "Ada pasangan dengan session_completed = 0, tidak perlu membuat pasangan baru.";
    }
}

// Tampilkan pesan jika ada
if (!empty($message)) {
    echo "<div class='message'>$message</div>";
}

$conn->close();
?>

<!-- Form untuk memulai eksekusi -->
<form method="post" action="index.php">
    <input type="hidden" name="start" value="1">
    <button type="submit">Mulai Proses</button>
</form>
