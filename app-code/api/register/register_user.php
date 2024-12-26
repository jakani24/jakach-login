<?php
// Set response headers to return JSON
header('Content-Type: application/json');

include "../../config/config.php";
// Connect to the database
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

// Check the connection
if ($conn === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . mysqli_connect_error()
    ]);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validate input
    if (!isset($data['username']) ||  !isset($data['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid input. Username and password are required.'
        ]);
        exit;
    }

    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = trim($data['password']);
    $telegram_id = trim($data['telegram']);

    // Check for empty fields
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required.'
        ]);
        exit;
    }

    // Check if the username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Username already taken.'
        ]);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Check if the email already exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0 && $email!="") {
        echo json_encode([
            'success' => false,
            'message' => 'Email already registered.'
        ]);
        mysqli_stmt_close($stmt);
        exit;
    }
    mysqli_stmt_close($stmt);

    $pepper=bin2hex(random_bytes(32));
    // Hash the password / a salt is added automaticly
    $hashedPassword = password_hash($password.$pepper, PASSWORD_BCRYPT);
    
    //random token which is used to auth users even if they change theyr username
    $user_token=bin2hex(random_bytes(32));

    // Insert the user into the database
    $sql = "INSERT INTO users (username, email, password, telegram_id, pepper, auth_method_enabled_pw, auth_method_required_pw, auth_method_enabled_passkey, auth_method_required_passkey, auth_method_enabled_2fa, auth_method_required_2fa,auth_method_keepmeloggedin_enabled, user_token) VALUES (?, ?, ?, ?, ?, 1, 1,0,0,0,0,0,?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'ssssss', $username, $email, $hashedPassword, $telegram_id, $pepper,$user_token);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            'success' => true,
            'message' => 'Registration successful!'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Registration failed. Please try again later.'
        ]);
    }
    mysqli_stmt_close($stmt);
} else {
    // Invalid request method
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST is allowed.'
    ]);
}

// Close the database connection
mysqli_close($conn);
?>
