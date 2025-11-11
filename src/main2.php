<?php
// main2.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";
?>
<!-- <main style="margin-top:100px;"> -->

  <!-- <div class="container" style="max-width:1200px;margin:0 auto;"> -->
    <!-- <h2>상품목록</h2>

     <!--태그 버튼 
    <div class="tags">
      <a href="main2.php" class="tag">전체</a>
      </?php
      $tags = $conn->query("SELECT * FROM Tag_TG");
      while($t = $tags->fetch_assoc()){
          echo '<a href="main2.php?tag='.$t['Tag_Id'].'" class="tag">'.$t['Tag_Name'].'</a> ';
      }
      ?>
    </div>

    <ul class="prdList grid4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding:0;">
    </?php
    if(isset($_GET['tag'])){
        $tag = (int)$_GET['tag'];
        $sql = "SELECT p.* FROM Product_PD p
                JOIN ProductTag_PT pt ON p.Product_Id = pt.Product_Id
                WHERE pt.Tag_Id = ?
                LIMIT 100";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i",$tag);
        $stmt->execute();
        $res = $stmt->get_result();
    } else {
        $res = $conn->query("SELECT * FROM Product_PD LIMIT 100");
    }

    while($row = $res->fetch_assoc()):
    ?> -->


<?php
//카데고리 불러오는 php기능
// GET으로 받은 카테고리 ID
$categoryId = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;

// 카테고리 이름 불러오기
$categoryName = "전체상품";
if ($categoryId > 0) {
    $catQuery = $conn->query("SELECT Category_Name FROM Category WHERE Category_Id = $categoryId");
    if ($catRow = $catQuery->fetch_assoc()) {
        $categoryName = $catRow['Category_Name'];
    }
}

// 상품 목록 불러오기
if ($categoryId > 0) {
    $sql = "SELECT * FROM Product_PD WHERE Product_Category = $categoryId LIMIT 100";
} else {
    $sql = "SELECT * FROM Product_PD LIMIT 100";
}
$result = $conn->query($sql);
?>


      <!-- <li style="list-style:none;">
        <div class="prdList__item product">
          <div class="thumbnail img-wrap">
            <a href="product_detail.php?id=</?php echo $row['Product_Id']; ?>">
              <img src="</?php echo htmlspecialchars($row['Product_Image']); ?>" alt="</?php echo htmlspecialchars($row['Product_Name']); ?>" style="width:100%;height:220px;object-fit:cover;">
            </a>
            <div class="icon__box">
              <span class="wish"><button class="wish-btn" data-id="</?php echo $row['Product_Id']; ?>">WISH</button></span>
              <span class="cart"><a href="cart_add.php?id=</?php echo $row['Product_Id']; ?>">ADD</a></span>
            </div>
          </div>
          <div class="description info">
            <div class="name"></?php echo htmlspecialchars($row['Product_Name']); ?></div>
            <div class="price">₩</?php echo number_format($row['Product_Price']); ?></div>
            <div class="desc">사이즈: </?php echo $row['Product_Size']; ?> / 재고: </?php echo $row['Product_Count']; ?></div>
          </div>
        </div>
      </li>
    </?php endwhile; ?>
    </ul>
  </div>
</main> -->
<main class="main2">
  <!-- 카테고리 제목 -->
  <h1 class="category-title"><?php echo htmlspecialchars($categoryName); ?></h1>
  <!-- 구분선 -->
  <hr class="divider">

  <!-- 필터 + 정렬(기능 X)-->
  <div class="filter-sort">
    <div class="filters">
      <select id="filter-color">
        <option value="all">색상 전체</option>
        <option value="green">초록색</option>
        <option value="yellow">노랑색</option>
        <option value="pink">분홍색</option>
      </select>

      <select id="filter-season">
        <option value="all">계절 전체</option>
        <option value="spring">봄</option>
        <option value="summer">여름</option>
        <option value="fall">가을</option>
        <option value="winter">겨울</option>
      </select>
    </div>

    <div class="sort">
      <select id="sort-option">
        <option value="recommended">추천순</option>
        <option value="popular">인기순</option>
        <option value="priceAsc">가격 낮은순</option>
        <option value="priceDesc">가격 높은순</option>
      </select>
    </div>
  </div>

  <!-- 상품 리스트 영역 -->
  <section class="products">
    <ul class="prdList">
      
    <?php while ($row = $result->fetch_assoc()): ?>
        <li class="prdList__item">
            <div class="thumbnail">
              <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                <img src="<?php echo htmlspecialchars($row['Product_Image']); ?>" 
                     alt="<?php echo htmlspecialchars($row['Product_Name']); ?>">
              </a>
            </div>

            <div class="description">
              
              <div class="name">
                <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>"> <?php echo $row['Product_Name']; ?> <!-- 상품명 -->
                </a>
              </div>
              
              <div class="spec"> 
                <!-- 가격 -->
                <p>₩<?php echo number_format($row['Product_Price']); ?></p> 
              </div>
                <!-- 사이즈, 재고 -->
              <div class="wish-meta">
                <p>사이즈: <?php echo $row['Product_Size']; ?> / 재고: <?php echo $row['Product_Count']; ?></p>
                <img 
                    src="image/wish_off(2).png" 
                    alt="찜하기" 
                    class="wish-img" 
                    data-id="<?php echo $row['Product_Id']; ?>"
                    onclick="toggleWish(this)"
                ></div>
            </div>
        </li>
      <?php endwhile; ?>
    </ul>
  </section>
</main>

<script>
  //찜 기능
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
    imgElement.src = 'image/wish_on(2).png'; // 찜 O
  } else if (data.status === 'removed') {
    imgElement.src = 'image/wish_off(2).png'; // 찜 X
  }
}

</script>

<?php require "./footer.php"; ?>
