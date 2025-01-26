<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Cek apakah tombol "Start" telah ditekan
if (isset($_POST['start']) && $_POST['start'] == 1) {

    // Ambil 1 pengguna pria yang belum ada di tabel matches
    $male_sql = "
        SELECT * FROM users 
        WHERE gender = 'male' 
        AND id NOT IN (SELECT male_user_id FROM matches WHERE is_match = 1)
        ORDER BY RAND()
    ";
    $male_result = $conn->query($male_sql);

    // Ambil 1 pengguna wanita yang belum ada di tabel matches
    $female_sql = "
        SELECT * FROM users 
        WHERE gender = 'female' 
        AND id NOT IN (SELECT female_user_id FROM matches WHERE is_match = 1)
        ORDER BY RAND()
    ";
    $female_result = $conn->query($female_sql);

    // Jika ada data pengguna pria dan wanita yang belum dipasangkan
    if ($male_result->num_rows > 0 && $female_result->num_rows > 0) {
        // Ambil data pengguna pria
        $male = $male_result->fetch_assoc();
        $male_user_id = $male['id'];

        // Ambil data pengguna wanita
        $female = $female_result->fetch_assoc();
        $female_user_id = $female['id'];

        // Masukkan pasangan ke dalam tabel matches
        $insert_sql = "
            INSERT INTO matches (male_user_id, female_user_id, is_match, session_completed) 
            VALUES ($male_user_id, $female_user_id, 0, 0)
        ";
        if ($conn->query($insert_sql) === TRUE) {
            echo "Pasangan berhasil dibuat: " . $male['username'] . " - " . $female['username'] . "<br>";
        } else {
            echo "Error: " . $insert_sql . "<br>" . $conn->error;
        }
    } else {
        echo "Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan.";
    }
}

$conn->close();
?>