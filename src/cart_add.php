<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";

header('Content-Type: application/json; charset=utf-8'); // AJAX 응답용

// 로그인 체크
if (!isset($_SESSION['User_Id'])) {
    echo json_encode(["message" => "로그인이 필요합니다."]);
    exit;
}

$uid = $_SESSION['User_Id'];

// POST 요청 확인
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 필수 값 체크
    if (!isset($_POST['product_id']) || !isset($_POST['size_id'])) {
        echo json_encode(["message" => "요청값이 부족합니다."]);
        exit;
    }

    $pid  = (int)$_POST['product_id'];   // 상품 ID
    $size = (int)$_POST['size_id'];      // ⭐ 추가된 사이즈 ID
    $qty  = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

    // 1️⃣ 상품 존재 및 재고 확인
    $checkStock = $conn->prepare("SELECT Product_Count FROM Product_PD WHERE Product_Id = ?");
    $checkStock->bind_param("i", $pid);
    $checkStock->execute();
    $stockRow = $checkStock->get_result()->fetch_assoc();

    if (!$stockRow) {
        echo json_encode(["message" => "존재하지 않는 상품입니다."]);
        exit;
    }

    $stock = (int)$stockRow['Product_Count'];

    if ($stock <= 0) {
        echo json_encode(["message" => "재고가 부족합니다."]);
        exit;
    }

    // 2️⃣ 같은 상품 + 같은 사이즈가 장바구니에 있는지 확인
    $checkCart = $conn->prepare("
        SELECT Cart_Id, Cart_Quantity 
        FROM Cart_CT 
        WHERE Cart_UR_Id = ? AND Cart_PD_Id = ? AND Size_Id = ?
    ");
    $checkCart->bind_param("iii", $uid, $pid, $size);
    $checkCart->execute();
    $exist = $checkCart->get_result()->fetch_assoc();

    if ($exist) {
        // 이미 있을 경우 수량 증가
        $newQty = $exist['Cart_Quantity'] + $qty;

        if ($newQty > $stock) {
            echo json_encode(["message" => "최대 재고(".$stock."개) 이상 담을 수 없습니다."]);
            exit;
        }

        $update = $conn->prepare("UPDATE Cart_CT SET Cart_Quantity = ? WHERE Cart_Id = ?");
        $update->bind_param("ii", $newQty, $exist['Cart_Id']);
        $update->execute();

        echo json_encode(["message" => "장바구니 수량이 업데이트되었습니다."]);
        exit;
    }

    // 3️⃣ 새 장바구니 추가
    $insert = $conn->prepare("
        INSERT INTO Cart_CT (Cart_UR_Id, Cart_PD_Id, Size_Id, Cart_Quantity)
        VALUES (?, ?, ?, ?)
    ");
    $insert->bind_param("iiii", $uid, $pid, $size, $qty);
    $insert->execute();

    echo json_encode(["message" => "장바구니에 담겼습니다."]);
    exit;
}
?>

