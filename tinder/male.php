<?php
require_once '../helper/db.php';

if (isset($_POST['vote'])) {
    $vote = $_POST['vote']; // 'like' atau 'dislike'

    // Query untuk mengambil pasangan pria dan wanita yang sedang diproses
    $sql = "SELECT * FROM matches WHERE session_completed = 0 LIMIT 1"; // Mengambil satu pasangan yang sedang diproses
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $match = $result->fetch_assoc();
        $male_user_id = $match['male_user_id'];
        $female_user_id = $match['female_user_id'];

        // Update vote untuk pasangan pria (male_vote)
        if (in_array($vote, ['like', 'dislike'])) {
            $update_sql = "UPDATE matches SET female_vote = '$vote' WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
            if ($conn->query($update_sql) === TRUE) {
                // echo "Vote berhasil diberikan: " . ucfirst($vote) . "!<br>";
            } else {
                echo "Error: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Male</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<!-- <div id="match-details"></div> -->
<section class="main" id="match-details"></section>

    <script>
        // Membuka koneksi SSE
        const eventSource = new EventSource('backend.php'); // Pastikan path sesuai dengan backend SSE yang kamu buat

        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            
            // Pastikan ada data pasangan pria dan wanita
            if (data.status === 'waiting') {
                const male = data.male_user;
                const female = data.female_user;

                // Menampilkan detail pasangan
                const matchDetails = document.getElementById('match-details');
                matchDetails.innerHTML = `
                <div class="profile-card">
                        <div class="image">
                            <img src="../users/${female.photo}" alt="" class="profile-pic">
                        </div>
                    <div class="data">
                        <h2>${female.username}</h2>
                        <span>${female.phone}</span>
                    </div>
                    <div class="row">
                        <div class="info">
                            <h3>Age</h3>
                            <span>${female.age}</span>
                        </div>
                        <div class="info">
                            <h3>Gender</h3>
                            <span>${female.gender}</span>
                        </div>
                    </div>
                    <form method="POST" action="male.php">
                        <div class="buttons">
                            <button class="btn" type="submit" name="vote" value="like">LIKE</a>
                            <button class="btn" type="submit" name="vote" value="dislike">DISLIKE</a>
                        </div>
                    </form>
                </div>
                `;
            }
        };

        eventSource.onerror = function(error) {
            console.error("Error occurred:", error);
        };
    </script>
</body>
</html>