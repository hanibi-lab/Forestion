

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";

header('Content-Type: application/json; charset=utf-8'); // ✅ AJAX 응답용

if (!isset($_SESSION['User_Id'])) {
    echo json_encode(["message" => "로그인이 필요합니다."]);
    exit;
}
$uid = $_SESSION['User_Id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;

    // ✅ 1. 상품 재고 확인
    $checkStock = $conn->prepare("SELECT Product_Count FROM Product_PD WHERE Product_Id = ?");
    $checkStock->bind_param("i", $pid);
    $checkStock->execute();
    $stockRow = $checkStock->get_result()->fetch_assoc();

    if (!$stockRow) {
        echo json_encode(["message" => "존재하지 않는 상품입니다."]);
        exit;
    }

    $stock = (int)$stockRow['Product_Count'];

    // ✅ 2. 장바구니에 같은 상품이 이미 있는지 확인
    $checkCart = $conn->prepare("SELECT Cart_Id, Cart_Quantity FROM Cart_CT WHERE Cart_UR_Id = ? AND Cart_PD_Id = ?");
    $checkCart->bind_param("si", $uid, $pid);
    $checkCart->execute();
    $cartRow = $checkCart->get_result()->fetch_assoc();

    if ($cartRow) {
        $newQty = $cartRow['Cart_Quantity'] + $qty;

        if ($newQty > $stock) {
            echo json_encode(["message" => "이미 최대 재고 수량(" . $stock . "개)까지 담았습니다."]);
            exit;
        }

        $update = $conn->prepare("UPDATE Cart_CT SET Cart_Quantity = ? WHERE Cart_Id = ?");
        $update->bind_param("ii", $newQty, $cartRow['Cart_Id']);
        $update->execute();

        echo json_encode(["message" => "상품 수량이 업데이트되었습니다."]);
        exit;
    } else {
        // ✅ 새로 담기
        if ($stock <= 0) {
            echo json_encode(["message" => "재고가 없습니다."]);
            exit;
        }

        $insert = $conn->prepare("INSERT INTO Cart_CT (Cart_UR_Id, Cart_PD_Id, Cart_Quantity) VALUES (?, ?, ?)");
        $insert->bind_param("sii", $uid, $pid, $qty);
        $insert->execute();

        echo json_encode(["message" => "상품이 장바구니에 담겼습니다."]);
        exit;
    }
}
?>