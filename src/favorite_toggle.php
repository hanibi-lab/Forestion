<?php
// favorite_toggle.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['User_Id'])){ echo json_encode(['ok'=>false,'message'=>'로그인 필요']); exit;}
$data = json_decode(file_get_contents('php://input'), true);
$pid = (int)$data['product_id'];
include "db_conn.php";

$uid = $conn->real_escape_string($_SESSION['User_Id']);
$check = $conn->prepare("SELECT * FROM Favorite_FL WHERE Favorite_UR_Id = ? AND Favorite_PD_Id = ?");
$check->bind_param("si",$uid,$pid);
$check->execute();
if($check->get_result()->num_rows > 0){
    // 삭제
    $del = $conn->prepare("DELETE FROM Favorite_FL WHERE Favorite_UR_Id = ? AND Favorite_PD_Id = ?");
    $del->bind_param("si",$uid,$pid);
    $del->execute();
    echo json_encode(['ok'=>true,'message'=>'찜 해제됨']);
} else {
    $ins = $conn->prepare("INSERT INTO Favorite_FL (Favorite_UR_Id, Favorite_PD_Id, Favorite_Date) VALUES (?, ?, NOW())");
    $ins->bind_param("si",$uid,$pid);
    $ins->execute();
    echo json_encode(['ok'=>true,'message'=>'찜 추가됨']);
}
