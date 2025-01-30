<?php
include '../helper/db.php';

// SQL query to fetch users data
$sql = "SELECT u.name, u.email, u.phone, u.photo, u.username, u.password, t.transaction_id, t.total_amount, t.payment_method, t.payment_prooft, t.transaction_date, t.status 
        FROM users u
        JOIN transactions t ON u.id = t.user_id
        ";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    // Loop through all rows and push each to the array
    while($row = $result->fetch_assoc()) {
        $row['badge_class'] = ($row['status'] === 'Online') ? 'bg-gradient-success' : 'bg-gradient-secondary';
        $users[] = $row;
    }
} else {
    $users = [];
}

// Set the header to JSON and output the data
header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>