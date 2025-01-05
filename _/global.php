<?php
ini_set('display_errors', 1);
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'un');
define('DB_PASS', 'pw');
define('DB_NAME', 'www.host.com');
$con = mysqli_connect(DB_HOST,DB_USER, DB_PASS, DB_NAME);
mysqli_query($con, "SET NAMES utf8");

ini_set('memory_limit', '1000M');
date_default_timezone_set('Europe/Budapest');
?>
