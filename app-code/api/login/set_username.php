<?php
session_start();
$_SESSION["needs_auth"]=true;
$_SESSION["logged_in"]=false;
$_SESSION["username"]=preg_replace("/[^a-z0-9_]/","",$_POST["username"]);
?>
