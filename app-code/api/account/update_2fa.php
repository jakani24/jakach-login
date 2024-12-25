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
include "../utils/create_key.php";
include "../utils/generate_pin.php";

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
if($data->enable_2fa==true){
	//create 2fa secret key
	$twofa_secret=generateBase32Secret();
	$sql="UPDATE users SET 2fa = ?, auth_method_enabled_2fa = 1, auth_method_required_2fa = 1 WHERE id = ?";
	if ($update_stmt = $conn->prepare($sql)) {
            $update_stmt->bind_param("si", $twofa_secret, $id);
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => '2FA enabled. Your 2fa secret is: '.$twofa_secret.'']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to enable 2fa.']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
}

if($data->enable_2fa==false){
	//create 2fa secret key
	$sql="UPDATE users SET auth_method_enabled_2fa = 0, auth_method_required_2fa = 0 WHERE id = ?";
	if ($update_stmt = $conn->prepare($sql)) {
            $update_stmt->bind_param("i",$id);
            if ($update_stmt->execute()) {
                echo json_encode(['success' => true, 'message' => '2FA disabled.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to disable 2fa.']);
            }
            $update_stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error.']);
        }
}

?>

