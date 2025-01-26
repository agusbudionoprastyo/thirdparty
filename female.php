<?php
include('helper/db.php');

// Ambil pengguna laki-laki yang belum menyelesaikan sesi voting (session_completed = FALSE)
$sql = "SELECT u.id, u.username 
        FROM users u
        LEFT JOIN matches m ON u.id = m.male_user_id 
        WHERE m.female_user_id = 1 AND (m.session_completed = FALSE OR m.session_completed IS NULL)"; // Ganti 1 dengan ID pengguna female yang sedang login
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
    // Proses vote
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $maleId = $_POST['vote_like'] ?? $_POST['vote_dislike'];
        $vote = isset($_POST['vote_like']) ? 'like' : 'dislike';
        $femaleId = 1; // ID pengguna female yang sedang login (ganti dengan ID yang sesuai)

        // Pastikan tidak ada sesi voting yang sudah selesai
        $stmt = $conn->prepare("SELECT * FROM matches WHERE female_user_id = ? AND male_user_id = ? AND session_completed = FALSE");
        $stmt->bind_param("ii", $femaleId, $maleId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Jika ada data yang belum selesai sesi votingnya
            // Update vote perempuan
            $stmt = $conn->prepare("INSERT INTO matches (male_user_id, female_user_id, female_vote) 
                                    VALUES (?, ?, ?) 
                                    ON DUPLICATE KEY UPDATE female_vote = ?");
            $stmt->bind_param("iiss", $maleId, $femaleId, $vote, $vote);
            $stmt->execute();

            // Cek jika pasangan juga sudah melakukan vote
            $stmt = $conn->prepare("SELECT male_vote FROM matches WHERE female_user_id = ? AND male_user_id = ?");
            $stmt->bind_param("ii", $femaleId, $maleId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();

            if ($data['male_vote'] != 'dislike') {
                // Jika pasangan laki-laki juga like, tandai sebagai match
                $stmt = $conn->prepare("UPDATE matches SET is_match = TRUE, session_completed = TRUE WHERE female_user_id = ? AND male_user_id = ?");
                $stmt->bind_param("ii", $femaleId, $maleId);
                $stmt->execute();
                echo "<p>It's a match!</p>";
            }
        } else {
            echo "<p>Anda sudah memberi vote untuk pasangan ini atau sesi voting telah selesai.</p>";
        }
    }

    $conn->close();
    ?>
</body>
</html>
