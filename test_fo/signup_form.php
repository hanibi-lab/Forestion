<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>회원가입 - 정보 입력</title>
  <link rel="stylesheet" href="style.css">  <!-- CSS 외부 파일(style.css) 적용 -->
</head>
<body>
  <?php require "./header.php"; ?>

  <div class="signup-container">
    <h1 class="signup-title">회원가입</h1>

    <form class="signup-form" onsubmit="return submitSignup();">
      <input type="text" name="name" placeholder="이름" required>
      <input type="text" name="username" placeholder="아이디" required>
      <input type="password" name="password" placeholder="비밀번호" required>
      <input type="password" name="password_confirm" placeholder="비밀번호 확인" required>
      <input type="text" name="phone" placeholder="전화번호" required>
      <input type="email" name="email" placeholder="이메일" required>
      <button type="submit">가입</button>
    </form>
  </div>

  <?php 
    //require "./footer.php"; ?>

  <script>
    function submitSignup() {
      // 실제 DB 처리 대신 알람만 띄움
      alert("가입되셨습니다.");
      location.href = "index.php";
      return false; // 폼 전송 막음 (테스트용)
    }
  </script>
</body>
</html>