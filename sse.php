<?php
// sse.php
require_once 'helper/db.php'; // Pastikan path ini sesuai dengan lokasi db.php

// Set header untuk SSE
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Fungsi untuk mengirimkan data ke klien (browser)
function sendEvent($data) {
    echo "data: " . json_encode($data) . "\n\n"; // Kirim data dalam format JSON
    flush(); // Memastikan data segera dikirim
}

// Loop untuk terus memantau perubahan di tabel matches
while (true) {
    // Cek apakah ada pasangan yang belum selesai (session_completed = 0)
    $sql = "
        SELECT * FROM matches 
        WHERE session_completed = 0 
        LIMIT 1
    ";
    $result = $conn->query($sql);

    // Jika tidak ada pasangan yang ditemukan (tabel matches kosong atau session_completed = 0)
    if ($result->num_rows == 0) {
        // Cek apakah ada pria dan wanita yang tersedia untuk dipasangkan
        $male_sql = "
            SELECT * FROM users 
            WHERE gender = 'male' 
            AND id NOT IN (SELECT male_user_id FROM matches WHERE is_match = 0 AND session_completed = 0)
            ORDER BY RAND() LIMIT 1
        ";
        $male_result = $conn->query($male_sql);

        $female_sql = "
            SELECT * FROM users 
            WHERE gender = 'female' 
            AND id NOT IN (SELECT female_user_id FROM matches WHERE is_match = 0 AND session_completed = 0)
            ORDER BY RAND() LIMIT 1
        ";
        $female_result = $conn->query($female_sql);

        // Jika ada pasangan pria dan wanita yang ditemukan
        if ($male_result->num_rows > 0 && $female_result->num_rows > 0) {
            // Ambil data pengguna pria dan wanita
            $male = $male_result->fetch_assoc();
            $male_user_id = $male['id'];

            $female = $female_result->fetch_assoc();
            $female_user_id = $female['id'];

            // Masukkan pasangan ke dalam tabel matches
            $insert_sql = "
                INSERT INTO matches (male_user_id, female_user_id, is_match, session_completed) 
                VALUES ($male_user_id, $female_user_id, 0, 0)
            ";

            if ($conn->query($insert_sql) === TRUE) {
                // Kirimkan data pasangan baru ke klien (browser)
                sendEvent([
                    'message' => "Pasangan baru berhasil dibuat: {$male['username']} - {$female['username']}",
                    'male_username' => $male['username'],
                    'female_username' => $female['username']
                ]);
            } else {
                sendEvent([
                    'message' => "Error: " . $conn->error
                ]);
            }
        } else {
            // Kirimkan informasi jika tidak ada pengguna yang dapat dipasangkan
            sendEvent([
                'message' => "Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan."
            ]);
        }
    }

    // Tunggu 3 detik sebelum memeriksa lagi
    sleep(3);
}
?>