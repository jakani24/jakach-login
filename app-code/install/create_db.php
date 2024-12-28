<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
	 <title>Jakach-Login install</title>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>We are creating the databases used in jakach-login, please stand by</h4>
                </div>
                <div class="card-body">
					<p>If the creation fails, please wait a minute and try again. The database server might still be starting at the time.</p>
					<br>
                </div>
			<?php
			$success=1;
			include "../config/config.php";

			// Create connection
			$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD);

			// Check connection
			if ($conn->connect_error) {
				$success=0;
				die("Connection failed: " . $conn->connect_error);
			}

			// Create database
			$sql = "CREATE DATABASE IF NOT EXISTS $DB_DATABASE";
			if ($conn->query($sql) === TRUE) {
				echo '<br><div class="alert alert-success" role="alert">
					Database created successfully!
					</div>';
			} else {
				$success=0;
				echo '<br><div class="alert alert-danger" role="alert">
						Error creating database: ' . $conn->error .'
				</div>';
			}

			$conn->close();

			// Connect to the new database
			$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD,$DB_DATABASE);

			// Check connection
			if ($conn->connect_error) {
				$success=0;
				die("Connection failed: " . $conn->connect_error);
			}

			// Create user table
			$sql="CREATE TABLE IF NOT EXISTS users (
			    id INT AUTO_INCREMENT PRIMARY KEY,
			    username VARCHAR(255) NOT NULL UNIQUE,
			    public_key TEXT DEFAULT '',
			    credential_id VARBINARY(255),
			    last_login VARCHAR(255),
			    login_message INT,
			    user_token VARCHAR(128),
			    counter INT DEFAULT 0,
			    2fa VARCHAR(255),
			    email VARCHAR(255),
			    password VARCHAR(500),
			    pepper VARCHAR(255),
			    telegram_id VARCHAR(255),
			    permissions VARCHAR(255),
			    color_profile INT,
			    auth_key VARCHAR(255),
			    auth_method_keepmeloggedin_enabled INT,
			    auth_method_enabled_2fa INT,
			    auth_method_enabled_pw INT,
			    auth_method_enabled_passkey INT,
			    auth_method_required_2fa INT,
			    auth_method_required_pw INT,
			    auth_method_required_passkey INT
			);";


			if ($conn->query($sql) === TRUE) {
				echo '<br><div class="alert alert-success" role="alert">
						Table users created successfully!
				</div>';
			} else {
				$success=0;
				echo '<br><div class="alert alert-danger" role="alert">
						Error creating table users: ' . $conn->error .'
				</div>';
			}
			
			
			
			$sql="CREATE TABLE IF NOT EXISTS auth_tokens (
			    id INT AUTO_INCREMENT PRIMARY KEY,
			    auth_token VARCHAR(256),
			    user_id INT
			);";


			if ($conn->query($sql) === TRUE) {
				echo '<br><div class="alert alert-success" role="alert">
						Table auth_tokens created successfully!
				</div>';
			} else {
				$success=0;
				echo '<br><div class="alert alert-danger" role="alert">
						Error creating auth_tokens users: ' . $conn->error .'
				</div>';
			}
			
			$sql="CREATE TABLE IF NOT EXISTS reset_tokens (
			    id INT AUTO_INCREMENT PRIMARY KEY,
			    auth_token VARCHAR(256),
			    user_id INT,
			    valid_until INT
			);";


			if ($conn->query($sql) === TRUE) {
				echo '<br><div class="alert alert-success" role="alert">
						Table reset_tokens created successfully!
				</div>';
			} else {
				$success=0;
				echo '<br><div class="alert alert-danger" role="alert">
						Error creating reset_tokens users: ' . $conn->error .'
				</div>';
			}
			
			$sql="CREATE TABLE IF NOT EXISTS keepmeloggedin (
			    id INT AUTO_INCREMENT PRIMARY KEY,
			    auth_token VARCHAR(256),
			    user_id INT,
			    agent VARCHAR(255)
			);";


			if ($conn->query($sql) === TRUE) {
				echo '<br><div class="alert alert-success" role="alert">
						Table keepmeloggedin created successfully!
				</div>';
			} else {
				$success=0;
				echo '<br><div class="alert alert-danger" role="alert">
						Error creating keepmeloggedin users: ' . $conn->error .'
				</div>';
			}
			


			if($success!==1){
				echo '<br><div class="alert alert-danger" role="alert">
						There was an error creating the databases. Please try again or contact support at: <a href="mailto:info.jakach@gmail.com">info.jakach@gmail.com</a>
				</div>';
			}else{
				echo '<br><div class="alert alert-success" role="alert">
						Database created successfully!
				</div>';
			}

			$conn->close();
			?>
			</div>
		</div>
    </div>
</div>
</body>
</html>
