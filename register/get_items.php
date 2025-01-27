<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Query untuk mengambil data items dari tabel
$sql = "SELECT id, name FROM items";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kembalikan data sebagai JSON
echo json_encode($items);
?>
