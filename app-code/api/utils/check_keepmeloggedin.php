<?php
	function logmein(){
		$ret="failure";
		include "/var/www/html/config/config.php";
		$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
		if (isset($_COOKIE['auth_token'])) {
			$auth_token=$_COOKIE['auth_token'];
		    	$sql="SELECT user_id,agent FROM keepmeloggedin WHERE auth_token = ?";
		    	$user_id=0;
		    	$agent="";
			$stmt = mysqli_prepare($conn, $sql);
			mysqli_stmt_bind_param($stmt, 's',$auth_token);
			mysqli_stmt_execute($stmt);
			mysqli_stmt_store_result($stmt);
			if(mysqli_stmt_num_rows($stmt) == 1){
				mysqli_stmt_bind_result($stmt, $user_id,$agent);
				mysqli_stmt_fetch($stmt);
				mysqli_stmt_close($stmt);
				
				//load user data
				$sql="SELECT auth_method_required_pw, auth_method_required_2fa, auth_method_required_passkey, username, user_token,last_login, login_message,telegram_id, permissions FROM users WHERE id = ?";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param($stmt, 'i', $user_id);
				mysqli_stmt_execute($stmt);
				mysqli_stmt_store_result($stmt);
				$pw=0;
				$username="";
				$mfa=0;
				$passkey=0;
				$user_token="";
				$last_login="";
				$login_message=0;
				$telegram_id="";
				$permissions="";
				if(mysqli_stmt_num_rows($stmt) == 1){
					mysqli_stmt_bind_result($stmt, $pw,$mfa,$passkey,$username,$user_token,$last_login,$login_message,$telegram_id,$permissions);
					mysqli_stmt_fetch($stmt);
					$_SESSION["pw_required"] = $pw;
					$_SESSION["pw_authenticated"] = 1;
					$_SESSION["mfa_required"] = $mfa;
					$_SESSION["mfa_authenticated"] = 1;
					$_SESSION["passkey_required"] = $passkey;
					$_SESSION["passkey_authenticated"] = 1;
					$_SESSION["id"]=$user_id;
					$_SESSION["username"]=$username;
					$_SESSION["user_token"]=$user_token;
					$_SESSION["last_login"]=$last_login;
					$_SESSION["telegram_id"]=$telegram_id;
					//$_SESSION["login_message"]=$login_message;
					$_SESSION["login_message"]=false; // do not send a message if logged in via keepmeloggedin
					$_SESSION["permissions"]=$permissions;
					$_SESSION["keepmeloggedin_asked"]=true;
					$_SESSION["logged_in"]=true;
					$_SESSION["needs_auth"]=false;
					$ret="success";
				}
				mysqli_stmt_close($stmt);
			}else{
				mysqli_stmt_close($stmt);
			}
		}
		return $ret;
	}

?>
