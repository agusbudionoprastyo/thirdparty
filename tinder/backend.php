<?php
require_once '../helper/db.php';

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

// Fungsi untuk mengambil detail pengguna berdasarkan ID
function getUserDetails($user_id, $conn) {
    $sql = "SELECT id, username, gender, age, phone, photo FROM users WHERE id = $user_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

while (true) {
    // Cek apakah ada pasangan dengan session_completed = 0
    $check_sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Ada pasangan yang belum selesai, ambil ID pasangan yang belum diproses
        $pending_match = $check_result->fetch_assoc();
        $male_user_id = $pending_match['male_user_id'];
        $female_user_id = $pending_match['female_user_id'];

        // Ambil detail pengguna pria dan wanita
        $male_details = getUserDetails($male_user_id, $conn);
        $female_details = getUserDetails($female_user_id, $conn);

        if ($male_details && $female_details) {
            // Kirimkan detail pasangan yang belum diproses ke frontend dalam format JSON
            $message = json_encode([
                'status' => 'waiting',
                'male_user' => $male_details,
                'female_user' => $female_details
            ]);
            sendMessage($message);
        } else {
            sendMessage("Error: Tidak dapat menemukan detail pengguna.");
        }
    } else {
        // Ambil 1 pengguna pria yang belum dipasangkan dan belum ada di pasangan yang sudah match
        $male_sql = "
            SELECT * FROM users 
            WHERE gender = 'male' 
            AND id NOT IN (SELECT male_user_id FROM matches WHERE is_match = 1)
            ORDER BY RAND()
            LIMIT 1
        ";
        $male_result = $conn->query($male_sql);

        // Ambil 1 pengguna wanita yang belum dipasangkan dan belum ada di pasangan yang sudah match
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

            // Tambahkan pengecekan apakah pasangan pria dan wanita sudah pernah dipasangkan dan tidak saling suka
            $check_existing_sql = "
                SELECT * FROM matches 
                WHERE (male_user_id = $male_user_id AND female_user_id = $female_user_id)
                OR (male_user_id = $female_user_id AND female_user_id = $male_user_id)
                AND is_match = 0
            ";
            $check_existing_result = $conn->query($check_existing_sql);

            if ($check_existing_result->num_rows > 0) {
                // Jika pasangan sudah ada dan tidak saling suka, jangan pasangkan lagi
                sendMessage("Pasangan ini sudah ada dan tidak saling suka, tidak akan dipasangkan lagi.");
            } else {
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
            }
        } else {
            sendMessage("Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan.");
        }
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