<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Ambil transaction_id dari parameter GET
$transaction_id = $_GET['transaction_id'];

if (empty($transaction_id)) {
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

// Query untuk mendapatkan detail transaksi berdasarkan transaction_id
$sql = "SELECT total_amount, transaction_date FROM transactions WHERE transaction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $transaction_id);  // Menggunakan parameter bind untuk mencegah SQL injection
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah ada data
if ($result->num_rows > 0) {
    $transactionDetails = [];
    while ($row = $result->fetch_assoc()) {
        $transactionDetails[] = [
            'total_amount' => $row['total_amount'],
            'transaction_date' => $row['transaction_date']
        ];
    }
    // Mengembalikan data dalam format JSON
    echo json_encode($transactionDetails);
} else {
    // Jika tidak ada data, kirimkan error dalam format JSON
    echo json_encode(['error' => 'Transaction not found']);
}

// Menutup koneksi
$conn->close();
?>