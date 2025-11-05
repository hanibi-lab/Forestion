<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="ko">
  <head>
    <meta charset="UTF-8">
    <title>헤더</title>
    <!-- <link rel="stylesheet" href="css/style.css">   공동 스타일 -->
    <link rel="stylesheet" href="style.css">  <!-- 공동 스타일 -->
  </head>

    <div class="header">
      <!-- 로고 -->
      <div class="logo">
          <?php if (isset($_SESSION['User_Id'])): ?> <!-- User_Id 확인 -->

        <!-- 로그인 상태면 home.php(메인 페이지)로 -->
        <a href="home.php">
          <?php else: ?>

        <!-- 비로그인 상태면 index.php(로그인 페이지)로 -->
        <a href="index.php">
           <?php endif; ?>
          <img src="image/logo.png" alt="Forestion" width="120" height="auto">   <!-- 로고 이미지 클릭 시 index.php로 넘어감 -->
        </a>
      </div>

      <!-- 카테고리 메뉴 -->
      <nav class="nav-categories">
        <a href="main2.php?tag=plants">식물</a>
        <a href="main2.php?tag=seed">씨앗</a>
        <a href="main2.php?tag=tool">도구</a>
        <a href="main2.php?tag=etc">기타</a>
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
        <!-- <a href="login.php" class="login-text">Login</a> -->
        <!-- 로그인 / 로그아웃 추가-->
        <?php if (isset($_SESSION['User_Id'])): ?>
          <a href="logout.php" class="login-text">Logout</a>
        <?php else: ?>
          <a href="index.php" class="login-text">Login</a>
        <?php endif; ?>
      </div>
    </div>
</html>
