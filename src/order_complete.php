<?php
// // order_complete.php
// error_reporting(E_ALL); ini_set('display_errors',1);
// session_start();
// include "db_conn.php";
// if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
// $uid = $_SESSION['User_Id'];
// // $rec = $_POST['reciever']; $phone = $_POST['phone']; $addr = $_POST['addr']; $pay = $_POST['payment'];
// $orderName = $_POST['order_name']; $order_Phone = $_POST['order_phone'];  
// $receiver_Addr = $_POST['receiver_addr']; $pay = $_POST['payment'];

// // 장바구니에서 상품 불러와 총액 계산
// $stmt = $conn->prepare("SELECT p.Product_Id, p.Product_Price FROM Cart_CT c JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id WHERE c.Cart_UR_Id = ?");
// $stmt->bind_param("s",$uid); $stmt->execute(); $res = $stmt->get_result();

// $total = 0; $items = [];
// while($r = $res->fetch_assoc()){
//     $items[] = $r;
//     $total += (int)$r['Product_Price']; // 수량 없이 단가만 더함 — 실제론 수량 곱하기 필요
// }

// // 주문 삽입
// $ins = $conn->prepare("INSERT INTO Order_OD (Order_UR_Id, Order_Date, Order_TotalPrice, Order_Payment) VALUES (?, NOW(), ?, ?)");
// $ins->bind_param("sis",$uid,$total,$pay);
// $ins->execute();
// $order_num = $conn->insert_id;

// // 주문상세 저장
// $insd = $conn->prepare("INSERT INTO OrderDetail_OD (Order_Num, Product_Id, Quantity, UnitPrice) VALUES (?, ?, ?, ?)");
// foreach($items as $it){
//     $qty = 1;
//     $insd->bind_param("iiii", $order_num, $it['Product_Id'], $qty, $it['Product_Price']);
//     $insd->execute();
// }
// // 장바구니 비우기
// $del = $conn->prepare("DELETE FROM Cart_CT WHERE Cart_UR_Id = ?");
// $del->bind_param("s",$uid); $del->execute();

// echo "주문 완료! 주문번호: $order_num <a href='mypage.php'>마이페이지 보기</a>";
?>

<?php
// order_complete.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
$uid = $_SESSION['User_Id'];

$orderName = $_POST['order_name'];
$orderPhone = $_POST['order_phone'];
$receiverName = $_POST['receiver_name'];
$receiverPhone = $_POST['receiver_phone'];
$receiverAddr = $_POST['receiver_addr'];
$receiverMemo = $_POST['receiver_memo'];
$pay = $_POST['payment'];


$selected = $_POST['selected_items'] ?? [];
if (empty($selected)) {
    echo "선택된 상품이 없습니다.";
    exit;
}

//장바구니에서 상품 가져오기 (사이즈 포함)
$in = implode(',', array_fill(0, count($selected), '?'));
$stmt = $conn->prepare("
  SELECT p.Product_Id, p.Product_Price, c.Cart_Quantity, c.Size_Id 
  FROM Cart_CT c 
  JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id 
  WHERE c.Cart_Id IN ($in) AND c.Cart_UR_Id = ?
");
$params = [...$selected, $uid];
$types = str_repeat('i', count($selected)) . 's';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
$items = [];
while($r = $res->fetch_assoc()){
    $items[] = $r;
    $total += $r['Product_Price'] * $r['Cart_Quantity'];
}

//주문 테이블 삽입
$ins = $conn->prepare("INSERT INTO Order_OD 
  (Order_UR_Id, Order_Date, Order_TotalPrice, Order_Payment, 
   Orderer_Name, Order_Phone, Order_Receiver_Name, Order_Address, Order_Memo)
  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?)");

$ins->bind_param(
  "sissssss", // s,i,s,s,s,s,s 총 7개
  $uid, $total, $pay, $orderName, $orderPhone, $receiverName, $receiverAddr, $receiverMemo
);

$ins->execute();
$order_num = $conn->insert_id;

// 주문상세 테이블 삽입

// $insd = $conn->prepare("INSERT INTO OrderDetail_OD (Order_Num, Product_Id, Quantity, UnitPrice) VALUES (?, ?, ?, ?)");
// foreach($items as $it){
//     $insd->bind_param("iiii", $order_num, $it['Product_Id'], $it['Cart_Quantity'], $it['Product_Price']);
//     $insd->execute();
// }

$insd = $conn->prepare("INSERT INTO OrderDetail_OD (Order_Num, Product_Id, Size_Id, Quantity, UnitPrice) VALUES (?, ?, ?, ?, ?)");
foreach($items as $it){//사이즈 저장
    $insd->bind_param("iiiii", $order_num, $it['Product_Id'], $it['Size_Id'], $it['Cart_Quantity'], $it['Product_Price']);
    $insd->execute();
}

// 🔽 결제 완료 후 재고 차감 
foreach ($selected as $cartId) {

    // 1) 장바구니 정보 가져오기
    $getCart = $conn->prepare("
        SELECT Cart_PD_Id, Cart_Quantity 
        FROM Cart_CT 
        WHERE Cart_Id = ? AND Cart_UR_Id = ?
    ");
    $getCart->bind_param("is", $cartId, $uid);
    $getCart->execute();
    $cart = $getCart->get_result()->fetch_assoc();

    if ($cart) {
        $pid = $cart['Cart_PD_Id'];
        $qty = $cart['Cart_Quantity'];

        // 2) 재고 차감 (재고가 부족하지 않을 때만)
        $updateStock = $conn->prepare("
            UPDATE Product_PD
            SET Product_Count = Product_Count - ?
            WHERE Product_Id = ? AND Product_Count >= ?
        ");
        $updateStock->bind_param("iii", $qty, $pid, $qty);
        $updateStock->execute();
    }
}

// 장바구니에서 선택된 항목만 제거
$inClause = implode(',', array_fill(0, count($selected), '?'));
$del = $conn->prepare("DELETE FROM Cart_CT WHERE Cart_Id IN ($inClause) AND Cart_UR_Id = ?");
$params = [...$selected, $uid];
$types = str_repeat('i', count($selected)) . 's';
$del->bind_param($types, ...$params);
$del->execute();

?>
 <!-- header("Location: order_detail.php?order_num=$order_num");

echo "
<main class='complete-main'>
  <div class='complete-box'>
    <h2>결제가 완료되었습니다!</h2>
    <p class='order-number'>주문번호: $order_num</p>
    <div class='back-btn-box'>
      <button class='back-btn' onclick=\"location.href='home.php'\">← 메인페이지로 이동</button>
    </div>
  </div>
</main>
";
exit; -->

</html>
<head>
  <link rel="stylesheet" href="style.css">
</head>
  <body>
    <?php require "./header.php"; ?> 
    <main class="complete-main">
        <div class="content-box">
            <div class="complete-box">
                <h1>결제가 완료되었습니다!</h1>
                <p>주문해주셔서 감사합니다.</p>

                <a href="home.php" class="complete-btn">메인페이지로 이동</a>
            </div>
        </div>
    </main>
    <?php require "./footer.php"; ?>
  </body>
</html>