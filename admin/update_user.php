<?php
include '../helper/db.php';

// Set the header for JSON response
header('Content-Type: application/json');

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if the necessary fields are provided
if (isset($data['user_id']) && isset($data['status'])) {
    $user_id = $data['user_id'];
    $status = $data['status'];

    // Prepare the SQL update statement to change the status
    $sql = "UPDATE transactions SET status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    
    // Bind the parameters
    $stmt->bind_param('si', $status, $user_id);
    
    // Execute the statement and check for success
    if ($stmt->execute()) {
        // Return a success response
        echo json_encode(['success' => true]);
    } else {
        // Return an error response if the update failed
        echo json_encode(['success' => false, 'message' => 'Failed to update status']);
    }
    
    // Close the prepared statement
    $stmt->close();
} else {
    // Return an error response if required parameters are missing
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
}

$conn->close();
?>
