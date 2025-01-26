<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pasangan Matchmaking</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        #messages {
            margin-top: 20px;
        }
        .message {
            padding: 10px;
            background-color: #f4f4f4;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .user-details {
            margin-top: 10px;
            padding: 10px;
            background-color: #e9e9e9;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

    <h1>Pasangan Matchmaking</h1>
    <div id="messages"></div>

    <script>
        // Membuka koneksi SSE
        const eventSource = new EventSource('backend.php'); // Ganti dengan path PHP yang sesuai
        
        // Mendengarkan event dari server
        eventSource.onmessage = function(event) {
            const messageData = JSON.parse(event.data);

            if (messageData.status === 'waiting') {
                const male = messageData.male_user;
                const female = messageData.female_user;

                // Ambil elemen untuk menampilkan pesan
                const messagesContainer = document.getElementById('messages');

                // Bersihkan pesan yang ada sebelumnya
                messagesContainer.innerHTML = '';

                // Buat elemen pesan baru
                const messageElement = document.createElement('div');
                messageElement.classList.add('message');
                messageElement.innerHTML = `
                    <strong>Pasangan Baru:</strong><br>
                    <div class="user-details">
                        <strong>Pria:</strong><br>
                        Username: ${male.username}<br>
                        Gender: ${male.gender}<br>
                        Age: ${male.age}<br>
                        City: ${male.city}<br>
                    </div>
                    <div class="user-details">
                        <strong>Wanita:</strong><br>
                        Username: ${female.username}<br>
                        Gender: ${female.gender}<br>
                        Age: ${female.age}<br>
                        City: ${female.city}<br>
                    </div>
                `;
                messagesContainer.appendChild(messageElement);
            }
        };

        eventSource.onerror = function(error) {
            console.error("Error occurred:", error);
        };
    </script>
</body>
</html>