<?php
session_start();
header('Content-Type: application/json');
include "../../config/config.php";
include "../utils/get_location.php";
$username=$_SESSION["username"];
$sql="SELECT id, email, telegram_id FROM users WHERE username = ?;";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
$mail="";
$id="";
$telegram_id="";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
mysqli_stmt_bind_result($stmt,$id, $mail,$telegram_id);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);
//send telegram message
$device = $_SERVER['HTTP_USER_AGENT'];
$ip=$_SERVER["REMOTE_ADDR"];
$location=get_location_from_ip($ip);
$date=date('Y-m-d H:i:s');
$token=bin2hex(random_bytes(128));
$link="https://auth.jakach.ch:444/login/reset_pw.php?token=$token";

$message = "*Password reset token*\n\n"
    . "You have requested the reset of your password here is your reset link.\n\n"
    . "*Link*: [click here]($link)\n\n"
    . "*Details of this request:*\n"
    . "• *Date&Time*: $date\n"
    . "• *Device&Browser*: $device\n"
    . "*Location*: ".$location["country"].", ".$location["state"].", ".$location["city"]."\n"
    . "• *Account*: ".$_SESSION["username"]."\n"
    . "• *IP*: $ip\n\n"
    ."If this was you, you can reset your password. If this was not you somebody else tried to reset your password!\n"
    . "*Thank you for using Jakach login!*";

// Telegram API URL
$url = "https://api.telegram.org/$TELEGRAM_BOT_API/sendMessage";

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
//send mail
if(!empty($mail)){
	$loc=$location["country"].", ".$location["state"].", ".$location["city"];
	$content = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            font-size: 24px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }
        a {
            color: #ffffff;
            background-color: #007bff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
        }
        a:hover {
            background-color: #0056b3;
        }
        .footer {
            font-size: 14px;
            color: #888;
            text-align: center;
            margin-top: 20px;
        }
        .footer a {
            color: #888;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class='email-container'>
        <h1>Password Reset Request</h1>
        <p>Hi $mail,</p>
        <p>You have requested a password reset link. Here it is:</p>
        <p><a href='$link'>Click here to reset your password</a></p>
        <p>If you did not request this, please ignore this email. If you did, you can reset your password using the link above.</p>
        
        <p><strong>Request Details:</strong></p>
        <ul>
            <li><strong>Date & Time:</strong> $date</li>
            <li><strong>Device & Browser:</strong> $device</li>
            <li><strong>Account:</strong> $mail</li>
            <li><strong>IP Address:</strong> $ip</li>
            <li><strong>Location:</strong> $loc</li>
        </ul>
        
        <p>If this was you, you can reset your password. If this was not you, someone else may have tried to reset your password.</p>
        
        <div class='footer'>
            <p>Thanks for using our service!</p>
        </div>
    </div>

</body>
</html>
";


	$message = [
	    "personalizations" => [
		[
		    "to" => [
		        [
		            "email" => $mail
		        ]
		    ]
		]
	    ],
	    "from" => [
		"email" => $SENDGRID_MAIL
	    ],
	    "subject" => "Jakach login password reset",
	    "content" => [
		[
		    "type" => "text/html",
		    "value" => $content
		]
	    ]
	];
	
	$url = "https://api.sendgrid.com/v3/mail/send";

	// Initialize cURL
	$ch = curl_init($url);

	// Set cURL options
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
	    "Authorization: Bearer $SENDGRID_KEY", 
	    "Content-Type: application/json"
	]);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));

	// Execute the cURL request
	curl_exec($ch);
	curl_close($ch);
}


//insert the token into our db
$valid_until=time()+(8600/2);
$sql="INSERT INTO reset_tokens (auth_token, user_id,valid_until) VALUES (?,?,?);";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'sii', $token,$id,$valid_until);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

?>
