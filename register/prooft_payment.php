<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Ambil transaction_id dari parameter GET
$transaction_id = $_GET['transaction_id'];

if (empty($transaction_id)) {
    echo json_encode(['error' => 'Transaction ID is required']);
    exit;
}

// Cek apakah ada file yang diupload
if (isset($_FILES['paymentproof']) && $_FILES['paymentproof']['error'] == UPLOAD_ERR_OK) {
    // Set direktori tujuan untuk menyimpan file
    $uploadDir = '../users/';
    $uploadedFile = $uploadDir . basename($_FILES['paymentproof']['name']);

    // Validasi apakah file yang diupload adalah file gambar atau PDF (untuk bukti pembayaran)
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    $fileExtension = pathinfo($uploadedFile, PATHINFO_EXTENSION);

    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        echo json_encode(['error' => 'Invalid file type. Only image or PDF allowed.']);
        exit;
    }

    // Proses upload file
    if (move_uploaded_file($_FILES['paymentproof']['tmp_name'], $uploadedFile)) {
        // Update status transaksi dan bukti pembayaran di database
        $sql = "UPDATE transactions SET status = 'completed', payment_proof = ? WHERE transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $uploadedFile, $transaction_id);  // File path dan transaction_id
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => 'Payment proof uploaded and transaction updated']);
        } else {
            echo json_encode(['error' => 'Transaction update failed']);
        }
    } else {
        echo json_encode(['error' => 'File upload failed']);
    }
} else {
    echo json_encode(['error' => 'No file uploaded or there was an upload error']);
}

// Menutup koneksi
$conn->close();
?>
