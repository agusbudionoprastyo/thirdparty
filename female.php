<?php
require_once 'helper/db.php';

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
            $update_sql = "UPDATE matches SET male_vote = '$vote' WHERE male_user_id = $male_user_id AND female_user_id = $female_user_id";
            if ($conn->query($update_sql) === TRUE) {
                echo "Vote berhasil diberikan: " . ucfirst($vote) . "!<br>";
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
    <title>Vote - Male</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .user-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Female Vote</h1>

    <div id="match-details"></div> <!-- Tempat untuk menampilkan pasangan -->

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
                    <h2>Pasangan yang Ditemukan</h2>
                    <div class="user-details">
                        <strong>Male</strong><br>
                        Username: ${male.username}<br>
                        Gender: ${male.gender}<br>
                        Age: ${male.age}<br>
                        City: ${male.city}<br>
                    </div>
                    <div class="user-details">
                        <strong>Female:</strong><br>
                        Username: ${female.username}<br>
                        Gender: ${female.gender}<br>
                    </div>
                    <!-- Form untuk memberi vote -->
                    <form method="POST" action="male.php">
                        <button type="submit" name="vote" value="like">Like</button>
                        <button type="submit" name="vote" value="dislike">Dislike</button>
                    </form>
                `;
            }
        };

        eventSource.onerror = function(error) {
            console.error("Error occurred:", error);
        };
    </script>

</body>
</html>