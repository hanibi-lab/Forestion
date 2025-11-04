<?php
session_start();
require "./header.php";
if(!isset($_SESSION['User_Id'])) {
  header("Location: index.php");
  exit;
}
$uid = $_SESSION['User_Id'];
?>

<main class="mypage-main">
  <div class="mypage-container">

    <h2 class="qna-title">많이 물어보는 문의</h2>
    <p class="qna-subtext">내용이 있을랑가?</p>
    <section id="prdQnA">
      <!-- <p>Q&A 기능 자리</p> -->
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


    <div class="back-btn-box">
      <button class="back-btn" onclick="location.href='mypage.php'">← 마이페이지로 돌아가기</button>
    </div>

  </div>
</main>

<script>
// ✅ Q&A 토글 기능 (여러 개 동시에 열리고 닫힘 가능 + 박스 전체 클릭 가능)
const qnaItems = document.querySelectorAll(".QnA_item");

qnaItems.forEach(item => {
  const question = item.querySelector(".QnA_question");
  const button = item.querySelector(".QnA_btn");

  question.addEventListener("click", () => toggleQnA(item));
  button.addEventListener("click", (e) => {
    e.stopPropagation();
    toggleQnA(item);
  });
});

function toggleQnA(item) {
  item.classList.toggle("active");
}
</script>

<?php require "./footer.php"; ?>
