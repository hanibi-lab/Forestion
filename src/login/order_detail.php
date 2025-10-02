<?php
// order_detail.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); include "db_conn.php"; require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
$uid = $_SESSION['User_Id'];
$num = isset($_GET['num']) ? (int)$_GET['num'] : 0;

$ord = $conn->prepare("SELECT * FROM Order_OD WHERE Order_Num = ? AND Order_UR_Id = ?");
$ord->bind_param("is",$num,$uid); $ord->execute(); $o = $ord->get_result()->fetch_assoc();
if(!$o) { echo "주문 없음"; exit; }
echo "<h2>주문 상세 - {$o['Order_Num']}</h2>";

$od = $conn->prepare("SELECT d.*, p.Product_Name, p.Product_Image FROM OrderDetail_OD d JOIN Product_PD p ON d.Product_Id = p.Product_Id WHERE d.Order_Num = ?");
$od->bind_param("i",$num); $od->execute(); $rd = $od->get_result();
while($r = $rd->fetch_assoc()){
    echo "<div><img src='{$r['Product_Image']}' style='width:80px;'> {$r['Product_Name']} x {$r['Quantity']} | ₩".number_format($r['UnitPrice'])."</div>";
}

