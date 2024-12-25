<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];

include "../../config/config.php";
include "../utils/generate_pin.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

$username=$_SESSION["username"];
$sql="SELECT 2fa FROM users WHERE username = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$twofa_secret="";
mysqli_stmt_bind_result($stmt, $twofa_secret);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
$twofa_pin=$_POST["twofa_pin"];

if(generateTOTP($twofa_secret)===$twofa_pin){
	$_SESSION["mfa_authenticated"]=1;
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
