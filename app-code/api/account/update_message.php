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
if($data->enable_message==true){
	$sql="UPDATE users SET login_message=1 WHERE id = ?";
	if ($update_stmt = $conn->prepare($sql)) {
            $update_stmt->bind_param("i", $id);
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Login messages enabled.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to enable login messages.']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
}

if($data->enable_message==false){
	//create 2fa secret key
	$sql="UPDATE users SET login_message=0 WHERE id = ?";
	if ($update_stmt = $conn->prepare($sql)) {
            $update_stmt->bind_param("i",$id);
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Login messages disabled.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to disable login messages.']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
}

?>

