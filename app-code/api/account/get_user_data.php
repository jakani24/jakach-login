<?php
// Simulate fetching user data from a database
session_start();
header('Content-Type: application/json');
if($_SESSION["logged_in"]!==true){
	$data=[
		'status' => 'error',
		'message' => 'not logged in'
	];
	echo json_encode($user_data);
	exit();
}

include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

$username=$_SESSION["username"];
$sql="SELECT id, email, telegram_id, auth_method_enabled_2fa, user_token, login_message FROM users WHERE username = ?";
$id=0;
$email="";
$telegram_id="";
$twofa_enabled="";
$user_token="";
$login_message=0;
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt, $id,$email,$telegram_id,$twofa_enabled,$user_token,$login_message);
mysqli_stmt_fetch($stmt);

$_SESSION["id"]=$id;
// Sample user data
$user_data = [
    "name" => $username,
    "email" => $email,
    "telegram_id" => $telegram_id,
    "twofa_enabled" => $twofa_enabled,
    "user_token"=>$user_token,
    "last_login"=>$_SESSION["last_login"],
    "login_message"=>$login_message
];

// Send JSON response
echo json_encode($user_data);
?>
