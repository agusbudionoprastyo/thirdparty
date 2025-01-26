<?php
require_once 'helper/db.php';

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('Access-Control-Allow-Origin: *');

function sendMessage($message) {
    echo "data: $message\n\n";
    ob_flush();
    flush();
}

while (true) {
    // Cek apakah ada pasangan dengan session_completed = 0
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1";
    $check_result = $conn->query($check_sql);

    // Jika ada pasangan yang belum selesai diproses, jangan buat pasangan baru
    if ($check_result->num_rows > 0) {
        sendMessage("Ada pasangan yang belum diproses, menunggu untuk diproses.");
    } else {
        // Ambil 1 pengguna pria yang belum dipasangkan dengan wanita tertentu
        $male_sql = "
            SELECT * FROM users 
            WHERE gender = 'male' 
            AND id NOT IN (SELECT male_user_id FROM matches WHERE female_user_id IS NOT NULL)
            ORDER BY RAND()
            LIMIT 1
        ";
        $male_result = $conn->query($male_sql);

        // Ambil 1 pengguna wanita yang belum dipasangkan dengan pria tertentu
        $female_sql = "
            SELECT * FROM users 
            WHERE gender = 'female' 
            AND id NOT IN (SELECT female_user_id FROM matches WHERE male_user_id IS NOT NULL)
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

            // Periksa apakah pria dan wanita ini sudah dipasangkan sebelumnya
            $pair_check_sql = "
                SELECT * FROM matches
                WHERE (male_user_id = $male_user_id AND female_user_id = $female_user_id)
                   OR (male_user_id = $female_user_id AND female_user_id = $male_user_id)
                LIMIT 1
            ";
            $pair_check_result = $conn->query($pair_check_sql);

            if ($pair_check_result->num_rows == 0) {
                // Masukkan pasangan ke dalam tabel matches (session_completed = 0 secara default)
                $insert_sql = "
                    INSERT INTO matches (male_user_id, female_user_id, session_completed) 
                    VALUES ($male_user_id, $female_user_id, 0)
                ";

                if ($conn->query($insert_sql) === TRUE) {
                    sendMessage("Pasangan berhasil dibuat: " . $male['username'] . " - " . $female['username']);
                } else {
                    sendMessage("Error: " . $insert_sql . " - " . $conn->error);
                }
            } else {
                sendMessage("Pasangan ini sudah ada sebelumnya: " . $male['username'] . " - " . $female['username']);
            }
        } else {
            sendMessage("Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan.");
        }
    }

    sleep(1);
}

$conn->close();

?>