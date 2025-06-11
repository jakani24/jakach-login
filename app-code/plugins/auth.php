<?php
/*
This file can be installed in any service. If done so a user can authenticate with Jakach Auth. Jakach Auth will redirect the user here where their token gets validated, and then they can be logged in to your service.
*/
$auth_token = $_GET["auth"];

// Check the auth token against Jakach login API
$check_url = "https://auth.jakach.ch/api/auth/check_auth_key.php?auth_token=" . $auth_token;

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $check_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute cURL and get the response
$response = curl_exec($ch);

// Check for cURL errors
if(curl_errno($ch)) {
    die("cURL Error: " . curl_error($ch));
}

// Close cURL
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);
// Check if the response contains a valid status
if (isset($data['status'])) {
    if ($data['status'] == "success") {
        // Successful authentication: login the user
        session_start();
        $_SESSION["username"] = $data["username"];
        $_SESSION["id"] = $data["id"];
        $_SESSION["email"] = $data["email"];
        $_SESSION["telegram_id"] = $data["telegram_id"];
        $_SESSION["user_token"] = $data["user_token"];
        
        // Return a success response
        echo json_encode(['status' => 'success', 'msg' => 'logged in']);
    } else {
        // Authentication failed
        echo json_encode(['status' => 'failure', 'msg' => $data["msg"]]);
    }
} else {
    // Invalid response format or missing status
    echo json_encode(['status' => 'failure', 'msg' => 'Invalid response from authentication server']);
}
?>

