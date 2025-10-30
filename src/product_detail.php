<?php
// product_detail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $conn->prepare("SELECT * FROM Product_PD WHERE Product_Id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if(!$product){ echo "상품 없음"; exit;}
?>

<!-- 외부 CSS 연결 -->
<link rel="stylesheet" href="style.css">

<main class="product-detail">
  <div class="container">
    <!-- <h1><?php //echo htmlspecialchars($product['Product_Name']); ?></h1> -->
    
			<div class="product-flex">
			  <div class="product-image">
			    <img src="<?php echo htmlspecialchars($product['Product_Image']); ?>" alt="">
			  </div>
			
			  <!-- 가격, 사이즈, 재고 -->
			  <div class="product-info">
			    <div class="info-box">
            <p>상품명: <?php echo htmlspecialchars($product['Product_Name']); ?></p>
			      <p class="price">가격: <?php echo number_format($product['Product_Price']); ?> 원</p>
			      <p>사이즈: <?php echo htmlspecialchars($product['Product_Size']); ?></p>
			      <p>재고: <?php echo htmlspecialchars($product['Product_Count']); ?></p>
			    </div>
			    
			      <!-- 수량 + 장바구니 + 찜하기 -->
			      <div class="action-row">
			        <form action="cart_add.php" method="post" class="cart-form">
			          <input type="hidden" name="product_id" value="<?php echo $product['Product_Id']; ?>">
			          수량:
			          <input type="number" name="qty" value="1" min="1" max="<?php echo $product['Product_Count']; ?>">
			          <button type="submit" class="cart-btn">장바구니 담기</button>
			        <button id="wishToggle" data-id="<?php echo $product['Product_Id']; ?>" class="wish-btn">
			          찜하기
			        </button>
			        </form>
			      </div>
			  </div>
			</div>
		
    <!-- 탭 -->
    <div id="tabProduct" class="tabProduct display_tablet_only">
      <ul>
        <li class="selected"><a href="#prdDetail">상세정보</a></li>
        <li><a href="#prdInfo">구매안내</a></li>
        <li><a href="#prdReview">상품후기
          <span>(<?php
              $rC = $conn->prepare("SELECT COUNT(*) AS c FROM Review_RV WHERE Review_PD_Id = ?");
              $rC->bind_param("i",$id);
              $rC->execute();
              $c = $rC->get_result()->fetch_assoc()['c'];
              echo $c;
          ?>)</span></a></li>
        <li><a href="#prdQnA">상품문의 <span>(0)</span></a></li>
      </ul>
    </div>

    <!-- 상세정보 -->
    <section id="prdDetail">
      <h3>상세정보</h3>
      <p>여기에 상세 설명(이미지, HTML) 출력</p>
    </section>

    <section id="prdInfo">
      <h3>구매안내</h3>
      <p>배송/교환/환불 안내<br>
      <table>
        <caption>주문 금액에 따른 택배 비용</caption>
        <thead> 
          <tr>
            <th>주문금액</th>
            <th>포장비</th>
            <th>택배 비용</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>3만원 미만</td>
            <td>3,000원</td>
            <td>6,000원</td>
          </tr>
          <tr>
            <td>10만원 미만</td>
            <td>무료</td>
            <td>6,000원</td>
          </tr>
          <tr>
            <td>10만원 이상</td>
            <td>무료</td>
            <td>무료(단, 1박스에 한함)</td>
          </tr>
        </tbody>
      </table><br>
      
        분달이묘목 제외<br>
        구매금액에 상관없이	차량,용달배송	착불<br>
        분묘(분달이)는 무게에 따라 배송비가 다르게 책정이 되어 착불로 발송됩니다<br><br>

        1. 상품배송은 택배입니다. <br>
        2. 주문하신 날로부터 1~2일안에 택배로 받으실 수 있습니다.<br>
        -통장입금 확인후 배송을 원할시에는 3~4일전에 배송 날짜를 지정해 주시면 원하시는 날짜에 물건을 받아 보실 수 있습니다.<br>
        3. 배송비는 구매금액이 10만원 이상일 경우 무료로 배송됩니다.(단, 1박스에 한함) 단, 주문하신 상품이 차량배송과 혼합 배송일 경우에는 배송비는 차량배송비로 고객님 부담입니다.<br>
        4. 분달이 묘목의 경우에는 구매금액에 상관없이 착불배송입니다.<br>
        5. 운송방법은 기본적으로 택배 또는 차량배송이며 직접 방문 수령도 가능합니다.<br>
        6. 제주도를 비롯한 섬, 산간지역 등 기본배송지역 이외의 지역은 기본료 이외의 추가 배송비가 발생되며 고객님 부담입니다.<br><br><br>

        배송비 관련하여 10만원이 넘을 지라도 일부 품목의 경우(영산홍,사철등)
        금액에 비해서 부피가 크거나 무게가 무거운 묘목들은 고객 부담임을 양지하시기 바랍니다.
        </p>
    </section>

    <section id="prdReview">
      <h3>상품후기</h3>
      <?php
      $rv = $conn->prepare("SELECT r.*, u.User_Name FROM Review_RV r JOIN User_UR u ON r.Review_UR_Id = u.User_Id WHERE r.Review_PD_Id = ? ORDER BY r.Review_Date DESC");
      $rv->bind_param("i",$id);
      $rv->execute();
      $rr = $rv->get_result();
      while($row = $rr->fetch_assoc()){
          echo "<div class='review'><strong>".htmlspecialchars($row['User_Name'])."</strong> (".htmlspecialchars($row['Review_Date']).")<p>".nl2br(htmlspecialchars($row['Review_Content']))."</p></div>";
      }
      ?>

      <?php if(isset($_SESSION['User_Id'])): ?>
      <form action="review_post.php" method="post" class="review-form">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <textarea name="content" required></textarea><br>
        <select name="rating">
          <option value="5">5</option><option value="4">4</option><option value="3">3</option>
        </select>
        <button type="submit">리뷰 등록</button>
      </form>
      <?php else: ?>
        <p><a href="index.php">로그인</a> 후 리뷰 작성 가능</p>
      <?php endif; ?>
    </section>

    <section id="prdQnA">
      <h3>상품문의</h3>
      <p>Q&A 기능 자리</p>
    </section>
  </div>
</main>

<script>
// 찜 토글 기능
document.getElementById('wishToggle').addEventListener('click', async function(){
  const id = this.dataset.id;
  const res = await fetch('favorite_toggle.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({product_id: id})
  });
  const j = await res.json();
  alert(j.message);
});
</script>

<?php require "./footer.php"; ?>
