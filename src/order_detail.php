<!-- </?php
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
} -->
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

$order_num = $_GET['num'] ?? '';
// $order_num = $_GET['order_num'] ?? '';
$uid = $_SESSION['User_Id'];

if (!$order_num || !is_numeric($order_num)) {
    echo "<script>alert('잘못된 접근입니다.'); location.href='home.php';</script>";
    exit;
}

// 주문 정보
// $stmt = $conn->prepare("
//   SELECT o.Order_Num, o.Order_Date, o.Order_TotalPrice, o.Order_Payment,
//          o.Orderer_Name, o.Order_Phone, o.Order_Receiver_Name, 
//          o.Order_Receiver_Address, o.Order_Memo
//   FROM Order_OD o
//   WHERE o.Order_Num = ? AND o.Order_UR_Id = ?
// ");

$stmt = $conn->prepare("
  SELECT o.Order_Num, o.Order_Date, o.Order_TotalPrice, o.Order_Payment,
         o.Orderer_Name, o.Order_Phone, o.Order_Receiver_Name, 
         o.Order_Address, o.Order_Memo
  FROM Order_OD o
  WHERE o.Order_Num = ? AND o.Order_UR_Id = ?
");
$stmt->bind_param("ii", $order_num, $uid);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
  echo "<script>alert('존재하지 않는 주문입니다.'); location.href='home.php';</script>";
  exit;
}

// 상품 목록

// $stmt = $conn->prepare("
//   SELECT d.Product_Id, d.Quantity, d.UnitPrice,
//   d.Size_Id, s.Size_Name, //새로 추가됨
//   p.Product_Name, p.Product_Image
//   FROM OrderDetail_OD d
//   JOIN Product_PD p ON d.Product_Id = p.Product_Id
//   WHERE d.Order_Num = ?
// ");

$stmt = $conn->prepare("
SELECT d.Product_Id, d.Quantity, d.UnitPrice, 
       d.Size_Id, s.Size_Name,
       p.Product_Name, p.Product_Image
FROM OrderDetail_OD d
JOIN Product_PD p ON d.Product_Id = p.Product_Id
LEFT JOIN Size s ON d.Size_Id = s.Size_Id
WHERE d.Order_Num = ?
");
$stmt->bind_param("i", $order_num);
$stmt->execute();
$details = $stmt->get_result();
?>

<main class="checkout-main">
  <div class="ODdetil-container">
    <h2>주문 상세 내역</h2>

    <section class="order-info-section">
      <h3>주문 상세 정보</h3>
      <p><strong>주문번호:</strong> <?= htmlspecialchars($order['Order_Num']) ?></p>
      <p><strong>주문자 이름:</strong> <?= htmlspecialchars($order['Orderer_Name']) ?></p>
      <p><strong>주문자 전화번호:</strong> <?= htmlspecialchars($order['Order_Phone']) ?></p>
      <p><strong>수령자 이름:</strong> <?= htmlspecialchars($order['Order_Receiver_Name']) ?></p> 
      <p><strong>수령자 주소:</strong> <?= htmlspecialchars($order['Order_Address']) ?></p>
      <p><strong>배송 메모:</strong> <?= htmlspecialchars($order['Order_Memo']) ?></p>
      <p><strong>결제방법:</strong> <?= htmlspecialchars($order['Order_Payment']) ?></p>
      <p><strong>주문일자:</strong> <?= $order['Order_Date'] ?></p>
    </section>

    <section class="order-summary">
      <h3>주문 상품</h3>
      <?php
      $total = 0;
      while($row = $details->fetch_assoc()):
        $subtotal = $row['Quantity'] * $row['UnitPrice'];
        $total += $subtotal;
      ?>
      <div class="order-item">
        <img src="<?= htmlspecialchars($row['Product_Image']) ?>" alt="">
        <div class="order-info">
          <p><?= htmlspecialchars($row['Product_Name']) ?></p>
          <span>수량: <?= $row['Quantity'] ?>개</span>
          <?php if ($row['Size_Id'] != 0): ?>
              <span>사이즈: <?= htmlspecialchars($row['Size_Name']) ?></span>
          <?php else: ?>
              <span>사이즈: 없음</span>
          <?php endif; ?>
        </div>
        <span class="price">₩<?= number_format($subtotal) ?></span>
      </div>
      <?php endwhile; ?>
      <div class="total-price">총 결제 금액: <strong>₩<?= number_format($total) ?></strong></div>
    </section>
  </div>
</main>

<?php require "./footer.php"; ?>