<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="UTF-8">
    <title>헤더</title>
    <link rel="stylesheet" href="css/style.css">  <!-- 공동 스타일 -->
    <!--  <link rel="stylesheet" href="css/header.css">  -->  <!-- 헤더 스타일 -->
  </head>

    <div class="header">
      <!-- 로고 -->
      <div class="logo">
        <a href="index.php">
          <img src="images/logo.png" alt="Forestion">    <!-- 로고 이미지 클릭 시 index.php로 넘어감 -->
        </a>
      </div>

      <!-- 카테고리 메뉴 -->
      <nav class="nav-categories">
        <a href="tree.php">나무</a>
        <a href="flower.php">꽃</a>
        <a href="tool.php">도구</a>
        <a href="flowerpot.php">화분</a>
        <a href="fertilizer.php">비료</a>
      </nav>

      

      <div class="nav-icons"> 
        <!-- 검색 창 -->
         
        <!-- 마이페이지 -->
        <a href="mypage.php">
          <img src="images/mypage.png" alt="마이페이지" class="icon">
        </a>

        <!-- 장바구니 -->
        <a href="cart.php">
          <img src="images/cart.png" alt="장바구니" class="icon">
        </a>
        
        <!-- 로그인 페이지 -->
        <a href="login.php" class="login-text">Login</a>
      </div>
    </div>
</html>