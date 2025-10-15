<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include_once 'db_conn.php';

if(isset($_SESSION['User_Id']) && isset($_SESSION['User_Name'])){
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="UTF-8">
<title>Forestion</title>
<link rel="stylesheet" href="style.css"> <!-- 스타일 -->
</head>
<body>

<?php require "./header.php"; ?> <!-- 헤더: 로고, 카테고리, 로그인/마이페이지 -->

<main>
 <h1>Hello, <?php echo $_SESSION['User_Name']; ?></h1>
    <a href="logout.php">Logout</a>
<?php
}else{
    header("Location: index.php");
    exit();
}
?> 

<!-- 슬라이더 영역 -->
<section class="slider">
    <div class="slides">
        <!-- 여기서는 임시 이미지. DB 불러오려면 SELECT문 써서 불러올 수 있음 -->
        <img src="images/banner1.jpg" alt="배너1">
        <img src="images/banner2.jpg" alt="배너2">
        <img src="images/banner3.jpg" alt="배너3">
    </div>
    <button class="arrow left">&#10094;</button> <!-- 이전 배너 버튼 -->
    <button class="arrow right">&#10095;</button> <!-- 다음 배너 버튼 -->
</section>

<!-- 상품 리스트 영역 -->
<section class="products">
    <ul class="prdList">
        <?php
        // DB에서 상품 정보 8개 가져오기
        $result = $conn->query("SELECT * FROM Product_PD LIMIT 8");
        while($row = $result->fetch_assoc()):
        ?> 
        <li class="prdList__item">
            <div class="thumbnail">
                <!-- 상품 상세 페이지 링크 / 상품 클릭하면 product_detail.php?id=상품ID 로 이동 -->
                <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                      <img src="<?php echo $row['Product_Image']; ?>" 
			                 alt="<?php echo $row['Product_Name']; ?>">
                </a>
                <!-- 장바구니 기능 줄 옮김 
                <div class="icon__box">
                    <span class="cart">
                        ADD  장바구니 버튼, 추후 JS/PHP 연동 필요
                    </span>
                </div>
                -->
            </div>
            <div class="description">
                <div class="name">
                    <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                        <?php echo $row['Product_Name']; ?> <!-- 상품명 -->
                    </a>
                </div>
                <!-- ul을 div로 변경
                <ul class="spec">
                  <!-- 가격 
                    <li>₩<?php echo number_format($row['Product_Price']); ?></li> 
                </ul>
                -->
                <div class="spec">
                  <!-- 가격 -->
                    <p>₩<?php echo number_format($row['Product_Price']); ?></p> 
                </div>
                <!-- 사이즈, 재고 -->
                <p>사이즈: <?php echo $row['Product_Size']; ?> / 재고: <?php echo $row['Product_Count']; ?></p>
                
                <!-- 장바구니, 찜하기 버튼만 추가 -->
                <button type="submit">장바구니 담기</button>
                <button id="wishToggle" data-id="<?php echo $product['Product_Id']; ?>">
                찜하기
                </button>
            </div>
        </li>
        <?php endwhile; $conn->close(); ?>
    </ul>
</section>

</main>

<?php require "./footer.php"; ?> <!-- 푸터 -->

<!-- 슬라이더 JS 기능 -->
<script>
const slides = document.querySelector('.slides');
const slideImgs = document.querySelectorAll('.slides img');
const prev = document.querySelector('.arrow.left');
const next = document.querySelector('.arrow.right');
let index = 0;

function showSlide(i){
    if(i < 0) index = slideImgs.length-1;
    else if(i >= slideImgs.length) index = 0;
    else index = i;
    slides.style.transform = `translateX(-${index*100}%)`;
}

prev.addEventListener('click', ()=>showSlide(index-1));
next.addEventListener('click', ()=>showSlide(index+1));
</script>

</body>
</html>


