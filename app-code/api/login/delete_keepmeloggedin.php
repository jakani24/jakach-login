<?php
session_start();
header('Content-Type: application/json');
$send_to=$_SESSION["end_url"];

include "../../config/config.php";
$conn = new mysqli($DB_SERVERNAME, $DB_USERNAME, $DB_PASSWORD, $DB_DATABASE);
$user_id=$_SESSION["id"];
$sql="DELETE FROM keepmeloggedin WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

?>
