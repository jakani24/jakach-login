<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] !== true) {
    echo json_encode([
        'success' => false,
        'message' => 'Not logged in'
    ]);
    exit();
}

// Include database configuration
include "../../config/config.php";

// Create a new database connection
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

// Check for database connection errors
if ($conn->connect_error) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// Get the logged-in user's ID and username from the session
$id = $_SESSION["id"];
$username = $_SESSION["username"];

// Get the raw POST data (JSON)
$data = json_decode(file_get_contents("php://input"));

// Check if the required fields are present
if (isset($data->old_password) && isset($data->new_password)) {
    // Get the user ID (this should be taken from the session or JWT token)
    session_start();
    $user_id = $_SESSION['id']; // Assuming user_id is stored in session

    // Sanitize inputs
    $old_password = htmlspecialchars($data->old_password);
    $new_password = htmlspecialchars($data->new_password);
    
    // Check password strength (optional but recommended)
    if (strlen($new_password) < 12) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 12 characters long.']);
        exit;
    }

    // Fetch the current password from the database
    $sql = "SELECT password, pepper FROM users WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($current_password, $current_pepper);
        
        if ($stmt->fetch()) {
            // Verify the old password
            if (password_verify($old_password.$current_pepper, $current_password)) {
                // Hash the new password
                $new_pepper=bin2hex(random_bytes(32));
    		// Hash the password / a salt is added automaticly
    		$hashed_password = password_hash($new_password.$new_pepper, PASSWORD_BCRYPT);

                // Update the password in the database
                $update_sql = "UPDATE users SET password = ?, pepper = ? WHERE id = ?";
                if ($update_stmt = $conn->prepare($update_sql)) {
                    $update_stmt->bind_param("ssi", $hashed_password, $new_pepper, $user_id);
                    if ($update_stmt->execute()) {
                        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
                    }
                    $update_stmt->close();
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Old password is incorrect.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found.']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }

    // Close the database connection
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
}
?>

