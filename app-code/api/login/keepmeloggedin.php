<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];

include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
$keepmeloggedin=$_POST["keepmeloggedin"];
if($keepmeloggedin=="true"){
	$_SESSION["keepmeloggedin_asked"]=true;
	$user_id=$_SESSION["id"];
	
	//create a login token
	$login_token=bin2hex(random_bytes(128));
	$agent=$_SERVER['HTTP_USER_AGENT'];
	$sql="INSERT INTO keepmeloggedin (auth_token,user_id,agent) VALUES (?,?,?);";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 'sis', $login_token,$user_id,$agent);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	setcookie("auth_token", $login_token, time() + (30 * 24 * 60 * 60), "/", "", true, true);
	$data = [
		'status' => 'success'
	];
	echo(json_encode($data));

}else{
	$_SESSION["keepmeloggedin_asked"]=true;
	$data = [
		'status' => 'success'
	];
	echo(json_encode($data));
}


?>
