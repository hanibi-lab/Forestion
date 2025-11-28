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
<?php
}else{
    header("Location: index.php");
    exit();
}
?> 

<!-- 슬라이더 영역 -->
<section class="slider">
    <div class="slides">
        <img src="image/banner1.png" alt="배너1">
        <img src="images/banner2.jpg" alt="배너2">
        <img src="images/banner3.jpg" alt="배너3">
    </div>
    <button class="arrow left">&#10094;</button>
    <button class="arrow right">&#10095;</button>
</section>

<!-- 상품 리스트 영역 -->
<section class="products">
    <ul class="prdList">
        <?php
        // ⭐ 변경됨 : 사이즈를 묶어서 가져오는 JOIN 쿼리로 변경
        // $result = $conn->query("
        //     SELECT 
        //         p.*,
        //         GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes
        //     FROM Product_PD p
        //     LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
        //     LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
        //     GROUP BY p.Product_Id
        //     LIMIT 8
        // ");
        
        // ⭐ 변경됨 : 찜 여부 포함한 JOIN 쿼리로 변경
        // LIMIT 8 삭제(11-27)
        $uid = isset($_SESSION['User_Id']) ? $_SESSION['User_Id'] : null;

        if ($uid) {
            $result = $conn->query("
                SELECT 
                    p.*,
                    GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes,
                    CASE WHEN f.Favorite_PD_Id IS NOT NULL THEN 1 ELSE 0 END AS is_wished
                FROM Product_PD p
                LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
                LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
                LEFT JOIN Favorite_FL f 
                    ON p.Product_Id = f.Favorite_PD_Id 
                AND f.Favorite_UR_Id = '$uid'
                GROUP BY p.Product_Id
            ");
        } else {
            $result = $conn->query("
                SELECT 
                    p.*,
                    GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes,
                    0 AS is_wished
                FROM Product_PD p
                LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
                LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
                GROUP BY p.Product_Id
            ");
        }
        
        while($row = $result->fetch_assoc()):
        ?> 
         <li class="prdList__item">
            <!-- 재고가 0일 떄 품절 -->
          <!-- <div class="thumbnail"> -->
          <div class="thumbnail <?php echo ($row['Product_Count'] <= 0) ? 'soldout' : ''; ?>">
                <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                    <img src="<?php echo $row['Product_Image']; ?>" 
                        alt="<?php echo $row['Product_Name']; ?>">
                </a>
                <!-- ⭐ 찜 아이콘을 썸네일 안 (우측 하단)으로 이동 -->
                <img 
                    src="<?php echo $row['is_wished'] ? 'image/wish_on(2).png' : 'image/wish_off(2).png'; ?>"
                    alt="찜하기" 
                    class="wish-img" 
                    data-id="<?php echo $row['Product_Id']; ?>"
                    onclick="toggleWish(this)"
                >
            </div>

            <div class="description">
                <div class="name">
                    <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                        <?php echo $row['Product_Name']; ?>
                    </a>
                </div>

                <div class="spec">
                    <!-- 가격 -->
                    <p>₩<?php echo number_format($row['Product_Price']); ?></p> 
                </div>

                <!-- 사이즈 + 재고 -->
                <div class="wish-meta">

                    <!-- ⭐ 변경됨: 사이즈가 묶여서 출력됨 -->
                    <p>
                        사이즈:
                        <?php 
                            echo $row['Sizes'] 
                                ? htmlspecialchars($row['Sizes']) 
                                : "없음"; 
                        ?>
                        <!-- / 재고: <?php echo htmlspecialchars($row['Product_Count']); ?> -->
                        <!-- 재고가 0이면 품절이라고 글씨 띄우기 -->
                         / 재고: <?php echo $row['Product_Count'] > 0 ? htmlspecialchars($row['Product_Count']) : '품절'; ?>
                    </p>

                    <!-- 여기 있던 찜 이미지 img 태그는 썸네일 쪽으로만 이동했고, 삭제 X (위로 위치만 변경) -->
                </div>
            </div>
        </li>
        <?php endwhile; $conn->close(); ?>
    </ul>
</section>

</main>

<?php require "./footer.php"; ?>

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

<script>
// 찜 토글 기능
async function toggleWish(imgElement) {
  const id = imgElement.dataset.id;

  const res = await fetch('favorite_toggle.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ product_id: id })
  });

  const data = await res.json();
  alert(data.message);

  if (data.status === 'added') {
    imgElement.src = 'image/wish_on(2).png';
  } else if (data.status === 'removed') {
    imgElement.src = 'image/wish_off(2).png';
  }
}
</script>

</body>
</html>
