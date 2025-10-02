<?php
// mypage.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); include "db_conn.php"; require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit;}
$uid = $_SESSION['User_Id'];
?>
<main style="margin-top:100px;">
  <h2>마이페이지 (<?php echo htmlspecialchars($uid); ?>)</h2>
  <h3>주문내역</h3>
  <?php
  $ord = $conn->prepare("SELECT * FROM Order_OD WHERE Order_UR_Id = ? ORDER BY Order_Date DESC");
  $ord->bind_param("s",$uid); $ord->execute(); $ro = $ord->get_result();
  while($o = $ro->fetch_assoc()){
      echo "<div>주문번호: <a href='order_detail.php?num={$o['Order_Num']}'>{$o['Order_Num']}</a> | 총액: ₩".number_format($o['Order_TotalPrice'])." | 날짜: {$o['Order_Date']}</div>";
  }
  ?>

  <h3>찜 목록</h3>
  <?php
  $fav = $conn->prepare("SELECT f.Favorite_Date, p.* FROM Favorite_FL f JOIN Product_PD p ON f.Favorite_PD_Id = p.Product_Id WHERE f.Favorite_UR_Id = ?");
  $fav->bind_param("s",$uid); $fav->execute(); $rf = $fav->get_result();
  while($f = $rf->fetch_assoc()){
      echo "<div><img src='{$f['Product_Image']}' style='width:80px;'> {$f['Product_Name']}</div>";
  }
  ?>
</main>
<?php require "./footer.php"; ?>
