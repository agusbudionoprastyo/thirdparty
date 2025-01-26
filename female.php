<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Query untuk mengambil pasangan pria dan wanita berdasarkan session_completed = 0
$sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ambil data pasangan
    $match = $result->fetch_assoc();
    $male_user_id = $match['male_user_id'];
    $female_user_id = $match['female_user_id'];

    // Query untuk mengambil data pengguna pria berdasarkan male_user_id
    $male_sql = "SELECT * FROM users WHERE id = $male_user_id";
    $male_result = $conn->query($male_sql);
    $male = $male_result->fetch_assoc();

    // Cek jika tombol "Like" atau "Dislike" ditekan
    if (isset($_POST['vote'])) {
        $vote = $_POST['vote']; // 'like' atau 'dislike'

        // Pastikan hanya like atau dislike yang valid
        if (in_array($vote, ['like', 'dislike'])) {
            // Update vote untuk pasangan di tabel matches
            $update_sql = "UPDATE matches SET female_vote = '$vote' WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
            if ($conn->query($update_sql) === TRUE) {
                echo "Vote berhasil diberikan: " . ucfirst($vote) . "!<br>";

                // Cek apakah pria sudah memberikan vote
                $check_vote_sql = "SELECT * FROM matches WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
                $check_vote_result = $conn->query($check_vote_sql);
                $check_vote = $check_vote_result->fetch_assoc();

                // Jika kedua pasangan sudah memberikan vote, ubah session_completed menjadi 1
                if ($check_vote['male_vote'] && $check_vote['female_vote']) {
                    $update_session_sql = "UPDATE matches SET session_completed = 1 WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
                    if ($conn->query($update_session_sql) === TRUE) {
                        echo "Sesi pasangan ini telah selesai!";
                    } else {
                        echo "Error saat mengubah session_completed: " . $conn->error;
                    }
                }
            } else {
                echo "Error: " . $conn->error;
            }
        }
    }
} else {
    echo "Tidak ada pasangan pria yang tersedia untuk diproses.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Female</title>
</head>
<body>
    <h1>Vote Pasangan</h1>
    
    <?php if (isset($male)): ?>
        <p>Pasangan yang ditemukan:</p>
        <p>Perempuan: <?php echo $female_user_id; ?></p>
        <p>Laki-laki: <?php echo $male['username']; ?> (<?php echo $male['gender']; ?>)</p>
        
        <!-- Tombol untuk memberikan vote -->
        <form method="POST">
            <button type="submit" name="vote" value="like">Like</button>
            <button type="submit" name="vote" value="dislike">Dislike</button>
        </form>
    <?php endif; ?>
</body>
</html>
