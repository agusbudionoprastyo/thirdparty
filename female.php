<?php
include('helper/db.php');

// Ambil ID perempuan yang aktif, misalnya ID = 1
$femaleId = 1;  // Ganti dengan ID pengguna perempuan yang aktif

// Ambil pasangan laki-laki yang belum memberi vote (session_completed = FALSE)
$sql = "SELECT id, username FROM users WHERE gender = 'male' AND session_completed = FALSE";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Female - Vote</title>
</head>
<body>
    <h1>Vote untuk Laki-laki</h1>
    <form method="POST">
        <ul>
            <?php while($row = $result->fetch_assoc()): ?>
                <li>
                    <?php echo $row['username']; ?>
                    <button type="submit" name="vote_like" value="<?php echo $row['id']; ?>">Like</button>
                    <button type="submit" name="vote_dislike" value="<?php echo $row['id']; ?>">Dislike</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </form>

    <?php
    // Jika ada vote yang dikirimkan
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $maleId = $_POST['vote_like'] ?? $_POST['vote_dislike'];
        $vote = isset($_POST['vote_like']) ? 'like' : 'dislike';

        // Cek apakah pasangan ini sudah ada di tabel matches
        $stmt = $conn->prepare("SELECT * FROM matches WHERE female_user_id = ? AND male_user_id = ?");
        $stmt->bind_param("ii", $femaleId, $maleId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Jika belum ada, tambahkan entri ke tabel matches
            $stmt = $conn->prepare("INSERT INTO matches (female_user_id, male_user_id, female_vote) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $femaleId, $maleId, $vote);
            $stmt->execute();
        } else {
            // Jika sudah ada, update vote perempuan
            $stmt = $conn->prepare("UPDATE matches SET female_vote = ? WHERE female_user_id = ? AND male_user_id = ?");
            $stmt->bind_param("sii", $vote, $femaleId, $maleId);
            $stmt->execute();
        }

        // Update session_completed pada pengguna perempuan
        $stmt = $conn->prepare("UPDATE users SET session_completed = TRUE WHERE id = ?");
        $stmt->bind_param("i", $femaleId);
        $stmt->execute();

        // Cek apakah pasangan laki-laki sudah memberikan vote
        $stmt = $conn->prepare("SELECT male_vote FROM matches WHERE female_user_id = ? AND male_user_id = ?");
        $stmt->bind_param("ii", $femaleId, $maleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        // Jika keduanya memberi "like", maka match!
        if ($data['male_vote'] == 'like' && $vote == 'like') {
            // Tandai sebagai match
            $stmt = $conn->prepare("UPDATE matches SET is_match = TRUE WHERE female_user_id = ? AND male_user_id = ?");
            $stmt->bind_param("ii", $femaleId, $maleId);
            $stmt->execute();

            echo "<p>It's a match!</p>";
        }

        echo "<p>Vote telah diberikan! Tunggu sampai pasangan Anda memberikan vote.</p>";
    }

    $conn->close();
    ?>
</body>
</html>