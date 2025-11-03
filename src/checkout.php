<?php
// checkout.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit; }
$uid = $_SESSION['User_Id'];

// 선택된 상품 불러오기
$selected = $_POST['selected_items'] ?? [];
if (empty($selected)) {
  echo "<main class='checkout-main'><p class='empty-text'>선택된 상품이 없습니다.</p></main>";
  require "./footer.php"; exit;
}

$in = implode(',', array_fill(0, count($selected), '?'));
// $stmt = $conn->prepare("SELECT c.Cart_Id, p.Product_Name, p.Product_Price, p.Product_Image 
//c.Cart_Quantity, p.Product_Id 추가
$stmt = $conn->prepare("SELECT c.Cart_Id, c.Cart_Quantity, p.Product_Id,p.Product_Name, p.Product_Price, p.Product_Image 
  FROM Cart_CT c 
  JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id 
  WHERE c.Cart_Id IN ($in) AND c.Cart_UR_Id = ?");
$params = [...$selected, $uid];
$types = str_repeat('i', count($selected)) . 's';
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$total = 0;
?>

<!-- <main style="margin-top:100px;">
  <h2>주문서 작성</h2>
  <form action="order_complete.php" method="post">
    수령인: <input name="reciever" required><br>
    휴대폰: <input name="phone" required><br>
    주소: <input name="addr" required><br>
    결제수단:
    <select name="payment">
      <option>네이버페이</option>
      <option>토스페이</option>
      <option>가상결제</option>
    </select><br>
    <button type="submit">주문완료</button>
  </form>
</main> -->
<main class="checkout-main">
  <div class="checkout-container">
    <h2>주문서 작성</h2>
    
    <form action="order_complete.php" method="post">  <!-- order_complet에 정보 보내기 -->
    <section class="order-summary">
      <h3>주문 상품</h3>
      <?php while($r = $res->fetch_assoc()):
        $subtotal = $r['Product_Price'] * $r['Cart_Quantity'];
        $total += $subtotal; ?>
        <div class="order-item">
          <img src="<?= htmlspecialchars($r['Product_Image']) ?>" alt="">
          <div class="order-info">
            <p><?= htmlspecialchars($r['Product_Name']) ?></p>
            <span>수량: <?= $r['Cart_Quantity'] ?>개</span>
          </div>
          <span class="price">₩<?= number_format($subtotal) ?></span>
          <!-- order_complete.php로 보낼 데이터 -->
          <input type="hidden" name="selected_items[]" value="<?= $r['Cart_Id'] ?>">
          <input type="hidden" name="product_id[]" value="<?= $r['Product_Id'] ?>">
          <input type="hidden" name="quantity[]" value="<?= $r['Cart_Quantity'] ?>">
          <input type="hidden" name="price[]" value="<?= $r['Product_Price'] ?>">
        </div>
      <?php endwhile; ?>
      <div class="total-price">총 상품 금액: <strong>₩<?= number_format($total) ?></strong></div>
    </section>

    <section class="order-info-section">
      <h3>주문자 정보</h3>
      <input type="text" name="order_name" placeholder="이름" required>
      <input type="text" name="order_phone" placeholder="전화번호" required>
    </section>

    <section class="receiver-info">
      <h3>수령자 정보</h3>
      <input type="text" name="receiver_name" placeholder="이름" required>
      <input type="text" name="receiver_phone" placeholder="전화번호" required>
      <input type="text" name="receiver_addr" placeholder="주소" required>

      <label for="receiver_memo">배송 메모</label>
      <textarea id="receiver_memo" name="receiver_memo" placeholder="배송 메모를 입력하세요"></textarea>
    </section>

    <section class="payment-info">
      <h3>결제수단</h3>
      <select name="payment" required>
        <option>네이버페이</option>
        <option>토스페이</option>
        <option>가상결제</option>
      </select>
    </section>

    <div class="checkout-btn-wrap">
      <button type="submit" class="checkout-btn">결제하기</button>
    </div>
    </form>
  </div>
</main>
<?php require "./footer.php"; ?>
