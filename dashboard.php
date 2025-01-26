<?php
include('helper/db.php');

// Ambil hasil voting dari database
$sql = "SELECT username, vote_count FROM users ORDER BY vote_count DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Voting</title>
</head>
<body>
    <h1>Hasil Voting</h1>
    <ul>
        <?php while($row = $result->fetch_assoc()): ?>
            <li><?php echo $row['username']; ?>: <?php echo $row['vote_count']; ?> Vote</li>
        <?php endwhile; ?>
    </ul>
    <br>
    <a href="index.php">Kembali ke Voting</a>

    <?php
    $conn->close();
    ?>
</body>
</html>
