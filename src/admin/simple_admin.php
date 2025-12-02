<?php
// src/admin/simple_admin.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. 관리자 체크 (header.php에서 쓴 admin 아이디랑 꼭 맞추기)
if (!isset($_SESSION['User_Id']) || $_SESSION['User_Id'] !== 'adminuser') {
    echo "관리자만 접근 가능합니다.";
    exit;
}

// 2. DB 연결 (경로: admin 폴더 기준 한 단계 위)
require_once "../db_conn.php";

// ------------------------------------------------------
// 3. POST 요청 처리 (상품 추가 / 품절 / 삭제 / 재고 변경)
// ------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 3-1. 상품 추가
    if ($action === 'add_product') {
        $name     = trim($_POST['Product_Name'] ?? '');
        $category = (int)($_POST['Product_Category'] ?? 0);
        $price    = (int)($_POST['Product_Price'] ?? 0);
        $count    = (int)($_POST['Product_Count'] ?? 0);
        $image    = trim($_POST['Product_Image'] ?? '');

        if ($name !== '' && $category > 0 && $price >= 0 && $image !== '') {
            $stmt = $conn->prepare("
                INSERT INTO Product_PD (Product_Name, Product_Category, Product_Price, Product_Image, Product_Count)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("siisi", $name, $category, $price, $image, $count);
            $stmt->execute();
            $stmt->close();
        }
        // 비어 있는 값이 있어도 따로 에러 안 띄우고 넘어가게 했음 (단순 버전)

    } else {
        // 3-2. 나머지 액션은 Product_Id가 필요함
        $productId = isset($_POST['Product_Id']) ? (int)$_POST['Product_Id'] : 0;

        if ($productId > 0) {
            if ($action === 'update_stock') {
                // 재고 수정 (보너스 기능)
                $newStock = max(0, (int)($_POST['Product_Count'] ?? 0));
                $stmt = $conn->prepare("UPDATE Product_PD SET Product_Count = ? WHERE Product_Id = ?");
                $stmt->bind_param("ii", $newStock, $productId);
                $stmt->execute();
                $stmt->close();

            } elseif ($action === 'soldout') {
                // 품절 처리 = 재고 0
                $stmt = $conn->prepare("UPDATE Product_PD SET Product_Count = 0 WHERE Product_Id = ?");
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $stmt->close();

            } elseif ($action === 'delete') {
                // 상품 삭제
                // 외래키 걸려 있는 테이블들에서 먼저 지워줌 (간단 버전)
                $conn->query("DELETE FROM Cart_CT       WHERE Cart_PD_Id = {$productId}");
                $conn->query("DELETE FROM Favorite_FL   WHERE Favorite_PD_Id = {$productId}");
                $conn->query("DELETE FROM OrderDetail_OD WHERE Product_Id = {$productId}");
                $conn->query("DELETE FROM PayHistory_PH WHERE PayHistory_PD_Id = {$productId}");
                $conn->query("DELETE FROM Review_RV    WHERE Review_PD_Id = {$productId}");
                // Product_Size는 ON DELETE CASCADE라 Product_PD 삭제하면 같이 지워짐

                $stmt = $conn->prepare("DELETE FROM Product_PD WHERE Product_Id = ?");
                $stmt->bind_param("i", $productId);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // 처리 후 새로고침 (중복 POST 방지)
    header("Location: simple_admin.php");
    exit;
}

// ------------------------------------------------------
// 4. 화면에 뿌릴 데이터 조회 (카테고리 / 상품 목록)
// ------------------------------------------------------

// 카테고리 목록 (상품 추가 폼에서 사용)
$categoriesResult = $conn->query("
    SELECT Category_Id, Category_Name
    FROM Category
    ORDER BY Category_Id ASC
");

// 상품 목록
$productsResult = $conn->query("
    SELECT p.Product_Id, p.Product_Name, c.Category_Name, p.Product_Price, p.Product_Image, p.Product_Count
    FROM Product_PD p
    LEFT JOIN Category c ON p.Product_Category = c.Category_Id
    ORDER BY p.Product_Id ASC
");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title> 관리자 페이지 - 상품 관리</title>
    <link rel="stylesheet" href="../style.css?v=999">

    
</head>
<body>
<div class="admin-shell">

    <!-- 상단 헤더 -->
    <header class="admin-header">
        <div class="admin-header-left">
            <h1 class="admin-title">Forestion 관리자</h1>
            <div class="admin-subtitle">상품 추가 · 재고 관리 · 품절 · 삭제</div>
        </div>
        <div class="admin-header-right">
            <span class="muted">관리자 계정:</span>
            <strong><?php echo htmlspecialchars($_SESSION['User_Id']); ?></strong>
            <span>|</span>
            <a href="../home.php">쇼핑몰 메인</a>
            <span>·</span>
            <a href="../logout.php">로그아웃</a>
        </div>
    </header>

    <main class="admin-main">

        <!-- 위쪽: 상품 추가 카드 + 간단 설명 -->
        <div class="section-row">
            <section class="card card-narrow">
                <div class="card-header">
                    <div class="card-title">
                        상품 추가
                        <span class="badge">새 상품 등록</span>
                    </div>
                </div>
                <p class="card-desc">
                    카테고리, 가격, 초기 재고, 이미지 경로만 입력하면<br>
                    바로 상품 목록에 추가됩니다.
                </p>

                <form method="post" class="add-form">
                    <input type="hidden" name="action" value="add_product">

                    <label>
                        상품명
                        <input type="text" name="Product_Name" required placeholder="예: 괴마옥 파인애플 선인장">
                    </label>

                    <label>
                        카테고리
                        <select name="Product_Category" required>
                            <option value="">선택하세요</option>
                            <?php while ($c = $categoriesResult->fetch_assoc()): ?>
                                <option value="<?php echo $c['Category_Id']; ?>">
                                    <?php echo htmlspecialchars($c['Category_Name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>

                    <label>
                        가격 (원)
                        <input type="number" name="Product_Price" min="0" value="0" required>
                    </label>

                    <label>
                        초기 재고
                        <input type="number" name="Product_Count" min="0" value="0" required>
                    </label>

                    <label>
                        이미지 경로
                        <input type="text" name="Product_Image"
                               placeholder="예: image/new_product.jpg" required>
                    </label>

                    <div style="margin-top:14px; display:flex; justify-content:flex-end; gap:8px;">
                        <button type="reset" class="btn btn-ghost">초기화</button>
                        <button type="submit" class="btn btn-primary">상품 추가</button>
                    </div>
                </form>
            </section>

            <section class="card card-wide">
                <div class="card-header">
                    <div class="card-title">
                        상품 요약
                        <span class="badge">관리 팁</span>
                    </div>
                </div>
                <p class="card-desc">
                    · 재고를 0으로 설정하면 자동으로 <strong>품절</strong> 상태가 됩니다.<br>
                    · 삭제 시, 해당 상품과 연결된 장바구니·주문내역 일부가 함께 정리됩니다.<br>
                    · 상세 페이지나 메인에서는 <strong>Product_Count</strong>에 따라 직접 표시를 컨트롤하면 됩니다.
                </p>
                <p class="card-desc muted">
                    ※ 실제 이미지 파일은 <code>image/</code> 폴더에 직접 업로드해야 합니다.
                </p>
            </section>
        </div>

        <!-- 아래쪽: 상품 목록 테이블 -->
        <section class="card" style="margin-top:24px;">
            <div class="card-header">
                <div class="card-title">
                    상품 목록
                    <span class="badge">재고 / 품절 / 삭제</span>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th style="width:70px;">이미지</th>
                        <th>상품명</th>
                        <th style="width:100px;">카테고리</th>
                        <th style="width:90px;">가격</th>
                        <th style="width:80px;">재고</th>
                        <th style="width:90px;">상태</th>
                        <th style="width:170px;">재고 변경</th>
                        <th style="width:90px;">품절</th>
                        <th style="width:90px;">삭제</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($p = $productsResult->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <span class="pill-id">#<?php echo $p['Product_Id']; ?></span>
                            </td>
                            <td>
                                <?php if (!empty($p['Product_Image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($p['Product_Image']); ?>"
                                         alt=""
                                         class="product-img-thumb">
                                <?php else: ?>
                                    <span class="muted">이미지 없음</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($p['Product_Name']); ?></td>
                            <td><?php echo htmlspecialchars($p['Category_Name']); ?></td>
                            <td><?php echo number_format($p['Product_Price']); ?>원</td>
                            <td><?php echo (int)$p['Product_Count']; ?></td>
                            <td>
                                <?php if ($p['Product_Count'] <= 0): ?>
                                    <span class="status-pill status-soldout">품절</span>
                                <?php else: ?>
                                    <span class="status-pill status-on">판매중</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <!-- 재고 수정 -->
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="Product_Id" value="<?php echo $p['Product_Id']; ?>">
                                    <input type="hidden" name="action" value="update_stock">
                                    <input type="number" name="Product_Count"
                                           value="<?php echo (int)$p['Product_Count']; ?>"
                                           min="0">
                                    <button type="submit" class="btn btn-primary">변경</button>
                                </form>
                            </td>
                            <td>
                                <!-- 품절 처리 -->
                                <form method="post" class="inline-form"
                                      onsubmit="return confirm('이 상품을 품절 처리할까요? (재고가 0이 됩니다)');">
                                    <input type="hidden" name="Product_Id" value="<?php echo $p['Product_Id']; ?>">
                                    <input type="hidden" name="action" value="soldout">
                                    <button type="submit" class="btn btn-warning">품절</button>
                                </form>
                            </td>
                            <td>
                                <!-- 삭제 -->
                                <form method="post" class="inline-form"
                                      onsubmit="return confirm('정말 이 상품을 삭제할까요? 기존 주문/장바구니 등에 영향이 있을 수 있습니다.');">
                                    <input type="hidden" name="Product_Id" value="<?php echo $p['Product_Id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-danger">삭제</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>
</body>
</html>