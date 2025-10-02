<!DOCTYPE html>
  <html lang="ko">
  <head>
    <meta charset="UTF-8">
    <title>로그인 페이지</title>
    <link rel="stylesheet" href="css/style.css">  <!-- 공동 스타일 -->
    <!--  <link rel="stylesheet" href="css/login.css"> -->  <!-- 로그인 스타일 -->
  </head>

  <body>
    <?php require "./header.php"; ?>    <!-- 헤더 -->

    <!-- 로그인 컨테이너 -->
    <div class="login-container">
      <p>LOGIN</p>
      <form class="login-form" method="post" action="">     <!-- 로그인 입력 폼 시작, action에 버튼 누르면 넘어갈 주소 넣기 --> 
        <input type="text" name="User_Id" placeholder="아이디" required>   <!-- 아이디 입력 --> 
        <input type="password" name="User_Pwd" placeholder="비밀번호" required>   <!-- 비밀번호 입력 --> 
        <button type="submit">로그인</button>   <!-- 로그인 버튼 -->
      </form>
      
      <!--아이디/비밀번호 찾기, 회원가입 링크 -->
      <div class="login-links">
        <a href="#" id="find-account">아이디  /  비밀번호 찾기</a> |  <!-- 아이디/비번 찾는 창 미구현 -->
        <a href="signup.php">회원가입</a>
      </div>
    </div>

      <?php 
        //require "./footer.php"; ?> <!-- 푸터 -->
</body>
</html>
