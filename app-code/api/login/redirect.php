<?php
session_start();
header('Content-Type: application/json');

include "../utils/get_location.php";

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
}*/
else if ($_SESSION["needs_auth"]===false && $_SESSION["mfa_authenticated"]==1 && $_SESSION["pw_authenticated"]==1 && $_SESSION["keepmeloggedin_asked"]==false){
	//send to keepmelogged in question
	$data=[
                'message' => 'ask_keepmeloggedin',
                'redirect' => '/login/keepmeloggedin.php'
        ];
        echo(json_encode($data));
}
else if ($_SESSION["needs_auth"]===false && $_SESSION["mfa_authenticated"]==1 && $_SESSION["pw_authenticated"]==1){
	//fully authenticated
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
		        'redirect' => '/account/'
		];
	}
	//update last login
	$ip=$_SERVER["HTTP_X_FORWARDED_FOR"];
	$date=date('Y-m-d H:i:s');
	$last_login_msg=$date." from ".$ip;
	$sql="UPDATE users SET last_login = ? WHERE id = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 'si', $last_login_msg,$user_id);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	//send login message
	if($_SESSION["login_message"] && $_SESSION["logged_in"]!==true){
		$device = $_SERVER['HTTP_USER_AGENT'];
		$location=get_location_from_ip($ip);
		$message = "⚠️ *Login Warning*\n\n"
		    . "We noticed a login attempt with your account.\n\n"
		    . "*Date&Time*: $date\n"
		    . "*Device&Browser*: $device\n"
		    . "*Location*: ".$location["country"].", ".$location["state"].", ".$location["city"]."\n"
		    . "*Account*: ".$_SESSION["username"]."\n"
		    . "*IP*: $ip\n\n"
		    . "If this was you, you can ignore this message. If not, please secure your account immediately.";

		// Telegram API URL
		$url = "https://api.telegram.org/$TELEGRAM_BOT_API/sendMessage";

		// Data to be sent in the POST request
		$telegram_id=$_SESSION["telegram_id"];
		$message_data = [
		    'chat_id' => $telegram_id,
		    'text' => $message,
		    'parse_mode' => 'Markdown', // Use Markdown for formatting
		];

		// Use cURL to send the request
		$ch = curl_init();

		// Construct the GET request URL
		$query_string = http_build_query($message_data); // Converts the array to URL-encoded query string
		$get_url = $url . '?' . $query_string; // Append query string to the base URL
		curl_setopt($ch, CURLOPT_URL, $get_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Still retrieve the response if needed
		curl_exec($ch);
		curl_close($ch);

	}
	
	$_SESSION["logged_in"]=true;
        echo(json_encode($data));
}
else{
	//we have to send the user around :)
	//load his auth methods. then send the first one. if he auths there he will be send back here and we can send him to the next auth method
	$username=$_SESSION["username"];
	$_SESSION["needs_auth"]=false;
	$_SESSION["logged_in"]=false;
	$sql="SELECT auth_method_required_pw, auth_method_required_2fa, auth_method_required_passkey, id, user_token,last_login, login_message,telegram_id, permissions FROM users WHERE username = ?";
	$stmt = mysqli_prepare($conn, $sql);
	mysqli_stmt_bind_param($stmt, 's', $username);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_store_result($stmt);
	$pw=0;
	$mfa=0;
	$passkey=0;
	$user_token="";
	$last_login="";
	$login_message=0;
	$telegram_id="";
	$permissions="";
	if(mysqli_stmt_num_rows($stmt) == 1){
		mysqli_stmt_bind_result($stmt, $pw,$mfa,$passkey,$user_id,$user_token,$last_login,$login_message,$telegram_id,$permissions);
		mysqli_stmt_fetch($stmt);
		$_SESSION["pw_required"] = $pw;
		$_SESSION["pw_authenticated"] = ($pw == 0) ? 1 : 0; // If $pw is 0, set pw_authenticated to 1
		$_SESSION["mfa_required"] = $mfa;
		$_SESSION["mfa_authenticated"] = ($mfa == 0) ? 1 : 0;
		$_SESSION["passkey_required"] = $passkey;
		$_SESSION["passkey_authenticated"] = ($passkey == 0) ? 1 : 0;
		$_SESSION["id"]=$user_id;
		$_SESSION["user_token"]=$user_token;
		$_SESSION["last_login"]=$last_login;
		$_SESSION["telegram_id"]=$telegram_id;
		$_SESSION["login_message"]=$login_message;
		$_SESSION["permissions"]=$permissions;
		$_SESSION["keepmeloggedin_asked"]=false;
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
