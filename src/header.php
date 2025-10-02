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
        <a href="main2.php?tag=tree">나무</a>
        <a href="main2.php?tag=flower">꽃</a>
        <a href="main2.php?tag=tool">도구</a>
        <a href="main2.php?tag=flowerpot">화분</a>
        <a href="main2.php?tag=fertilizer">비료</a>
      </nav>
      <!-- 기존 header 메뉴 -->
      <!-- <ul class="category-menu">
        <li><a href="main2.php?tag=tree">나무</a></li>
       <li><a href="main2.php?tag=flower">꽃</a></li>
       <li><a href="main2.php?tag=tool">도구</a></li>
        <li><a href="main2.php?tag=flowerpot">화분</a></li>
        <li><a href="main2.php?tag=fertilizer">비료</a></li>
      </ul> -->


      

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
