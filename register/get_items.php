<?php
include '../helper/db.php';

header('Content-Type: application/json');

// Query to get items from the table
$sql = "SELECT id, name FROM items";
$result = $conn->query($sql);

// Check if there are any items
if ($result->num_rows > 0) {
    // Fetch all rows as associative array
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    // Return the data as JSON
    echo json_encode($items);
} else {
    // If no items found
    echo json_encode([]);
}

// Close the connection
$conn->close();
?>