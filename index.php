<?php
// Menggunakan file db.php untuk koneksi database
require_once 'helper/db.php';

// Query untuk mengambil pasangan pertama (male_user_id dan female_user_id)
$sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Ambil data pasangan
    $row = $result->fetch_assoc();
    $male_user_id = $row['male_user_id'];
    $female_user_id = $row['female_user_id'];

    // Ambil data user male
    $male_sql = "SELECT * FROM users WHERE id = $male_user_id";
    $male_result = $conn->query($male_sql);
    $male = $male_result->fetch_assoc();

    // Ambil data user female
    $female_sql = "SELECT * FROM users WHERE id = $female_user_id";
    $female_result = $conn->query($female_sql);
    $female = $female_result->fetch_assoc();
} else {
    echo "No pair available to generate.";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Match</title>
</head>
<body>
    <h1>Generate Match</h1>
    <?php if ($result->num_rows > 0): ?>
        <p>Pasangan yang ditemukan:</p>
        <p>Laki-laki: <?php echo $male['username']; ?> (<?php echo $male['gender']; ?>)</p>
        <p>Perempuan: <?php echo $female['username']; ?> (<?php echo $female['gender']; ?>)</p>
        <a href="male.php?user_id=<?php echo $male_user_id; ?>&pair_id=<?php echo $female_user_id; ?>">Generate</a>
    <?php else: ?>
        <p>Belum ada pasangan untuk di-generate.</p>
    <?php endif; ?>
</body>
</html>

<?php $conn->close(); ?>