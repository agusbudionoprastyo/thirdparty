<?php
require_once 'helper/db.php';

// Mengambil pasangan yang belum diproses
$check_sql = "SELECT * FROM matches WHERE session_completed = 0";
$check_result = $conn->query($check_sql);

$unprocessed_matches = [];

if ($check_result->num_rows > 0) {
    // Ada pasangan yang belum diproses, ambil detailnya
    while ($row = $check_result->fetch_assoc()) {
        $male_user_id = $row['male_user_id'];
        $female_user_id = $row['female_user_id'];

        // Ambil data pengguna pria
        $male_sql = "SELECT * FROM users WHERE id = $male_user_id";
        $male_result = $conn->query($male_sql);
        $male = $male_result->fetch_assoc();

        // Ambil data pengguna wanita
        $female_sql = "SELECT * FROM users WHERE id = $female_user_id";
        $female_result = $conn->query($female_sql);
        $female = $female_result->fetch_assoc();

        // Masukkan pasangan yang belum diproses ke dalam array
        $unprocessed_matches[] = [
            'male_username' => $male['username'],
            'female_username' => $female['username'],
            'male_user_id' => $male_user_id,
            'female_user_id' => $female_user_id,
        ];
    }
}

// Menutup koneksi
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasangan yang Belum Diproses</title>
</head>
<body>
    <h1>Pasangan yang Belum Diproses</h1>
    <?php if (count($unprocessed_matches) > 0): ?>
        <ul>
            <?php foreach ($unprocessed_matches as $match): ?>
                <li>
                    <strong>Pasangan:</strong> <?= $match['male_username'] ?> dan <?= $match['female_username'] ?><br>
                    <strong>ID Pria:</strong> <?= $match['male_user_id'] ?><br>
                    <strong>ID Wanita:</strong> <?= $match['female_user_id'] ?><br>
                    <hr>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Tidak ada pasangan yang belum diproses.</p>
    <?php endif; ?>
</body>
</html>
