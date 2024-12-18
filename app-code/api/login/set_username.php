<?php
session_start();
$_SESSION["username"]=preg_replace("/[^a-z0-9_]/","",$_POST["username"]);
?>
