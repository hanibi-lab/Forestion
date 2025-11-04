<?php
//my_reviews.php 내가 쓴 리뷰 보기
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";

if (!isset($_SESSION['User_Id'])) {
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['User_Id'];

// 사용자 리뷰 불러오기
$sql = "
    SELECT r.*, p.Product_Name, p.Product_Image
    FROM Review_RV r
    JOIN Product_PD p ON r.Review_PD_Id = p.Product_Id
    WHERE r.Review_UR_Id = ?
    ORDER BY r.Review_Date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="myreviews-main">
  <div class="myreviews-container">
    <h2>내가 쓴 후기</h2>

    <?php if ($result->num_rows > 0): ?>
      <div class="review-list">
        <?php while ($row = $result->fetch_assoc()): ?>
          <div class="review-item">
            <!-- 상품 이미지 -->
           <a href="product_detail.php?id=<?= $row['Review_PD_Id'] ?>">
                <img src="<?= $row['Product_Image'] ?>" alt="상품 이미지" class="review-img">
           </a>

            <div class="review-content">
              <h3><?= htmlspecialchars($row['Product_Name']) ?></h3>
        
              <!-- 별점 표시 -->
              <div class="review-rating" style="color: gold; font-size: 18px;">
                <?= str_repeat('★', $row['Review_Rating']) . str_repeat('☆', 5 - $row['Review_Rating']); ?>
              </div>

              <p><?= nl2br(htmlspecialchars($row['Review_Content'])) ?></p>
              <small><?= $row['Review_Date'] ?></small>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php else: ?>
      <p class="empty-text">작성한 후기가 없습니다.</p>
    <?php endif; ?>
  </div>
</main>

<?php require "./footer.php"; ?>
