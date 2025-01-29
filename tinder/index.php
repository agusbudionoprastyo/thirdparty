<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tinder In Realife</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<section class="main" id="messages"></section>

<script>
    // Membuka koneksi SSE (Server-Sent Events)
    const eventSource = new EventSource('backend.php'); // Ganti dengan path PHP yang sesuai
    
    // Mendengarkan event dari server
    eventSource.onmessage = function(event) {
        const messageData = JSON.parse(event.data);  // Parse data yang dikirim dari server

        // Jika statusnya adalah 'waiting'
        if (messageData.status === 'waiting') {
            const male = messageData.male_user;
            const female = messageData.female_user;

            // Ambil elemen container pesan
            const messagesContainer = document.getElementById('messages');

            // Bersihkan pesan yang ada sebelumnya
            messagesContainer.innerHTML = '';

            // Buat HTML untuk profile card pria dan wanita
            const maleProfileCard = `
                <div class="profile-card">
                    <div class="image">
                        <img src="../users/${male.photo}" alt="" class="profile-pic">
                    </div>
                    <div class="data">
                        <h2>${male.username}</h2>
                        <span>${male.phone}</span>
                    </div>
                    <div class="row">
                        <div class="info">
                            <h3>Age</h3>
                            <span>${male.age}</span>
                        </div>
                        <div class="info">
                            <h3>Gender</h3>
                            <span>${male.gender}</span>
                        </div>
                    </div>
                </div>
            `;

            const femaleProfileCard = `
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
                </div>
            `;

            // Gabungkan kedua profile card dan masukkan ke dalam container
            messagesContainer.innerHTML = maleProfileCard + femaleProfileCard;
        }
    };

    // Tangani error jika ada masalah dengan koneksi SSE
    eventSource.onerror = function(error) {
        console.error("Error occurred:", error);
    };
</script>

</body>
</html>