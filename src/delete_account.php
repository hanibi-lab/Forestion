<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db_conn.php";  // $conn 연결

if (!isset($_SESSION['User_Id'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='index.php';</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $user_id = $_SESSION['User_Id'];

    // 1. 찜 목록 삭제
    $stmt = $conn->prepare("DELETE FROM Favorite_FL WHERE Favorite_UR_Id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();

    // 2. 주문 상세 내역 삭제 (orderdetail_od)
    $stmt = $conn->prepare(
        "DELETE odt FROM orderdetail_od odt 
         JOIN order_od o ON odt.Order_Num = o.Order_Num 
         WHERE o.Order_UR_Id = ?"
    );
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();

    // 3. 주문 내역 삭제 (order_od)
    $stmt = $conn->prepare("DELETE FROM Order_OD WHERE Order_UR_Id = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->close();

    // 4. 회원 삭제
    $stmt = $conn->prepare("DELETE FROM user_ur WHERE User_Id = ?");
    $stmt->bind_param("s", $user_id);
    if ($stmt->execute()) {
        session_destroy();
        echo "<script>alert('회원 탈퇴가 완료되었습니다.'); location.href='index.php';</script>";
    } else {
        echo "<script>alert('탈퇴 중 오류가 발생했습니다.'); history.back();</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
