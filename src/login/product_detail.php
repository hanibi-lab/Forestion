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
<main style="margin-top:100px;">
  <div class="container" style="max-width:900px;margin:0 auto;">
    <h1><?php echo htmlspecialchars($product['Product_Name']); ?></h1>
    <div style="display:flex;gap:20px;">
      <div style="flex:1;">
        <img src="<?php echo htmlspecialchars($product['Product_Image']); ?>" alt="" style="width:100%;height:400px;object-fit:cover;">
      </div>
      <div style="flex:1;">
        <p class="price">₩<?php echo number_format($product['Product_Price']); ?></p>
        <p>사이즈: <?php echo $product['Product_Size']; ?></p>
        <p>재고: <?php echo $product['Product_Count']; ?></p>

        <form action="cart_add.php" method="post">
          <input type="hidden" name="product_id" value="<?php echo $product['Product_Id']; ?>">
          수량: <input type="number" name="qty" value="1" min="1" max="<?php echo $product['Product_Count']; ?>">
          <button type="submit">장바구니 담기</button>
        </form>

        <button id="wishToggle" data-id="<?php echo $product['Product_Id']; ?>">
          찜하기
        </button>
      </div>
    </div>

    <!-- 탭 -->
    <div id="tabProduct" class="tabProduct display_tablet_only" style="margin-top:30px;">
      <ul style="display:flex;padding:0;margin:0;border-bottom:1px solid #e8e8e8;">
        <li class="selected" style="flex:1;text-align:center;"><a href="#prdDetail">상세정보</a></li>
        <li style="flex:1;text-align:center;"><a href="#prdInfo">구매안내</a></li>
        <li style="flex:1;text-align:center;"><a href="#prdReview">상품후기 <span>(<?php
            $rC = $conn->prepare("SELECT COUNT(*) AS c FROM Review_RV WHERE Review_PD_Id = ?");
            $rC->bind_param("i",$id); $rC->execute();
            $c = $rC->get_result()->fetch_assoc()['c']; echo $c;
        ?>)</span></a></li>
        <li style="flex:1;text-align:center;"><a href="#prdQnA">상품문의 <span>(0)</span></a></li>
      </ul>
    </div>

    <!-- 상세정보 -->
    <section id="prdDetail" style="padding:20px 0;">
      <h3>상세정보</h3>
      <!-- 예: 제품 설명을 DB에서 가져온다면 출력 -->
      <p>여기에 상세 설명(이미지, HTML) 출력</p>
    </section>

    <section id="prdInfo" style="padding:20px 0;">
      <h3>구매안내</h3>
      <p>배송/교환/환불 안내 …</p>
    </section>

    <section id="prdReview" style="padding:20px 0;">
      <h3>상품후기</h3>
      <!-- 리뷰 리스트 -->
      <?php
      $rv = $conn->prepare("SELECT r.*, u.User_Name FROM Review_RV r JOIN User_UR u ON r.Review_UR_Id = u.User_Id WHERE r.Review_PD_Id = ? ORDER BY r.Review_Date DESC");
      $rv->bind_param("i",$id);
      $rv->execute();
      $rr = $rv->get_result();
      while($row = $rr->fetch_assoc()){
          echo "<div class='review'><strong>".htmlspecialchars($row['User_Name'])."</strong> (".htmlspecialchars($row['Review_Date']).")<p>".nl2br(htmlspecialchars($row['Review_Content']))."</p></div>";
      }
      ?>
      <!-- 리뷰 작성 폼 (로그인 필요) -->
      <?php if(isset($_SESSION['User_Id'])): ?>
      <form action="review_post.php" method="post">
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

    <section id="prdQnA" style="padding:20px 0;">
      <h3>상품문의</h3>
      <p>Q&A 기능 자리</p>
    </section>

  </div>
</main>

<script>
// 찜 토글
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
