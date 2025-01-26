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

// Proses utama untuk memeriksa dan membuat pasangan baru
while (true) {
    // Cek apakah ada pasangan yang belum dipasangkan (is_match = 0)
    $check_sql = "SELECT * FROM matches WHERE is_match = 0 LIMIT 1";
    $check_result = $conn->query($check_sql);

    // Jika ada pasangan yang belum dipasangkan, jangan buat pasangan baru
    if ($check_result->num_rows > 0) {
        sendMessage("Ada pasangan yang belum dipasangkan, menunggu untuk diproses.");
    } else {
        // Ambil 1 pengguna pria yang belum dipasangkan
        $male_sql = "
            SELECT * FROM users 
            WHERE gender = 'male' 
            AND id NOT IN (SELECT male_user_id FROM matches WHERE is_match = 1)
            ORDER BY RAND()
            LIMIT 1
        ";
        $male_result = $conn->query($male_sql);

        // Ambil 1 pengguna wanita yang belum dipasangkan
        $female_sql = "
            SELECT * FROM users 
            WHERE gender = 'female' 
            AND id NOT IN (SELECT female_user_id FROM matches WHERE is_match = 1)
            ORDER BY RAND()
            LIMIT 1
        ";
        $female_result = $conn->query($female_sql);

        // Jika ada pasangan pria dan wanita yang belum dipasangkan
        if ($male_result->num_rows > 0 && $female_result->num_rows > 0) {
            // Ambil data pengguna pria
            $male = $male_result->fetch_assoc();
            $male_user_id = $male['id'];

            // Ambil data pengguna wanita
            $female = $female_result->fetch_assoc();
            $female_user_id = $female['id'];

            // Masukkan pasangan ke dalam tabel matches
            $insert_sql = "
                INSERT INTO matches (male_user_id, female_user_id, is_match) 
                VALUES ($male_user_id, $female_user_id, 0)
            ";
            
            if ($conn->query($insert_sql) === TRUE) {
                sendMessage("Pasangan berhasil dibuat: " . $male['username'] . " - " . $female['username']);
            } else {
                sendMessage("Error: " . $insert_sql . " - " . $conn->error);
            }
        } else {
            sendMessage("Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan.");
        }
    }

    // Tunggu selama 1 detik sebelum pengecekan berikutnya
    sleep(1);
}

$conn->close();
?>