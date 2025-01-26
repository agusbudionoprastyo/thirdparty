<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Pasangan</title>
    <script>
        // Membuat koneksi ke server untuk menerima SSE
        const eventSource = new EventSource('sse.php');

        // Ketika menerima pesan dari server
        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);  // Parse data JSON dari server
            const message = data.message;  // Ambil pesan yang dikirim

            // Tampilkan pesan di halaman
            const messageDiv = document.createElement('div');
            messageDiv.textContent = message;

            // Jika pasangan baru berhasil dibuat, tampilkan pasangan mereka
            if (data.male_username && data.female_username) {
                const matchDiv = document.createElement('div');
                matchDiv.textContent = `${data.male_username} dan ${data.female_username} berhasil dipasangkan!`;
                document.getElementById('matches').appendChild(matchDiv);
            }

            // Jika pasangan sedang dalam proses (session_completed = 0), tampilkan mereka
            if (data.male_username && data.female_username) {
                const processDiv = document.createElement('div');
                processDiv.textContent = `${data.male_username} dan ${data.female_username} sedang diproses...`;
                document.getElementById('matches').appendChild(processDiv);
            }

            document.body.appendChild(messageDiv);
        };

        // Menangani error jika koneksi SSE gagal
        eventSource.onerror = function(event) {
            console.error("Error with SSE connection:", event);
        };
    </script>
</head>
<body>
    <h1>Pasangan Baru</h1>
    <div id="matches"></div>
</body>
</html>