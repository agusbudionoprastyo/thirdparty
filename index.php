<?php
include('helper/db.php');

// Ambil 20 pengguna secara acak
$sql = "SELECT id, username, vote_count FROM users ORDER BY RAND() LIMIT 20";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realtime Voting</title>
</head>
<body>
    <h1>Vote untuk Pengguna Acak</h1>
    <form method="POST">
        <ul>
            <?php while($row = $result->fetch_assoc()): ?>
                <li>
                    <?php echo $row['username']; ?> (Vote: <?php echo $row['vote_count']; ?>)
                    <button type="submit" name="vote" value="<?php echo $row['id']; ?>">Vote</button>
                </li>
            <?php endwhile; ?>
        </ul>
    </form>
    <br>
    <a href="dashboard.php">Lihat Hasil Voting</a>

    <?php
    // Jika tombol vote ditekan
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote'])) {
        $userId = $_POST['vote'];

        // Update jumlah vote untuk user yang dipilih
        $updateQuery = "UPDATE users SET vote_count = vote_count + 1 WHERE id = $userId";
        if ($conn->query($updateQuery) === TRUE) {
            echo "<p>Vote berhasil!</p>";
        } else {
            echo "<p>Error: " . $conn->error . "</p>";
        }
    }

    $conn->close();
    ?>
</body>
</html>
