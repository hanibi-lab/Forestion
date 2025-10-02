
<?php
// cart.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit; }
$uid = $_SESSION['User_Id'];

if(isset($_GET['remove'])){
    $rid = (int)$_GET['remove'];
    $del = $conn->prepare("DELETE FROM Cart_CT WHERE Cart_Id = ? AND Cart_UR_Id = ?");
    $del->bind_param("is",$rid,$uid); $del->execute();
    header("Location: cart.php"); exit;
}

// 장바구니 불러오기
$stmt = $conn->prepare("SELECT c.Cart_Id, p.* FROM Cart_CT c JOIN Product_PD p ON c.Cart_PD_Id = p.Product_Id WHERE c.Cart_UR_Id = ?");
$stmt->bind_param("s",$uid);
$stmt->execute();
$res = $stmt->get_result();
?>
<main style="margin-top:100px;">
  <h2>장바구니</h2>
  <table>
    <tr><th>상품</th><th>가격</th><th>액션</th></tr>
    <?php while($r = $res->fetch_assoc()): ?>
      <tr>
        <td><img src="<?php echo $r['Product_Image']; ?>" style="width:80px;"> <?php echo $r['Product_Name']; ?></td>
        <td>₩<?php echo number_format($r['Product_Price']); ?></td>
        <td><a href="cart.php?remove=<?php echo $r['Cart_Id']; ?>">삭제</a></td>
      </tr>
    <?php endwhile; ?>
  </table>
  <a href="checkout.php">결제하기</a>
</main>
<?php require "./footer.php"; ?>
