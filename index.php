<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SSE Demo</title>
    <style>
        #messages {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            min-height: 50px;
            background-color: #f9f9f9;
        }
        .message {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>Server-Sent Events (SSE) - Status Pasangan</h1>
    
    <!-- Tempat untuk menampilkan pesan dari server -->
    <div id="messages"></div>

    <script>
        // Membuat koneksi ke SSE
        const eventSource = new EventSource('sse.php');
        
        // Menerima pesan dari server
        eventSource.onmessage = function(event) {
            const messageDiv = document.getElementById('messages');
            
            // Menampilkan pesan ke dalam div
            const newMessage = document.createElement('div');
            newMessage.classList.add('message');
            newMessage.textContent = event.data;
            messageDiv.appendChild(newMessage);
            
            // Scroll ke bagian bawah agar pesan terbaru terlihat
            messageDiv.scrollTop = messageDiv.scrollHeight;
        };

        // Menangani error SSE
        eventSource.onerror = function() {
            console.log('Error dalam koneksi SSE');
            eventSource.close();
        };
    </script>
</body>
</html>