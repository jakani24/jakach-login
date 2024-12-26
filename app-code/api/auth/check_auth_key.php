<?php
header('Content-Type: application/json');
include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

$auth_key=$_GET["auth_token"];
$sql="SELECT user_id FROM auth_tokens WHERE auth_token = ?;";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $auth_key);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
//if auth key is valid
if(mysqli_stmt_num_rows($stmt) == 1){
	$user_id=0;
	mysqli_stmt_bind_result($stmt,$user_id);
	mysqli_stmt_fetch($stmt);
	//we now have userid, close stmt
	mysqli_stmt_close($stmt);
	
	$sql="SELECT username, email, telegram_id, user_token FROM users WHERE id = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 'i', $user_id);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_store_result($stmt);
	$username="";
	$email="";
	$telegram="";
	$user_token="";
	mysqli_stmt_bind_result($stmt,$username,$email,$telegram,$user_token);
	mysqli_stmt_fetch($stmt);
	mysqli_stmt_close($stmt);
	$data=[
		'status'=>'success',
		'msg'=>'user authenticated',
		'username'=>$username,
		'email'=>$email,
		'telegram_id'=>$telegram,
		'id'=>$user_id,
		'user_token'=>$user_token
	];
	
	//remove auth key
	$sql="DELETE FROM auth_tokens WHERE auth_token = ?;";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 's', $auth_key);
	mysqli_stmt_execute($stmt);
	echo(json_encode($data));
}else{
	$data=[
		'status' => 'failure',
		'msg'=>'invalid auth key',
		'auth_key'=>$auth_key
	];
	echo(json_encode($data));
}

?>
