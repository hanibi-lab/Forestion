<?php
// product_detail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ⭐ 상품 정보 + 사이즈 문자열 가져오기 (JOIN 적용)
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

// ⭐ select용 사이즈 목록
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

$sizes = [];
while($row = $size_result->fetch_assoc()){
    $sizes[] = $row;
}

// ⭐ 찜 여부 확인
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
// ⭐ 상세 이미지 경로 만들기: 기본 이미지 파일명 + "2"
$detailImg = '';
if (!empty($product['Product_Image'])) {
    $imgPath = $product['Product_Image'];          // 예: image/3set_garden_shovel.jpg
    $dotPos  = strrpos($imgPath, '.');            // 마지막 '.' 위치 찾기

    if ($dotPos !== false) {
        // 앞부분 + '2' + 확장자
        // => image/3set_garden_shovel2.jpg
        $detailImg = substr($imgPath, 0, $dotPos) . '2' . substr($imgPath, $dotPos);
    }
}
?>

<link rel="stylesheet" href="style.css">

<main class="product-detail">
  <div class="container">
    <h1><?php echo htmlspecialchars($product['Product_Name']); ?></h1>
    
      <div class="product-flex">
        <div class="product-image">
          <img src="<?php echo htmlspecialchars($product['Product_Image']); ?>" alt="">
          <!-- ⭐ 상세 이미지 우측 하단 찜 아이콘 (home/main2와 동일 스타일) -->
          <img 
            src="<?php echo $is_wished ? 'image/wish_on(2).png' : 'image/wish_off(2).png'; ?>" 
            alt="찜하기" 
            id="wishToggle" 
            data-id="<?php echo $product['Product_Id']; ?>" 
            class="wish-img"
          >
        </div>

        <!-- 가격, 사이즈, 재고 -->
        <div class="product-info">
          <div class="info-box">
            <p>상품명: <?php echo htmlspecialchars($product['Product_Name']); ?></p>
            <p class="price">가격: <?php echo number_format($product['Product_Price']); ?> 원</p>

            <!-- ⭐ JOIN으로 가져온 사이즈 문자열 표시 -->
            <p>사이즈: 
              <?php echo $product['Sizes'] ? htmlspecialchars($product['Sizes']) : "없음"; ?>
            </p>

            <!-- <p>재고: </?php echo htmlspecialchars($product['Product_Count']); ?></p> -->
            <p>재고 : <?php echo ($product['Product_Count']<=0)? "품절" : htmlspecialchars($product['Product_Count']);?>
          </div>
          
            <!-- 수량 + 장바구니 + 찜하기 -->
            <div class="action-row">
              <form id="cartForm" class="cart-form">

                <input type="hidden" name="product_id" value="<?php echo $product['Product_Id']; ?>">

                <!-- ⭐ select 사이즈: 사이즈 있을 때만 표시 -->
                <?php if(count($sizes) > 0): ?>
                  <label>사이즈 선택:</label>
                  <select name="size_id" required>
                    <option value="" disabled selected>선택하세요</option>
                    <?php foreach($sizes as $s): ?>
                      <option value="<?php echo $s['Size_Id']; ?>">
                        <?php echo htmlspecialchars($s['Size_Name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php else: ?>
                  <!-- 사이즈 없음 제품은 size_id = 0 자동 전달 -->
                  <input type="hidden" name="size_id" value="0">
                <?php endif; ?>

                수량:
                <input type="number" name="qty" value="1" min="1" max="<?php echo $product['Product_Count']; ?>">

                <button type="button" id="addCartBtn" class="cart-btn">장바구니 담기</button>

                <!-- ✅ 기존 찜 이미지는 여기서 제거 (이미 위 product-image 안에 배치됨) -->

              </form>

              <script>
              document.getElementById('addCartBtn').addEventListener('click', async function(){
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

    <!-- 아래부턴 네가 준 원본 그대로 절대 변경 없음 -->
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

  <?php if (!empty($detailImg)): ?>
    <img src="<?php echo htmlspecialchars($detailImg); ?>" 
         alt="상품 상세 이미지" 
         class="detail-img">
  <?php else: ?>
    <p>상세 이미지가 준비되지 않았습니다.</p>
  <?php endif; ?>
</section>

    <!-- 구매안내 (리뉴얼 버전) -->
    <section id="prdInfo">
      <h3>구매안내</h3>

      <div class="guide-box">
        <h4 class="guide-title">배송 / 교환 / 환불 안내</h4>

        <div class="guide-table-wrap">
          <table class="guide-table">
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
          </table>
        </div>

        <ul class="guide-list">
          <li>분달이묘목 제외, 구매금액에 상관없이 차량·용달 배송은 착불입니다.</li>
          <li>분묘(분달이)는 무게에 따라 배송비가 다르게 책정되어 착불로 발송됩니다.</li>
          <li>상품 배송은 기본적으로 택배이며, 주문일로부터 1~2일 이내 발송됩니다.</li>
          <li>통장 입금 확인 후 배송을 원하는 경우, 3~4일 전에 배송 날짜를 지정해 주시면 원하시는 날짜에 수령 가능합니다.</li>
          <li>배송비는 주문 금액 10만원 이상일 경우 무료입니다. (단, 1박스 한정)</li>
          <li>차량 배송과 혼합 배송일 경우 배송비는 차량 배송비 기준으로 고객 부담입니다.</li>
          <li>직접 방문 수령도 가능합니다.</li>
          <li>제주도 및 산간 지역은 추가 배송비가 발생합니다.</li>
          <li>일부 품목(영산홍, 사철 등)은 금액과 관계없이 부피·무게에 따라 추가 배송비가 발생할 수 있습니다.</li>
        </ul>
      </div>
    </section>
    
    <!-- 후기 -->
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

    <!-- Q&A -->
    <section id="prdQnA">
      <h3>상품문의</h3>
      <ul class="QnA_list">
        <li class="QnA_item">
          <div class="QnA_question">
            <h4 class="QnA_title">배달 기간은 얼마나 걸리나요?</h4>
            <button class="QnA_btn">
              <span class="QnA_icon open">+</span>
              <span class="QnA_icon close">−</span>
            </button>
          </div>
          <div class="QnA_answer">
            <p class="QnA_text">
              보통 주문일로부터 1~2일 내 발송되며, 지역에 따라 2~4일 정도 소요됩니다.
            </p>
          </div>
        </li>

        <li class="QnA_item">
          <div class="QnA_question">
            <h4 class="QnA_title">교환이나 반품은 어떻게 하나요?</h4>
            <button class="QnA_btn">
              <span class="QnA_icon open">+</span>
              <span class="QnA_icon close">−</span>
            </button>
          </div>
          <div class="QnA_answer">
            <p class="QnA_text">
              상품 수령 후 7일 이내 고객센터로 문의 주시면 교환 또는 반품이 가능합니다.
            </p>
          </div>
        </li>
      </ul>
    </section>

  </div>
</main>

<script>
// ⭐ 찜 토글 기능 (최신 버전)
document.getElementById('wishToggle').addEventListener('click', async function(){
  const id = this.dataset.id;
  const img = this;

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
