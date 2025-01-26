<?php
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

while (true) {
    // Cek apakah ada pasangan dengan session_completed = 0
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Ada pasangan yang belum selesai, kirimkan detailnya
        while ($match = $check_result->fetch_assoc()) {
            $male_user_id = $match['male_user_id'];
            $female_user_id = $match['female_user_id'];

            // Ambil data pengguna pria
            $male_sql = "SELECT * FROM users WHERE id = $male_user_id";
            $male_result = $conn->query($male_sql);
            $male = $male_result->fetch_assoc();

            // Ambil data pengguna wanita
            $female_sql = "SELECT * FROM users WHERE id = $female_user_id";
            $female_result = $conn->query($female_sql);
            $female = $female_result->fetch_assoc();

            // Kirimkan detail pasangan yang belum diproses
            $message = "Pasangan belum diproses: ";
            $message .= "Pria: " . $male['username'] . " (ID: $male_user_id), ";
            $message .= "Wanita: " . $female['username'] . " (ID: $female_user_id), ";
            $message .= "Status: Menunggu voting";

            sendMessage($message);
        }
    } else {
        // Jika tidak ada pasangan yang belum diproses
        sendMessage("Tidak ada pasangan yang belum diproses.");
    }

    // Periksa pasangan yang telah dibuat, dan update jika sudah mendapatkan vote
    $check_vote_sql = "
        SELECT * FROM matches 
        WHERE session_completed = 0 AND male_vote IS NOT NULL AND female_vote IS NOT NULL
    ";
    $vote_result = $conn->query($check_vote_sql);

    while ($match = $vote_result->fetch_assoc()) {
        $match_id = $match['id'];
        $male_vote = $match['male_vote'];
        $female_vote = $match['female_vote'];

        // Jika kedua voting adalah LIKE (misalnya 1)
        if ($male_vote == "like" && $female_vote == "like") {
            // Update is_match menjadi 1
            $update_sql = "UPDATE matches SET is_match = 1 WHERE id = $match_id";
            if ($conn->query($update_sql) === TRUE) {
                sendMessage("Pasangan berhasil match: " . $match['male_user_id'] . " - " . $match['female_user_id']);
            } else {
                sendMessage("Error: " . $update_sql . " - " . $conn->error);
            }
        }

        // Set session_completed = 1 jika kedua voting sudah diberikan
        $update_session_sql = "UPDATE matches SET session_completed = 1 WHERE id = $match_id";
        if ($conn->query($update_session_sql) === TRUE) {
            sendMessage("Pasangan dengan ID $match_id telah selesai.");
        } else {
            sendMessage("Error updating session_completed: " . $update_session_sql . " - " . $conn->error);
        }
    }

    // Tunggu selama 1 detik sebelum pengecekan berikutnya
    sleep(1);
}

$conn->close();
?>