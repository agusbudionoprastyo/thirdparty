<?php
include('helper/db.php');

// Ambil daftar perempuan
$sql = "SELECT id, username FROM users WHERE gender = 'female' ORDER BY RAND() LIMIT 5"; // Ambil 5 perempuan secara acak
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
    // Proses vote like
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['vote_like'])) {
            $femaleId = $_POST['vote_like'];
            $maleId = 1; // ID pengguna male yang sedang login (misalnya ID 1)

            // Update vote male
            $stmt = $conn->prepare("INSERT INTO matches (male_user_id, female_user_id, male_vote) VALUES (?, ?, 'like') ON DUPLICATE KEY UPDATE male_vote = 'like'");
            $stmt->bind_param("ii", $maleId, $femaleId);
            $stmt->execute();

            // Cek jika pasangan female juga like
            $checkMatch = $conn->prepare("SELECT female_vote FROM matches WHERE male_user_id = ? AND female_user_id = ?");
            $checkMatch->bind_param("ii", $maleId, $femaleId);
            $checkMatch->execute();
            $result = $checkMatch->get_result();
            $data = $result->fetch_assoc();

            if ($data['female_vote'] == 'like') {
                // Tandai sebagai match
                $updateMatch = $conn->prepare("UPDATE matches SET is_match = TRUE WHERE male_user_id = ? AND female_user_id = ?");
                $updateMatch->bind_param("ii", $maleId, $femaleId);
                $updateMatch->execute();
                echo "<p>It's a match!</p>";
            }
        }

        if (isset($_POST['vote_dislike'])) {
            $femaleId = $_POST['vote_dislike'];
            $maleId = 1; // ID pengguna male yang sedang login (misalnya ID 1)

            // Update vote male
            $stmt = $conn->prepare("INSERT INTO matches (male_user_id, female_user_id, male_vote) VALUES (?, ?, 'dislike') ON DUPLICATE KEY UPDATE male_vote = 'dislike'");
            $stmt->bind_param("ii", $maleId, $femaleId);
            $stmt->execute();
        }
    }

    $conn->close();
    ?>
</body>
</html>
