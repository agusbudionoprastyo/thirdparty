<?php
include '../helper/db.php';

header('Content-Type: application/json');

$transaction_id = $_GET['transaction_id'];

if (isset($_FILES['paymentproof'])) {
    $file = $_FILES['paymentproof'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => 'File upload failed']);
        exit;
    }

    $uploadDir = '../users/paymentprooft/';
    $fileName = uniqid() . '-' . basename($file['name']);
    $uploadPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $sql = "UPDATE transactions SET status = 'paid', payment_prooft = ? WHERE transaction_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fileName, $transaction_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Bukti pembayaran berhasil diupload dan transaksi diperbarui.']);
        } else {
            echo json_encode(['error' => 'Gagal memperbarui transaksi']);
        }

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Gagal mengupload file']);
    }
} else {
    echo json_encode(['error' => 'Tidak ada file yang diupload']);
}

$conn->close();
?>