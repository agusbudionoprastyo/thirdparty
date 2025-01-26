<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Menyiapkan header untuk SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

// Fungsi untuk mengirim pesan ke klien
function sendMessage($message) {
    echo "data: $message\n\n";
    ob_flush();
    flush();
}

// Periksa apakah ada entry dengan session_completed = 0
while (true) {
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows == 0) {
        // Jika tidak ada pasangan dengan session_completed = 0, jalankan generate_match.php
        ob_start();
        include('generate_match.php');
        $output = ob_get_clean();
        sendMessage("Pasangan baru telah dibuat: $output");
    } else {
        sendMessage("Ada pasangan dengan session_completed = 0, menunggu untuk generate pasangan baru.");
    }

    // Tunggu selama 1 detik sebelum melakukan pengecekan lagi
    sleep(1);
}

$conn->close();
?>
