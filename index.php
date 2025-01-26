<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinder In Realife</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
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
                <div class="profile-card">
                    <div class="image">
                        <img src="assets/images/male.jpg" alt="" class="profile-pic">
                    </div>
                    <div class="data">
                        <h2>${male.username}</h2>
                        <span>${male.gender}</span>
                    </div>
                    <div class="row">
                        <div class="info">
                            <h3>Age</h3>
                            <span>${male.age}</span>
                        </div>
                        <div class="info">
                            <h3>City</h3>
                            <span>${male.city}</span>
                        </div>
                    </div>
                </div>
                <div class="profile-card">
                        <div class="image">
                            <img src="assets/images/female.jpg" alt="" class="profile-pic">
                        </div>
                    <div class="data">
                        <h2>${female.username}</h2>
                        <span>${female.gender}</span>
                    </div>
                    <div class="row">
                        <div class="info">
                            <h3>Age</h3>
                            <span>${female.age}</span>
                        </div>
                        <div class="info">
                            <h3>City</h3>
                            <span>${female.city}</span>
                        </div>
                    </div>
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