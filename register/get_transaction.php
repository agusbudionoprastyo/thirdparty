<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Ambil transaction_id dari parameter GET
$transaction_id = isset($_GET['transaction_id']) ? $_GET['transaction_id'] : '';

if (empty($transaction_id)) {
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

echo "Received transaction_id: " . $transaction_id;  // Debugging output

// Query untuk mendapatkan total_amount dan transaction_date berdasarkan transaction_id
$sql = "SELECT total_amount, transaction_date FROM transactions WHERE transaction_id = ?";

// Menyiapkan statement
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $transaction_id);  // Mengikat parameter untuk mencegah SQL injection

    // Menjalankan query
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        // Cek apakah ada hasil
        if ($result->num_rows > 0) {
            $transactionDetails = $result->fetch_assoc();
            echo json_encode($transactionDetails);
        } else {
            echo json_encode(['error' => 'Transaction not found']);
        }
    } else {
        echo json_encode(['error' => 'Failed to execute query']);
    }
} else {
    echo json_encode(['error' => 'Failed to prepare statement']);
}

// Menutup koneksi
$conn->close();
?>