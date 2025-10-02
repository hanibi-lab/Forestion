<?php
// main2.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";
require "./header.php";
?>
<main style="margin-top:100px;">
  <div class="container" style="max-width:1200px;margin:0 auto;">
    <h2>상품목록</h2>

    <!-- 태그 버튼 -->
    <div class="tags">
      <a href="main2.php" class="tag">전체</a>
      <?php
      $tags = $conn->query("SELECT * FROM Tag_TG");
      while($t = $tags->fetch_assoc()){
          echo '<a href="main2.php?tag='.$t['Tag_Id'].'" class="tag">'.$t['Tag_Name'].'</a> ';
      }
      ?>
    </div>

    <ul class="prdList grid4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding:0;">
    <?php
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
    ?>
      <li style="list-style:none;">
        <div class="prdList__item product">
          <div class="thumbnail img-wrap">
            <a href="product_detail.php?id=<?php echo $row['Product_Id']; ?>">
              <img src="<?php echo htmlspecialchars($row['Product_Image']); ?>" alt="<?php echo htmlspecialchars($row['Product_Name']); ?>" style="width:100%;height:220px;object-fit:cover;">
            </a>
            <div class="icon__box">
              <span class="wish"><button class="wish-btn" data-id="<?php echo $row['Product_Id']; ?>">WISH</button></span>
              <span class="cart"><a href="cart_add.php?id=<?php echo $row['Product_Id']; ?>">ADD</a></span>
            </div>
          </div>
          <div class="description info">
            <div class="name"><?php echo htmlspecialchars($row['Product_Name']); ?></div>
            <div class="price">₩<?php echo number_format($row['Product_Price']); ?></div>
            <div class="desc">사이즈: <?php echo $row['Product_Size']; ?> / 재고: <?php echo $row['Product_Count']; ?></div>
          </div>
        </div>
      </li>
    <?php endwhile; ?>
    </ul>
  </div>
</main>

<script>
// 찜 버튼(간단한 AJAX)
document.querySelectorAll('.wish-btn').forEach(btn=>{
  btn.addEventListener('click', async (e)=>{
    const pid = btn.dataset.id;
    const res = await fetch('favorite_toggle.php', {
      method:'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({product_id: pid})
    });
    const j = await res.json();
    alert(j.message);
  });
});
</script>

<?php require "./footer.php"; ?>
