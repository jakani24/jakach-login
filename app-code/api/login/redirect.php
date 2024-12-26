<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];

include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

 if($_SESSION["needs_auth"]===false && $_SESSION["pw_required"]==1 && $_SESSION["pw_authenticated"]==0){
//check for pw
	$data=[
                'message' => 'auth_pw',
		'redirect' => '/login/pw.php'
        ];
        echo(json_encode($data));
}
else if($_SESSION["needs_auth"]===false && $_SESSION["mfa_required"]==1 && $_SESSION["mfa_authenticated"]==0){
	$data=[
                'message' => 'auth_mfa',
                'redirect' => '/login/mfa.php'
        ];
        echo(json_encode($data));

//check for mfa
}
/*else if($_SESSION["needs_auth"]===false && $_SESSION["passkey_required"]==1 && $_SESSION["passkey_authenticated"]==0){
//check for passkey
	$data=[
                'message' => 'auth_passkey',
                'redirect' => '/login/passkey.php'
        ];
        echo(json_encode($data));
}*/else if ($_SESSION["needs_auth"]===false && $_SESSION["mfa_authenticated"]==1 && $_SESSION["pw_authenticated"]==1){
	//fully authenticated
	$_SESSION["logged_in"]=true;
	//create auth token which other services can then use to check if user logged in
	$user_id=$_SESSION["id"];
	$auth_token=bin2hex(random_bytes(128));
	$sql="INSERT INTO auth_tokens (auth_token,user_id) VALUES(?,?);";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 'si', $auth_token,$user_id);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	if(!empty($send_to)){
		$data=[
		        'message' => 'done',
		        'redirect' => $send_to."?auth=$auth_token"
		];
	}else{
		$data=[
		        'message' => 'done',
		        'redirect' => ''
		];
	}
        echo(json_encode($data));
}
else{
	//we have to send the user around :)
	//load his auth methods. then send the first one. if he auths there he will be send back here and we can send him to the next auth method
	$username=$_SESSION["username"];
	$_SESSION["needs_auth"]=false;
	$_SESSION["logged_in"]=false;
	$sql="SELECT auth_method_required_pw, auth_method_required_2fa, auth_method_required_passkey, id, user_token FROM users WHERE username = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 's', $username);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_store_result($stmt);
	$pw=0;
	$mfa=0;
	$passkey=0;
	$user_token="";
	if(mysqli_stmt_num_rows($stmt) == 1){
		mysqli_stmt_bind_result($stmt, $pw,$mfa,$passkey,$user_id,$user_token);
		mysqli_stmt_fetch($stmt);
		$_SESSION["pw_required"] = $pw;
		$_SESSION["pw_authenticated"] = ($pw == 0) ? 1 : 0; // If $pw is 0, set pw_authenticated to 1
		$_SESSION["mfa_required"] = $mfa;
		$_SESSION["mfa_authenticated"] = ($mfa == 0) ? 1 : 0;
		$_SESSION["passkey_required"] = $passkey;
		$_SESSION["passkey_authenticated"] = ($passkey == 0) ? 1 : 0;
		$_SESSION["id"]=$user_id;
		$_SESSION["user_token"]=$user_token;
		$data=[
			'message' => 'prepared_start_auth',
			'redirect' => '/login/'
		];
		echo(json_encode($data));
	}else{
		$data = [
			'message' => 'this user does not exist',
			'redirect' => '/?user_not_found'
		];
		echo(json_encode($data));
	}
	mysqli_stmt_close($stmt);

}


?>
