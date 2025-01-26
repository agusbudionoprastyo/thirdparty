<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Cek apakah tombol "Start" telah ditekan
if (isset($_POST['start']) && $_POST['start'] == 1) {

    // Ambil 1 pengguna pria yang belum ada di tabel matches
    $male_sql = "
        SELECT * FROM users 
        WHERE gender = 'male' 
        AND id NOT IN (SELECT male_user_id FROM matches WHERE session_completed = 1)
        AND id NOT IN (SELECT female_user_id FROM matches WHERE session_completed = 1)
        ORDER BY RAND() LIMIT 1
    ";
    $male_result = $conn->query($male_sql);

    if ($male_result->num_rows > 0) {
        $male = $male_result->fetch_assoc();
        $male_user_id = $male['id'];  // ID pengguna pria
    } else {
        echo "Tidak ada pengguna pria yang tersedia.";
        exit;
    }

    // Ambil 1 pengguna wanita yang belum ada di tabel matches
    $female_sql = "
        SELECT * FROM users 
        WHERE gender = 'female' 
        AND id NOT IN (SELECT male_user_id FROM matches WHERE session_completed = 1)
        AND id NOT IN (SELECT female_user_id FROM matches WHERE session_completed = 1)
        ORDER BY RAND() LIMIT 1
    ";
    $female_result = $conn->query($female_sql);

    if ($female_result->num_rows > 0) {
        $female = $female_result->fetch_assoc();
        $female_user_id = $female['id'];  // ID pengguna wanita
    } else {
        echo "Tidak ada pengguna wanita yang tersedia.";
        exit;
    }

    // Insert pasangan ke tabel matches
    $insert_sql = "
        INSERT INTO matches (male_user_id, female_user_id, session_completed) 
        VALUES ($male_user_id, $female_user_id, 0)
    ";
    if ($conn->query($insert_sql) === TRUE) {
        echo "Pasangan berhasil dibuat!<br>";
        echo "Laki-laki: " . $male['username'] . " (" . $male['gender'] . ")<br>";
        echo "Perempuan: " . $female['username'] . " (" . $female['gender'] . ")<br>";
    } else {
        echo "Error: " . $insert_sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>