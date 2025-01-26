<?php
session_start();
include('helper/db.php');

// Ambil ID pengguna laki-laki yang aktif dari session
$maleId = $_SESSION['user_id'];  // ID pengguna laki-laki yang aktif

// Ambil pasangan perempuan yang belum memberi vote
$sql = "SELECT id, username FROM users WHERE gender = 'female' AND session_completed = FALSE";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Male - Vote</title>
</head>
<body>
    <h1>Vote untuk Perempuan</h1>
    <form method="POST">
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
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
        $femaleId = $_POST['vote_like'] ?? $_POST['vote_dislike'];
        $vote = isset($_POST['vote_like']) ? 'like' : 'dislike';

        // Cek apakah pasangan ini sudah ada di tabel matches
        $stmt = $conn->prepare("SELECT * FROM matches WHERE male_user_id = ? AND female_user_id = ?");
        $stmt->bind_param("ii", $maleId, $femaleId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Jika belum ada, tambahkan entri ke tabel matches
            $stmt = $conn->prepare("INSERT INTO matches (male_user_id, female_user_id, male_vote) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $maleId, $femaleId, $vote);
            $stmt->execute();
        } else {
            // Jika sudah ada, update vote laki-laki
            $stmt = $conn->prepare("UPDATE matches SET male_vote = ? WHERE male_user_id = ? AND female_user_id = ?");
            $stmt->bind_param("sii", $vote, $maleId, $femaleId);
            $stmt->execute();
        }

        // Update session_completed pada pengguna laki-laki
        $stmt = $conn->prepare("UPDATE users SET session_completed = TRUE WHERE id = ?");
        $stmt->bind_param("i", $maleId);
        $stmt->execute();

        // Cek apakah pasangan perempuan sudah memberikan vote
        $stmt = $conn->prepare("SELECT female_vote FROM matches WHERE male_user_id = ? AND female_user_id = ?");
        $stmt->bind_param("ii", $maleId, $femaleId);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        // Jika keduanya memberi "like", maka match!
        if ($data['female_vote'] == 'like' && $vote == 'like') {
            // Tandai sebagai match
            $stmt = $conn->prepare("UPDATE matches SET is_match = TRUE WHERE male_user_id = ? AND female_user_id = ?");
            $stmt->bind_param("ii", $maleId, $femaleId);
            $stmt->execute();

            echo "<p>It's a match!</p>";
        }

        echo "<p>Vote telah diberikan! Tunggu sampai pasangan Anda memberikan vote.</p>";
    }

    $conn->close();
    ?>
</body>
</html>