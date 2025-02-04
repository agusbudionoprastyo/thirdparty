<?php
include '../helper/db.php';

// SQL query to fetch users data
$sql = "SELECT u.name, u.email, u.phone, u.photo, u.username, u.password, t.user_id, t.transaction_id, t.total_amount, t.payment_method, t.payment_prooft, t.transaction_date, t.status 
        FROM users u
        JOIN transactions t ON u.id = t.user_id";
$result = $conn->query($sql);

$users = [];
if ($result->num_rows > 0) {
    // Loop through all rows and push each to the array
    while($row = $result->fetch_assoc()) {
        // Dynamically set the badge class based on the transaction status
        switch ($row['status']) {
            case 'paid':
                $row['badge_class'] = 'bg-gradient-info';
                break;
            case 'pending':
                $row['badge_class'] = 'bg-gradient-warning';
                break;
            case 'confirm':
                $row['badge_class'] = 'bg-gradient-success';
                break;
            default:
                $row['badge_class'] = 'bg-gradient-secondary';  // Fallback for any other status
                break;
        }
        
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