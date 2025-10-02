<?php
// cart_add.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php?error=로그인필요"); exit;}
$uid = $_SESSION['User_Id'];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)$_POST['qty']);
} else {
    $pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $qty = 1;
}
if(!$pid){ header("Location: main2.php"); exit; }
// Cart_CT 테이블에 단순 삽입 (중복 체크 필요)
$check = $conn->prepare("SELECT Cart_Id FROM Cart_CT WHERE Cart_UR_Id = ? AND Cart_PD_Id = ?");
$check->bind_param("si",$uid,$pid);
$check->execute();
if($check->get_result()->num_rows > 0){
    // 이미 있으면 무시 또는 수량 테이블이 있으면 업데이트. 현재 스키마엔 수량 컬럼이 없으므로 간단히 알림
    header("Location: cart.php?msg=already");
    exit;
} else {
    // Cart_Id 자동생성이 아니라면 MAX+1 방식 (권장: Cart_Id AUTO_INCREMENT)
    $ridRes = $conn->query("SELECT COALESCE(MAX(Cart_Id),0)+1 AS nxt FROM Cart_CT");
    $r = $ridRes->fetch_assoc();
    $nxt = $r['nxt'];
    $ins = $conn->prepare("INSERT INTO Cart_CT (Cart_Id, Cart_UR_Id, Cart_PD_Id) VALUES (?, ?, ?)");
    $ins->bind_param("isi",$nxt,$uid,$pid);
    $ins->execute();
    header("Location: cart.php");
    exit;
}
?>