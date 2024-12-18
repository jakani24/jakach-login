<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];

include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

$username=$_SESSION["username"];
$sql="SELECT password,pepper FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$pw="";
$pepper="";
mysqli_stmt_bind_result($stmt, $pw,$pepper);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
$password=$_POST["password"];

if(password_verify($password.$pepper,$pw)){
	$_SESSION["pw_authenticated"]=1;
	$data = [
		'status' => 'success'
	];
	echo(json_encode($data));
}else{
        $data = [
                'status' => 'failure'
        ];
        echo(json_encode($data));

}


?>
