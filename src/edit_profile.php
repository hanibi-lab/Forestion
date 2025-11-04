<?php
// edit_profile.php 내 정보 수정
error_reporting(E_ALL); ini_set('display_errors',1);
session_start(); 
include "db_conn.php"; 
require "./header.php";

if(!isset($_SESSION['User_Id'])){
    header("Location: index.php");
    exit;
}

$uid = $_SESSION['User_Id'];

// 사용자 정보 불러오기
$stmt = $conn->prepare("SELECT User_Name, User_PhoneNum, User_Addr FROM user_ur WHERE User_Id = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// 정보 수정 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $addr = $_POST['addr'];
    $password = $_POST['password'];

    if ($password) {
        $stmt = $conn->prepare("UPDATE user_ur SET User_Name=?, User_PhoneNum=?, User_Addr=?, User_Password=? WHERE User_Id=?");
        $stmt->bind_param("sssss", $name, $phone, $addr, password_hash($password, PASSWORD_DEFAULT), $uid);
    } else {
        $stmt = $conn->prepare("UPDATE user_ur SET User_Name=?, User_PhoneNum=?, User_Addr=? WHERE User_Id=?");
        $stmt->bind_param("ssss", $name, $phone, $addr, $uid);
    }

    if ($stmt->execute()) {
        echo "<script>alert('회원 정보가 수정되었습니다.'); location.href='mypage.php';</script>";
        exit;
    } else {
        echo "<script>alert('수정 중 오류가 발생했습니다.');</script>";
    }
}
?>

<div class="sign-body">
    <form class="sign-form" method="post">
        <h2>회원 정보 수정</h2>

        <label for="name">이름</label>
        <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['User_Name']) ?>" required>

        <label for="phone">전화번호</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['User_PhoneNum']) ?>">

        <label for="addr">주소</label>
        <input type="text" name="addr" id="addr" value="<?= htmlspecialchars($user['User_Addr']) ?>">

        <label for="password">비밀번호</label>
        <input type="password" name="password" id="password" placeholder="변경할 비밀번호 입력">

        <button type="submit">정보 수정</button>
    </form>
</div>

<?php require "./footer.php"; ?>
