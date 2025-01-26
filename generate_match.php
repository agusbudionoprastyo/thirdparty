<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Cek apakah tombol "Start" telah ditekan
if (isset($_POST['start']) && $_POST['start'] == 1) {

    // Ambil semua pengguna pria yang belum ada di tabel matches
    $male_sql = "
        SELECT * FROM users 
        WHERE gender = 'male' 
        AND id NOT IN (SELECT male_user_id FROM matches WHERE session_completed = 0)
        ORDER BY RAND()
    ";
    $male_result = $conn->query($male_sql);

    // Ambil semua pengguna wanita yang belum ada di tabel matches
    $female_sql = "
        SELECT * FROM users 
        WHERE gender = 'female' 
        AND id NOT IN (SELECT female_user_id FROM matches WHERE session_completed = 0)
        ORDER BY RAND()
    ";
    $female_result = $conn->query($female_sql);

    // Jika ada data pengguna pria dan wanita yang belum dipasangkan
    if ($male_result->num_rows > 0 && $female_result->num_rows > 0) {
        // Simpan semua pengguna pria dan wanita dalam array
        $male_users = [];
        while ($row = $male_result->fetch_assoc()) {
            $male_users[] = $row;
        }

        $female_users = [];
        while ($row = $female_result->fetch_assoc()) {
            $female_users[] = $row;
        }

        // Algoritma untuk mencocokkan semua pasangan
        foreach ($male_users as $male) {
            foreach ($female_users as $female) {
                // Masukkan pasangan ke dalam tabel matches
                $insert_sql = "
                    INSERT INTO matches (male_user_id, female_user_id, session_completed) 
                    VALUES (" . $male['id'] . ", " . $female['id'] . ", 0)
                ";
                if ($conn->query($insert_sql) === TRUE) {
                    echo "Pasangan berhasil dibuat: " . $male['username'] . " - " . $female['username'] . "<br>";
                } else {
                    echo "Error: " . $insert_sql . "<br>" . $conn->error;
                }
            }
        }
    } else {
        echo "Tidak ada pasangan pria atau wanita yang tersedia untuk dipasangkan.";
    }
}

$conn->close();
?>