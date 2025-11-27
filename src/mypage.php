<?php
// mypage.php
error_reporting(E_ALL); 
ini_set('display_errors',1);
session_start(); 
include "db_conn.php"; 
require "./header.php";

if(!isset($_SESSION['User_Id'])){ 
  header("Location: index.php"); 
  exit;
}

$uid = $_SESSION['User_Id'];

//추가
// 사용자 정보 불러오기
$stmt = $conn->prepare("SELECT User_Name, User_Id, User_PhoneNum, User_Addr FROM user_ur WHERE User_Id = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
$u = $result->fetch_assoc();

// 전화번호 포맷팅: 01012341234 -> 010-1234-1234
$rawPhone = $u['User_PhoneNum'] ?? '';
$phoneFormatted = '';

if (!empty($rawPhone)) {
    // 숫자만 남기기 (혹시라도 '-'가 섞여 들어온 경우 대비)
    $digits = preg_replace('/\D/', '', $rawPhone);

    // 3-3-4 or 3-4-4 형태 지원
    if (preg_match('/^(\d{3})(\d{3,4})(\d{4})$/', $digits, $m)) {
        $phoneFormatted = $m[1] . '-' . $m[2] . '-' . $m[3];
    } else {
        // 규격 안 맞으면 있는 그대로 보여줌
        $phoneFormatted = $rawPhone;
    }
}

// 찜 삭제 처리
if (isset($_GET['remove_fav'])) {
  $pid = (int)$_GET['remove_fav'];
  $delFav = $conn->prepare("DELETE FROM Favorite_FL WHERE Favorite_UR_Id = ? AND Favorite_PD_Id = ?");
  $delFav->bind_param("si", $uid, $pid);
  $delFav->execute();
  header("Location: mypage.php");
  exit;
}

// 회원 탈퇴 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
  $del = $conn->prepare("DELETE FROM user_ur WHERE User_Id = ?");
  $del->bind_param("s", $uid);
  if ($del->execute()) {
    session_destroy();
    echo "<script>alert('회원 탈퇴가 완료되었습니다.'); location.href='index.php';</script>";
    exit;
  } else {
    echo "<script>alert('회원 탈퇴 중 오류가 발생했습니다.');</script>";
  }
}
?>

<!-- 
<main style="margin-top:100px;">
  <h2>마이페이지 (</?php echo htmlspecialchars($uid); ?>)</h2>
  <h3>주문내역</h3>
  </?php
  $ord = $conn->prepare("SELECT * FROM Order_OD WHERE Order_UR_Id = ? ORDER BY Order_Date DESC");
  $ord->bind_param("s",$uid); $ord->execute(); $ro = $ord->get_result();
  while($o = $ro->fetch_assoc()){
      echo "<div>주문번호: <a href='order_detail.php?num={$o['Order_Num']}'>{$o['Order_Num']}</a> | 총액: ₩".number_format($o['Order_TotalPrice'])." | 날짜: {$o['Order_Date']}</div>";
  }
  ?>

  <h3>찜 목록</h3>
  </?php
  $fav = $conn->prepare("SELECT f.Favorite_Date, p.* FROM Favorite_FL f JOIN Product_PD p ON f.Favorite_PD_Id = p.Product_Id WHERE f.Favorite_UR_Id = ?");
  $fav->bind_param("s",$uid); $fav->execute(); $rf = $fav->get_result();
  while($f = $rf->fetch_assoc()){
      echo "<div><img src='{$f['Product_Image']}' style='width:80px;'> {$f['Product_Name']}</div>";
  }
  ?>
</main>
-->
<main class="mypage-main">
  <div class="mypage-container">

    <!-- 프로필 영역 -->
    <div class="mypage-profile">
      <img src="./image/default_profile.png" alt="기본 프로필 이미지" class="profile-img">
      <div class="profile-info">
        <h2><?= htmlspecialchars($u['User_Name']) ?> 님</h2>
        <p><strong>ID:</strong> <?= htmlspecialchars($u['User_Id']) ?></p>
        <p><strong>전화번호:</strong> 
          <?= !empty($phoneFormatted) ? htmlspecialchars($phoneFormatted) : '등록된 번호 없음' ?>
        </p>
        <p><strong>주소:</strong> <?= $u['User_Addr'] ? htmlspecialchars($u['User_Addr']) : '등록된 주소 없음' ?></p>
      </div>
    </div>

    <!-- 주문 내역 -->
    <section class="order-section">
      <h3> 주문 내역</h3>
      <?php
      $ord = $conn->prepare("SELECT * FROM Order_OD WHERE Order_UR_Id = ? ORDER BY Order_Date DESC");
      $ord->bind_param("s", $uid);
      $ord->execute();
      $ro = $ord->get_result();

      if ($ro->num_rows > 0) {
        echo "<div class='order-list'>";
        while ($o = $ro->fetch_assoc()) {
          echo "<div class='order-item'>
                  <span>주문번호: <a href='order_detail.php?num={$o['Order_Num']}'>{$o['Order_Num']}</a></span>
                  <span>총액: ₩" . number_format($o['Order_TotalPrice']) . "</span>
                  <span>날짜: {$o['Order_Date']}</span>
                </div>";
        }
        echo "</div>";
      } else {
        echo "<p class='empty-text'>주문 내역이 없습니다.</p>";
      }
      ?>
    </section>

    <!-- 찜 목록 -->
    <section class="wishlist-section">
      <h3>찜 목록</h3>
      <div class="wishlist-grid">
      <?php
      $fav = $conn->prepare("SELECT f.Favorite_Date, p.* 
                            FROM Favorite_FL f 
                            JOIN Product_PD p ON f.Favorite_PD_Id = p.Product_Id 
                            WHERE f.Favorite_UR_Id = ?");
      $fav->bind_param("s", $uid);
      $fav->execute();
      $rf = $fav->get_result();

      if ($rf->num_rows > 0) {
        while ($f = $rf->fetch_assoc()) {
          echo "
              <div class='wishlist-item'>
                <a href='product_detail.php?id={$f['Product_Id']}'>
                  <img src='{$f['Product_Image']}' alt='{$f['Product_Name']}'>
                  <p>{$f['Product_Name']}</p>
                </a>
                <a href='mypage.php?remove_fav={$f['Product_Id']}' class='delete-btn'>삭제</a>
              </div>
        ";
        }
      } else {
        echo "<p class='empty-text'>찜한 상품이 없습니다.</p>";
      }
      ?>
      </div>
    </section>

    <!-- 메뉴 버튼 -->
    <div class="mypage-menu">
      <button onclick="location.href='edit_profile.php'"> 내 정보 수정</button>
      <button onclick="location.href='my_qna.php'"> Q&A 보기</button>
      <button onclick="location.href='my_reviews.php'">내가 쓴 후기 보기</button>
      <button onclick="deleteAccount()">회원 탈퇴</button>
    </div>
  </div>

<script>
function deleteAccount() {
    if (!confirm("정말 탈퇴하시겠습니까?")) return;

    fetch("delete_account.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "delete_account=1"
    })
    .then(response => response.text())
    .then(data => {
        document.write(data); // PHP에서 echo로 alert + redirect 처리
    })
    .catch(error => {
        alert("탈퇴 중 오류가 발생했습니다.");
        console.error(error);
    });
}
</script>
</main>
<?php require "./footer.php"; ?> 
