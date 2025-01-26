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

    // Query untuk mengambil data pengguna wanita berdasarkan female_user_id
    $female_sql = "SELECT * FROM users WHERE id = $female_user_id";
    $female_result = $conn->query($female_sql);
    $female = $female_result->fetch_assoc();

    // Jika form vote sudah disubmit
    if (isset($_POST['vote'])) {
        $vote = $_POST['vote']; // 'like' atau 'dislike'

        // Update vote untuk pasangan di tabel matches
        $update_sql = "UPDATE matches SET male_vote = '$vote' WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
        if ($conn->query($update_sql) === TRUE) {
            echo "Vote berhasil diberikan: " . ucfirst($vote) . "!<br>";

            // Cek apakah wanita juga sudah memberikan vote
            $check_vote_sql = "SELECT * FROM matches WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
            $check_vote_result = $conn->query($check_vote_sql);
            $check_vote = $check_vote_result->fetch_assoc();

            if ($check_vote['male_vote'] && $check_vote['female_vote']) {
                // Jika kedua pasangan sudah memberikan vote, ubah session_completed menjadi 1
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
} else {
    echo "Tidak ada pasangan wanita yang tersedia untuk diproses.";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vote - Male</title>
</head>
<body>
    <h1>Vote Pasangan</h1>
    
    <?php if (isset($female)): ?>
        <p>Pasangan yang ditemukan:</p>
        <p>Laki-laki: <?php echo $male_user_id; ?></p>
        <p>Perempuan: <?php echo $female['username']; ?> (<?php echo $female['gender']; ?>)</p>
        
        <!-- Form untuk memberikan vote -->
        <form method="POST">
            <label for="like">Like</label>
            <input type="radio" name="vote" value="like" required>
            <label for="dislike">Dislike</label>
            <input type="radio" name="vote" value="dislike" required>

            <button type="submit">Submit Vote</button>
        </form>
    <?php endif; ?>
</body>
</html>
