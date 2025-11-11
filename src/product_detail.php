<?php
// product_detail.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 상품 정보 가져오기
$stmt = $conn->prepare("SELECT * FROM Product_PD WHERE Product_Id = ?");
$stmt->bind_param("i",$id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if(!$product){ echo "상품 없음"; exit;}

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
			      <p>사이즈: <?php echo htmlspecialchars($product['Product_Size']); ?></p>
			      <p>재고: <?php echo htmlspecialchars($product['Product_Count']); ?></p>
			    </div>
			    
			      <!-- 수량 + 장바구니 + 찜하기 -->
			      <div class="action-row">
			        <!-- <form action="cart_add.php" method="post" class="cart-form"> -->
              <form id="cartForm" class="cart-form">
			          <input type="hidden" name="product_id" value="<?php echo $product['Product_Id']; ?>">
			          수량:
			          <input type="number" name="qty" value="1" min="1" max="<?php echo $product['Product_Count']; ?>">
			          <!-- <button type="submit" class="cart-btn">장바구니 담기</button> -->
                <button type="button" id="addCartBtn" class="cart-btn">장바구니 담기</button>
			          <!-- <button type="button" id="wishToggle" data-id="<?php echo $product['Product_Id']; ?>" class="wish-btn">
			          찜하기
			        </button> -->
                <!-- 변경 -->
                <!-- <div class="wish-btn"> -->
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
      <!-- <p>Q&A 기능 자리</p> -->
       <!-- <p>Q&A 기능 자리 구현</p> -->
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
// 찜 토글 기능 (이미지 변경 추가)
document.getElementById('wishToggle').addEventListener('click', async function(){
  const img = this;
  const id = this.dataset.id;
  const res = await fetch('favorite_toggle.php', {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body: JSON.stringify({product_id: id})
  });
  // const j = await res.json();
  // alert(j.message);

  const data = await res.json();
  alert(data.message);

  // 상태 변경 (찜 됨 / 찜 해제)
  if (data.status === 'added') {
    img.src = 'image/wish_on(2).png'; // 찜한 상태
  } else if (data.status === 'removed') {
    img.src = 'image/wish_off(2).png'; // 찜 해제 상태
  }
});
</script>

<script>
// Q&A 토글 기능 (질문 박스 전체 클릭 가능 + 한 번에 하나만 열림 ) 
const qnaItems = document.querySelectorAll(".QnA_item");

qnaItems.forEach(item => {
  const question = item.querySelector(".QnA_question");
  const button = item.querySelector(".QnA_btn");

  // 질문 박스를 클릭했을 때
  question.addEventListener("click", () => toggleSingleQnA(item));

  // 버튼을 클릭했을 때 (이벤트 중복 방지)
  button.addEventListener("click", (e) => {
    e.stopPropagation(); // question 이벤트로 중복 실행 방지
    toggleSingleQnA(item);
  });
});

function toggleSingleQnA(item) {
  const isActive = item.classList.contains("active");

  // 다른 QnA 닫기
  document.querySelectorAll(".QnA_item").forEach(i => i.classList.remove("active"));

  // 클릭한 항목만 열기
  if (!isActive) item.classList.add("active");
}
</script>

<!-- <script>
// Q&A 토글 기능 (여러 개 동시에 열림 가능)
const btns = document.querySelectorAll(".QnA_btn");

btns.forEach((btn) => {
  btn.addEventListener("click", () => {
    const faqItem = btn.closest(".QnA_item");
    faqItem.classList.toggle("active"); // toggle로 상태만 반전
  });
});
</script> -->

<?php require "./footer.php"; ?>
