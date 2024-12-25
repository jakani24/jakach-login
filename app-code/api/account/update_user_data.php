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

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate the input
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['telegram_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input: Missing fields'
        ]);
        exit();
    }

    // Sanitize and validate the input
    $name = preg_replace("/[^a-zA-Z0-9_]/", "", $data['name']); // Allow only letters, numbers, and underscores
    $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL); // Sanitize email
    $telegram_id = htmlspecialchars($data['telegram_id'], ENT_QUOTES, 'UTF-8'); // Escape special characters

    //check if username is allready taken
    $id_check=0;
    $sql="SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $name);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $id_check);
    mysqli_stmt_fetch($stmt);
    if(mysqli_stmt_num_rows($stmt) > 0 && $username!==$name){
	//this username is allready taken
	echo json_encode([
            'success' => false,
            'message' => 'Username allready taken. Please choose another username.'
        ]);
        exit();

    }
    mysqli_stmt_close($stmt);
    // Prepare the SQL query
    $sql = "UPDATE users SET email = ?, username = ?, telegram_id = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Check if the statement was prepared successfully
    if (!$stmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to prepare statement: ' . $conn->error
        ]);
        exit();
    }

    // Bind parameters to the prepared statement
    $stmt->bind_param('sssi', $email, $name, $telegram_id, $id);

    // Execute the statement
    if ($stmt->execute()) {
        // Respond with success
        echo json_encode([
            'success' => true,
            'message' => 'User data updated successfully'
        ]);
	$_SESSION["username"]=$name;
    } else {
        // Respond with failure
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update user data: ' . $stmt->error
        ]);
    }

    // Close the statement
    $stmt->close();
} else {
    // Respond with error for invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

// Close the database connection
$conn->close();
?>
