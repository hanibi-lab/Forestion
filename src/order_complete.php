<?php
// order_complete.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
$uid = $_SESSION['User_Id'];
// $rec = $_POST['reciever']; $phone = $_POST['phone']; $addr = $_POST['addr']; $pay = $_POST['payment'];
$orderName = $_POST['order_name']; $order_Phone = $_POST['order_phone'];  
$receiver_Addr = $_POST['receiver_addr']; $pay = $_POST['payment'];

// 장바구니에서 상품 불러와 총액 계산
$stmt = $conn->prepare("SELECT p.Product_Id, p.Product_Price FROM Cart_CT c JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id WHERE c.Cart_UR_Id = ?");
$stmt->bind_param("s",$uid); $stmt->execute(); $res = $stmt->get_result();

$total = 0; $items = [];
while($r = $res->fetch_assoc()){
    $items[] = $r;
    $total += (int)$r['Product_Price']; // 수량 없이 단가만 더함 — 실제론 수량 곱하기 필요
}

// 주문 삽입
$ins = $conn->prepare("INSERT INTO Order_OD (Order_UR_Id, Order_Date, Order_TotalPrice, Order_Payment) VALUES (?, NOW(), ?, ?)");
$ins->bind_param("sis",$uid,$total,$pay);
$ins->execute();
$order_num = $conn->insert_id;

// 주문상세 저장
$insd = $conn->prepare("INSERT INTO OrderDetail_OD (Order_Num, Product_Id, Quantity, UnitPrice) VALUES (?, ?, ?, ?)");
foreach($items as $it){
    $qty = 1;
    $insd->bind_param("iiii", $order_num, $it['Product_Id'], $qty, $it['Product_Price']);
    $insd->execute();
}
// 장바구니 비우기
$del = $conn->prepare("DELETE FROM Cart_CT WHERE Cart_UR_Id = ?");
$del->bind_param("s",$uid); $del->execute();

echo "주문 완료! 주문번호: $order_num <a href='mypage.php'>마이페이지 보기</a>";
?>

