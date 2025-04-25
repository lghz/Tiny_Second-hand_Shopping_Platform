<?php
include 'db.php';
session_start();

// 관리자 권한 체크 및 삭제 처리
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $delete_id = intval($_POST['delete_product']);
    // 상품 삭제 (관리자 권한으로)
    $mysqli->query("DELETE FROM products WHERE id=$delete_id");
    // 연관 데이터도 필요시 삭제 (예: 차단, 거래, 메시지 등)
    $mysqli->query("DELETE FROM blocks WHERE blocked_product_id=$delete_id");
    // 메시지/거래 등은 실제 구현에 따라 연관성에 맞게 추가
    // 삭제 후 새로고침
    header("Location: products.php");
    exit;
}

$search = isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '';
if ($search) {
    $stmt = $mysqli->prepare("SELECT p.id, p.title, p.price, u.username, p.is_sold FROM products p JOIN users u ON p.seller_id=u.id WHERE p.title LIKE ? AND p.is_blocked=0");
    $like = "%$search%";
    $stmt->bind_param("s", $like);
} else {
    $stmt = $mysqli->prepare("SELECT p.id, p.title, p.price, u.username, p.is_sold FROM products p JOIN users u ON p.seller_id=u.id WHERE p.is_blocked=0");
}
$stmt->execute();
$stmt->bind_result($id, $title, $price, $seller, $is_sold);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>상품 목록</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>상품 목록</h1>
    <nav>
      <a href="products.php" class="active">상품 목록</a>
      <a href="add_product.php">상품 등록</a>
      <a href="message.php">메시지</a>
      <a href="points.php">포인트</a>
      <a href="../index.php">메인</a>
    </nav>
  </div>
</header>
<main>
  <div class="main-card">
    <form method="get" style="text-align:center;">
        <input type="text" name="search" placeholder="상품 검색" value="<?=htmlspecialchars($search)?>" style="width:60%;max-width:300px;">
        <button type="submit" class="button">검색</button>
        <a href="add_product.php" class="button" style="background:#00b894;">상품 등록</a>
    </form>
    <table>
        <tr><th>제목</th><th>가격</th><th>판매자</th><th>상태</th></tr>
        <?php while ($stmt->fetch()): ?>
        <tr>
            <td><?=htmlspecialchars($title)?></td>
            <td><?=number_format($price)?>P</td>
            <td><?=htmlspecialchars($seller)?></td>
            <td>
                <?php if ($is_sold): ?>
                    <span style="color:green;font-weight:bold;">판매완료</span>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <form method="post" style="display:inline; margin-left:10px;">
                        <input type="hidden" name="delete_product" value="<?=$id?>">
                        <button type="submit" class="button" style="background:red; padding:4px 12px; font-size:0.9rem;" onclick="return confirm('정말 삭제하시겠습니까?');">삭제</button>
                    </form>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="product_detail.php?id=<?=$id?>" class="button" style="padding:6px 18px;">상세</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>