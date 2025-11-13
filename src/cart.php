<?php
// cart.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit; }
$uid = $_SESSION['User_Id'];

if(isset($_GET['remove'])){
    $rid = (int)$_GET['remove'];
    $del = $conn->prepare("DELETE FROM Cart_CT WHERE Cart_Id = ? AND Cart_UR_Id = ?");
    $del->bind_param("is",$rid,$uid); 
    $del->execute();
    header("Location: cart.php"); exit;
}

// ⭐ 사이즈 LEFT JOIN 추가됨
$stmt = $conn->prepare("
    SELECT 
        c.Cart_Id, 
        c.Cart_Quantity, 
        p.Product_Id, 
        p.Product_Name, 
        p.Product_Price, 
        p.Product_Image, 
        p.Product_Count,
        s.Size_Name   
    FROM Cart_CT c 
    JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id 
    LEFT JOIN Size s ON c.Size_Id = s.Size_Id   
    WHERE c.Cart_UR_Id = ?
");
$stmt->bind_param("s",$uid);
$stmt->execute();
$res = $stmt->get_result();
?>

<!-- 여기 부분 새로 추가 -->
<main class="cart-main">
  <div class="cart-container">
    <h2>장바구니</h2>

     <?php if ($res->num_rows > 0): ?>
      <form id="cartForm" action="checkout.php" method="post">
      <div class="cart-list">
        <?php while ($r = $res->fetch_assoc()): ?>
          <div class="cart-item">
            <div class="cart-left">
              <input type="checkbox" name="selected_items[]" value="<?= $r['Cart_Id'] ?>">
              <a href="product_detail.php?id=<?= $r['Product_Id'] ?>">
                <img src="<?= htmlspecialchars($r['Product_Image']) ?>" alt="<?= htmlspecialchars($r['Product_Name']) ?>">
              </a>

              <p>상품명: <?= htmlspecialchars($r['Product_Name']) ?></p>

              <!-- ⭐ 사이즈 표시 (null이면 없음) -->
              <p>사이즈: 
                <b><?= $r['Size_Name'] ? htmlspecialchars($r['Size_Name']) : "없음" ?></b>
              </p>

            </div>
            
            <div class="cart-right">
              <p>수량: <?= $r['Cart_Quantity'] ?>개</p>
              <span class="price">가격: <?= number_format($r['Product_Price'] * $r['Cart_Quantity']) ?>원</span>
              <a href="cart.php?remove=<?= $r['Cart_Id'] ?>" class="delete-btn">삭제</a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
      
      <div class="checkout-btn-wrap">
         <button form="cartForm" type="submit" class="checkout-btn">선택 상품 결제하기</button>
      </div>
    <?php else: ?>
      <p class="empty-text">장바구니에 담긴 상품이 없습니다.</p>
    <?php endif; ?>
  </div>
</main>

<?php require "./footer.php"; ?>
