<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];
//if allready authenticated
if(($_SESSION["auth_passkey"]==="not_reauired" or $_SESSION["auth_passkey"]==="authenticated") and ($_SESSION["auth_password"]==="not_reauired" or $_SESSION["auth_password"]==="authenticated") and ($_SESSION["auth_2fa"]==="not_reauired" or $_SESSION["auth_2fa"]==="authenticated")){
	//user is fully authenticated, send him to the desired page
	$data = [
	    'login' => true,
	    'message' => 'fully_logged_in',
	    'redirect' => $send_to
	];
	echo(json_encode($data));
}else{
	//we have to send the user around :)
	//load his auth methods. then send the first one. if he auths there he will be send back here and we can send him to the next auth method
	$username=$_SESSION["username"];
}


?>
