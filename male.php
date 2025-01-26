<?php
// backend.php sudah mengirimkan data pasangan melalui SSE.
// Kami ingin agar halaman ini menerima data dan menampilkan form voting.
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
    <h1>Vote Pasangan Pria</h1>

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
                        <strong>Wanita:</strong><br>
                        Username: ${female.username}<br>
                        Gender: ${female.gender}<br>
                        Age: ${female.age}<br>
                        City: ${female.city}<br>
                    </div>
                    <div class="user-details">
                        <strong>Pria:</strong><br>
                        Username: ${male.username}<br>
                        Gender: ${male.gender}<br>
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