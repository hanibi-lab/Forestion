<?php
// review_post.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); include "db_conn.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
$uid = $_SESSION['User_Id'];
$pid = (int)$_POST['product_id'];
$content = $conn->real_escape_string($_POST['content']);
$rating = (int)$_POST['rating'];
$ins = $conn->prepare("INSERT INTO Review_RV (Review_UR_Id, Review_PD_Id, Review_Rating, Review_Content, Review_Date) VALUES (?, ?, ?, ?, NOW())");
$ins->bind_param("siis",$uid,$pid,$rating,$content);
$ins->execute();
header("Location: product_detail.php?id=".$pid);
