<!DOCTYPE html>
  <html lang="ko">
  <head>
    <meta charset="UTF-8">
    <title>회원가입 약관 동의</title>
    <link rel="stylesheet" href="css/style.css">  <!-- 공동 스타일 -->
    <!--  <link rel="stylesheet" href="css/sigup.css">  -->  <!-- 회원가입 스타일 -->
  </head>
  <body>
      <?php require "./header.php"; ?>    <!-- 헤더 -->
    
    <div class="signup-container">
      <!-- 제목 -->
      <h1 class="signup-title">회원가입</h1>

      <!-- 서비스 이용약관 -->
      <div class="terms-section">
        <h3>서비스 이용약관</h3>
        <textarea readonly> 서비스 이용약관 내용
        </textarea>
        <label><input type="radio" name="terms1" value="agree"> 동의합니다</label>
        <label><input type="radio" name="terms1" value="disagree"> 동의하지 않습니다</label>
      </div>

      <!-- 개인정보수집 및 이용 동의 -->
      <div class="terms-section">
        <h3>개인정보수집 및 이용 동의</h3>
        <textarea readonly> 개인정보수집 및 이용 동의 내용
        </textarea>
        <label><input type="radio" name="terms2" value="agree"> 동의합니다</label>
        <label><input type="radio" name="terms2" value="disagree"> 동의하지 않습니다</label>
      </div>

      <!-- 확인 버튼 -->
      <button id="terms-confirm">확인</button>

    <?php 
      //require "./footer.php"; ?> <!-- 푸터 -->

    <script>
      document.getElementById("terms-confirm").onclick = function() {
        const terms1 = document.querySelector("input[name='terms1']:checked");
        const terms2 = document.querySelector("input[name='terms2']:checked");

        if (!terms1 || !terms2) {
          alert("약관에 동의 여부를 모두 선택해주세요.");
          return;
        }

        if (terms1.value === "agree" && terms2.value === "agree") {
          // 동의했으면 다음 단계로 이동
          location.href = "signup_form.php";
        } else {
          alert("모든 약관에 동의해야 회원가입이 가능합니다.");
        }
      }
    </script>
  </body>
</html>