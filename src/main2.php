<?php
// main2.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
//아이디 확인
if (!isset($_SESSION['User_Id'])) {
    header("Location: index.php");
    exit();
}
include "db_conn.php";
require "./header.php";
?>

<main class="main2">

<?php
// 🟢 GET 파라미터로 받은 카테고리 ID
$categoryId = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;

// 🟢 카테고리 제목 불러오기
$categoryName = "전체상품";
if ($categoryId > 0) {
    $catQuery = $conn->query("SELECT Category_Name FROM Category WHERE Category_Id = $categoryId");
    if ($catRow = $catQuery->fetch_assoc()) {
        $categoryName = $catRow['Category_Name'];
    }
}

// 🟢 상품 목록 불러오기 (JOIN + GROUP_CONCAT)
// if ($categoryId > 0) {
//     $sql = "
//         SELECT 
//             p.*,
//             GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes
//         FROM Product_PD p
//         LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
//         LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
//         WHERE p.Product_Category = $categoryId
//         GROUP BY p.Product_Id
//         LIMIT 100
//     ";
// } else {
//     $sql = "
//         SELECT 
//             p.*,
//             GROUP_CONCAT(s.Size_Name ORDER BY s.Size_Id SEPARATOR '/') AS Sizes
//         FROM Product_PD p
//         LEFT JOIN Product_Size ps ON p.Product_Id = ps.Product_Id
//         LEFT JOIN Size s ON ps.Size_Id = s.Size_Id
//         GROUP BY p.Product_Id
//         LIMIT 100
//     ";
// }
// $result = $conn->query($sql);

$uid = isset($_SESSION['User_Id']) ? $_SESSION['User_Id'] : null;
$categoryId = isset($_GET['tag']) ? (int)$_GET['tag'] : 0;

// 🟢 상품 목록 불러오기 (JOIN + 찜 상태)
$baseQuery = "
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
";

if ($categoryId > 0) {
    $baseQuery .= " WHERE p.Product_Category = $categoryId";
}

$baseQuery .= " GROUP BY p.Product_Id LIMIT 100";
$result = $conn->query($baseQuery);

?>

  <!-- 카테고리 제목 -->
  <h1 class="category-title"><?php echo htmlspecialchars($categoryName); ?></h1>

  <!-- 구분선 -->
  <hr class="divider">

  <!-- 🔽 필터 + 정렬 (기능 없음, UI만 유지) -->
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

  <!-- 🔽 상품 리스트 -->
  <section class="products">
    <ul class="prdList">

      <?php while ($row = $result->fetch_assoc()): ?>
        <li class="prdList__item">
          <div class="thumbnail">
            <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
              <img src="<?php echo htmlspecialchars($row['Product_Image']); ?>" 
                   alt="<?php echo htmlspecialchars($row['Product_Name']); ?>">
            </a>
            <!-- ⭐ 썸네일 안 오른쪽 아래 찜 아이콘 -->
            <img 
              src="<?php echo $row['is_wished'] ? 'image/wish_on(2).png' : 'image/wish_off(2).png'; ?>"
              alt="찜하기"
              class="wish-img"
              data-id="<?php echo $row['Product_Id']; ?>"
              onclick="toggleWish(this)">
          </div>

          <div class="description">
            <!-- 상품명 -->
            <div class="name">
              <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
                <?php echo htmlspecialchars($row['Product_Name']); ?>
              </a>
            </div>

            <!-- 가격 -->
            <div class="spec">
              <p>₩<?php echo number_format($row['Product_Price']); ?></p>
            </div>

            <!-- 사이즈 + 재고 (텍스트만) -->
            <div class="wish-meta">
              <p>
                사이즈: 
                <?php echo $row['Sizes'] ? htmlspecialchars($row['Sizes']) : '없음'; ?>
                / 재고: <?php echo $row['Product_Count']; ?>
              </p>
            </div>
          </div>
        </li>
      <?php endwhile; ?>

    </ul>
  </section>
</main>

<script>
// 🟣 찜 기능 JS
async function toggleWish(imgElement) {
  const id = imgElement.dataset.id;

  const res = await fetch('favorite_toggle.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ product_id: id })
  });

  const data = await res.json();
  alert(data.message);

  imgElement.src = 
      (data.status === 'added') 
      ? 'image/wish_on(2).png' 
      : 'image/wish_off(2).png';
}
</script>

<?php require "./footer.php"; ?>
