
<?php
// upload_product.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_conn.php";

// 간단한 관리자 검증 (실제론 더 강화)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['Product_Name'];
    $price = (int)$_POST['Product_Price'];
    $category = (int)$_POST['Product_Category'];
    $size = $_POST['Product_Size'];
    $count = (int)$_POST['Product_Count'];

    if(!isset($_FILES['product_img']) || $_FILES['product_img']['error'] !== UPLOAD_ERR_OK){
        die("이미지 업로드 오류");
    }

    $uploadDir = __DIR__ . '/uploads/';
    if(!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $tmp = $_FILES['product_img']['tmp_name'];
    $orig = basename($_FILES['product_img']['name']);
    $ext = pathinfo($orig, PATHINFO_EXTENSION);
    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $uploadDir . $filename;

    if (move_uploaded_file($tmp, $dest)) {
        $imgPath = 'uploads/' . $filename; // DB에 저장할 경로 (상대 경로)
        $stmt = $conn->prepare("INSERT INTO Product_PD (Product_Id, Product_Name, Product_Category, Product_Price, Product_Image, Product_Size, Product_Count) VALUES (?, ?, ?, ?, ?, ?, ?)");
        // Product_Id가 AUTO_INCREMENT가 아니면 id 생성 로직 필요. 예시에선 자동 증가 안되어 있던 테이블이라서 간단히 MAX+1 처리
        $res = $conn->query("SELECT COALESCE(MAX(Product_Id),0)+1 AS nxt FROM Product_PD");
        $r = $res->fetch_assoc();
        $nxt = (int)$r['nxt'];

        $stmt->bind_param("isisssi", $nxt, $name, $category, $price, $imgPath, $size, $count);
        if($stmt->execute()){
            echo "상품 업로드 성공. <a href='admin_product_list.php'>돌아가기</a>";
        } else {
            echo "DB저장 오류: " . $stmt->error;
        }
    } else {
        echo "파일 이동 실패";
    }
}
?>
<!-- 간단 업로드 폼 (관리자용) -->
<form method="post" enctype="multipart/form-data">
    <input name="Product_Name" required placeholder="상품명"><br>
    <input name="Product_Category" required placeholder="카테고리번호"><br>
    <input name="Product_Price" required type="number" placeholder="가격"><br>
    <input name="Product_Size" required placeholder="S/M/L"><br>
    <input name="Product_Count" required type="number" placeholder="재고수"><br>
    <input type="file" name="product_img" required><br>
    <button type="submit">업로드</button>
</form>
