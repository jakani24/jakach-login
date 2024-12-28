<?php
// Check if the POST request contains 'token' and 'password'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
        exit;
    }
    include "../../config/config.php";
 
    // Create a new database connection
    $conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);

    $token = $_POST['token'];
    $user_id="";
    $valid_until=0;
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $sql="SELECT user_id, valid_until FROM reset_tokens WHERE auth_token=?;";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $user_id,$valid_until);
    mysqli_stmt_fetch($stmt);
    if(mysqli_stmt_num_rows($stmt) > 0 && time()<$valid_until){
    	mysqli_stmt_close($stmt);
    	// Check if passwords match
		if ($password !== $confirmPassword) {
			echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
			exit;
		}
		if (strlen($password)<12) {
			echo json_encode(['status' => 'error', 'message' => 'Password must be at least 12 characters.']);
			exit;
		}

	   	 $new_pepper=bin2hex(random_bytes(32));
		// Hash the password / a salt is added automaticly
		$hashed_password = password_hash($password.$new_pepper, PASSWORD_BCRYPT);

		// Update the password in the database
		$update_sql = "UPDATE users SET password = ?, pepper = ? WHERE id = ?";
		if ($update_stmt = $conn->prepare($update_sql)) {
		    $update_stmt->bind_param("ssi", $hashed_password, $new_pepper, $user_id);
		    if ($update_stmt->execute()) {
			echo json_encode(['status' => 'success','success' => true, 'message' => 'Password updated successfully.']);
		    } else {
			echo json_encode(['success' => false, 'message' => 'Failed to update password.']);
		    }
		    $update_stmt->close();
		} else {
		    echo json_encode(['success' => false, 'message' => 'Database error.']);
		}
    }else {
    	mysqli_stmt_close($stmt);
    	echo json_encode(['success' => false, 'message' => 'Invalid auth token']);
    }
    //remove token
    $sql="DELETE FROM reset_tokens WHERE auth_token = ?;";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $token);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
	
} else {
    // If it's not a POST request, show error
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

