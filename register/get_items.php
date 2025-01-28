<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Query untuk mendapatkan id, name, dan price dari tabel items
$sql = "SELECT id, name, description, price FROM items WHERE active = '1' AND stock > 0";
$result = $conn->query($sql);

// Cek apakah ada data
if ($result->num_rows > 0) {
    // Ambil data sebagai array asosiatif
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'description' => $row['description'],
            'price' => $row['price'],
        ];
    }
    // Kembalikan data dalam format JSON
    echo json_encode($items);
} else {
    // Jika tidak ada data
    echo json_encode([]);
}

// Menutup koneksi
$conn->close();
?>