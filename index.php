<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pasangan - Real-Time</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        h1 {
            text-align: center;
        }
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }
        th {
            background-color: #f4f4f4;
        }
        .no-match {
            color: red;
            text-align: center;
            font-size: 16px;
        }
        .matched {
            color: green;
            text-align: center;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <h1>Detail Pasangan yang Belum Diproses</h1>
    
    <!-- Tabel untuk menampilkan detail pasangan yang belum diproses -->
    <table id="matchTable">
        <thead>
            <tr>
                <th>ID Pasangan</th>
                <th>Pasangan Pria</th>
                <th>Pasangan Wanita</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data pasangan yang belum diproses akan ditampilkan di sini -->
        </tbody>
    </table>

    <p id="statusMessage" class="no-match">Menunggu pasangan yang belum diproses...</p>

    <script>
        // Membuat koneksi ke SSE
        const eventSource = new EventSource('sse.php');
        
        // Menerima pesan dari server
        eventSource.onmessage = function(event) {
            const message = event.data;
            const matchTableBody = document.getElementById('matchTable').getElementsByTagName('tbody')[0];
            const statusMessage = document.getElementById('statusMessage');
            
            // Cek apakah ada pasangan yang belum diproses
            if (message.includes("Pasangan belum diproses")) {
                // Menampilkan detail pasangan yang belum diproses dalam tabel
                const matchDetails = message.split(": ")[1]; // Pisahkan "Pasangan belum diproses" dengan detail
                const matchParts = matchDetails.split(", ");

                const male = matchParts[0].split(": ")[1]; // Nama Pria
                const female = matchParts[1].split(": ")[1]; // Nama Wanita
                const maleId = matchParts[0].split("(")[1].split(")")[0]; // ID Pria
                const femaleId = matchParts[1].split("(")[1].split(")")[0]; // ID Wanita

                // Menambahkan baris baru di tabel
                const newRow = matchTableBody.insertRow();
                newRow.innerHTML = `
                    <td>${maleId} - ${femaleId}</td>
                    <td>${male}</td>
                    <td>${female}</td>
                    <td>Menunggu Voting</td>
                `;
                
                // Tampilkan status "Pasangan belum diproses"
                statusMessage.classList.remove("no-match");
                statusMessage.classList.add("matched");
                statusMessage.textContent = "Ada pasangan yang belum diproses";
            } else if (message === "Tidak ada pasangan yang belum diproses.") {
                // Jika tidak ada pasangan yang belum diproses
                statusMessage.classList.remove("matched");
                statusMessage.classList.add("no-match");
                statusMessage.textContent = "Tidak ada pasangan yang belum diproses.";
            } else {
                // Pesan lainnya
                console.log(message);
            }
        };

        // Menangani error SSE
        eventSource.onerror = function() {
            console.log('Error dalam koneksi SSE');
            eventSource.close();
        };
    </script>
</body>
</html>