<?php
// checkout.php
error_reporting(E_ALL); ini_set('display_errors',1);
session_start();
include "db_conn.php";
require "./header.php";
if(!isset($_SESSION['User_Id'])){ header("Location: index.php"); exit; }
$uid = $_SESSION['User_Id'];
?>
<main style="margin-top:100px;">
  <h2>주문서 작성</h2>
  <form action="order_complete.php" method="post">
    수령인: <input name="reciever" required><br>
    휴대폰: <input name="phone" required><br>
    주소: <input name="addr" required><br>
    결제수단:
    <select name="payment">
      <option>네이버페이</option>
      <option>토스페이</option>
      <option>가상결제</option>
    </select><br>
    <button type="submit">주문완료</button>
  </form>
</main>
<?php require "./footer.php"; ?>
