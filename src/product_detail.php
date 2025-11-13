<?php
// product_detail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 상품 정보 가져오기 (⭐ Product_Size 컬럼 제거 → JOIN 으로 사이즈 가져오기)
$stmt = $conn->prepare("
    SELECT 
        p.*,
        GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes
    FROM Product_PD p
    LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
    LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
    WHERE p.Product_Id = ?
    GROUP BY p.Product_Id
");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if(!$product){ echo "상품 없음"; exit; }


// ⭐ 상품별 사이즈 목록 조회 (select용)
$size_stmt = $conn->prepare("
    SELECT s.Size_Id, s.Size_Name
    FROM Product_Size ps
    JOIN Size s ON ps.Size_Id = s.Size_Id
    WHERE ps.Product_Id = ?
    ORDER BY s.Size_Id
");
$size_stmt->bind_param("i", $id);
$size_stmt->execute();
$size_result = $size_stmt->get_result();

// 배열로 저장
$sizes = [];
while ($row = $size_result->fetch_assoc()) {
    $sizes[] = $row;
}


// 현재 로그인한 유저의 찜 여부 확인
$is_wished = false;
if (isset($_SESSION['User_Id'])) {
  $uid = $_SESSION['User_Id'];
  $check = $conn->prepare("SELECT * FROM Favorite_FL WHERE Favorite_UR_Id = ? AND Favorite_PD_Id = ?");
  $check->bind_param("si", $uid, $id);
  $check->execute();
  $result = $check->get_result();
  if ($result->num_rows > 0) {
    $is_wished = true;
  }
}
?>

<!-- 외부 CSS 연결 -->
<link rel="stylesheet" href="style.css">

<main class="product-detail">
  <div class="container">
    <h1><?php echo htmlspecialchars($product['Product_Name']); ?></h1>
    
      <div class="product-flex">
        <div class="product-image">
          <img src="<?php echo htmlspecialchars($product['Product_Image']); ?>" alt="">
        </div>
      
        <!-- 가격, 사이즈, 재고 -->
        <div class="product-info">
          <div class="info-box">
            <p>상품명: <?php echo htmlspecialchars($product['Product_Name']); ?></p>
            <p class="price">가격: <?php echo number_format($product['Product_Price']); ?> 원</p>
            <p>사이즈: <?php echo $product['Sizes'] ? htmlspecialchars($product['Sizes']) : "없음"; ?></p>
            <p>재고: <?php echo htmlspecialchars($product['Product_Count']); ?></p>
          </div>
          
            <!-- 수량 + 장바구니 + 찜하기 -->
            <div class="action-row">
              <form id="cartForm" class="cart-form">

                <input type="hidden" name="product_id" value="<?php echo $product['Product_Id']; ?>">

                <!-- ⭐⭐ 사이즈 선택 (사이즈 있을 때만 select 표시) -->
                <?php if (count($sizes) > 0): ?>
                  <label>사이즈 선택:</label>
                  <select name="size_id" required>
                    <option value="" disabled selected>사이즈를 선택하세요</option>
                    <?php foreach ($sizes as $s): ?>
                      <option value="<?php echo $s['Size_Id']; ?>">
                        <?php echo htmlspecialchars($s['Size_Name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php else: ?>
                  <!-- 사이즈 없는 상품은 자동 0으로 전달 -->
                  <input type="hidden" name="size_id" value="0">
                <?php endif; ?>

                수량:
                <input type="number" name="qty" value="1" min="1" max="<?php echo $product['Product_Count']; ?>">

                <button type="button" id="addCartBtn" class="cart-btn">장바구니 담기</button>

                <img 
                    src="<?php echo $is_wished ? 'image/wish_on(2).png' : 'image/wish_off(2).png'; ?>" 
                    alt="찜하기" 
                    id="wishToggle" 
                    data-id="<?php echo $product['Product_Id']; ?>" 
                    class="wish-img"
                >
              </form>

              <script>
              document.getElementById('addCartBtn').addEventListener('click', async function(e){
                const form = document.getElementById('cartForm');
                const formData = new FormData(form);
                const res = await fetch('cart_add.php', {
                  method: 'POST',
                  body: formData
                });
                const data = await res.json();
                alert(data.message);
              });
              </script>
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
      ... (중략: 원본 그대로 유지) ...
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
      ... (중략: 원본 그대로 유지) ...
    </section>
  </div>
</main>

<script>
// 찜 토글 기능 (이미지 변경 추가)
document.getElementById('wishToggle').addEventListener('click', async function(){
  const img = this;
  const id = this.dataset.id;
  const res = await fetch('favorite_toggle.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({product_id: id})
  });

  const data = await res.json();
  alert(data.message);

  if (data.status === 'added') {
    img.src = 'image/wish_on(2).png';
  } else if (data.status === 'removed') {
    img.src = 'image/wish_off(2).png';
  }
});
</script>

<script>
// Q&A 토글 기능
const qnaItems = document.querySelectorAll(".QnA_item");

qnaItems.forEach(item => {
  const question = item.querySelector(".QnA_question");
  const button = item.querySelector(".QnA_btn");

  question.addEventListener("click", () => toggleSingleQnA(item));

  button.addEventListener("click", (e) => {
    e.stopPropagation();
    toggleSingleQnA(item);
  });
});

function toggleSingleQnA(item) {
  const isActive = item.classList.contains("active");
  document.querySelectorAll(".QnA_item").forEach(i => i.classList.remove("active"));
  if (!isActive) item.classList.add("active");
}
</script>

<?php require "./footer.php"; ?>
