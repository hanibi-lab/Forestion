<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sname = "localhost";
$uname = "root";
$password = "";
$db_name = "Forestion";

$conn = mysqli_connect($sname, $uname, $password, $db_name);

if(!$conn){
    die("Connection failed: " . mysqli_connect_error());
}
?>