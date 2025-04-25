<?php
include 'db.php';
session_start();

if (!isset($_GET['id'])) {
    echo "잘못된 접근입니다.";
    exit;
}
$id = intval($_GET['id']);
$stmt = $mysqli->prepare("SELECT p.title, p.description, p.price, u.username, p.seller_id, p.image, p.is_sold FROM products p JOIN users u ON p.seller_id=u.id WHERE p.id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $desc, $price, $seller, $seller_id, $img_path, $is_sold);

if ($stmt->fetch()):
    $my_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    // 삭제 처리
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product']) && $my_id == $seller_id) {
        $stmt_del = $mysqli->prepare("DELETE FROM products WHERE id=? AND seller_id=?");
        $stmt_del->bind_param("ii", $id, $my_id);
        $stmt_del->execute();
        $stmt_del->close();
        header("Location: products.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>상품 상세</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .detail-btns { margin-top: 18px; }
    </style>
    <script>
    function confirmBuy() {
        return confirm('정말 구매하시겠습니까?');
    }
    function soldAlert() {
        alert('이미 판매된 상품입니다');
        return false;
    }
    </script>
</head>
<body>
<header>
  <div class="container">
    <h1>상품 상세</h1>
    <nav>
      <a href="products.php">상품 목록</a>
      <a href="add_product.php">상품 등록</a>
      <a href="message.php">메시지</a>
      <a href="points.php">포인트</a>
      <a href="../index.php">메인</a>
    </nav>
  </div>
</header>
<main>
  <div class="main-card" style="max-width:420px;">
    <h2><?=htmlspecialchars($title)?></h2>
    <?php if ($img_path): ?>
        <img src="../uploads/<?=htmlspecialchars($img_path)?>" alt="상품 이미지" class="product-img"><br>
    <?php endif; ?>
    <p><strong>판매자:</strong> <?=htmlspecialchars($seller)?></p>
    <p><strong>가격:</strong> <?=number_format($price)?>P</p>
    <p><strong>설명:</strong><br><?=nl2br(htmlspecialchars($desc))?></p>
    <div class="detail-btns">
    <a href="products.php" class="button" style="background:#888;">목록으로</a>
    <?php if ($my_id && $my_id != $seller_id): ?>
        <?php if ($is_sold): ?>
            <button class="button" style="background:gray;color:#fff;" onclick="soldAlert();return false;">문의</button>
            <button class="button" style="background:gray;color:#fff;" onclick="soldAlert();return false;">구매</button>
        <?php else: ?>
            <form method="get" action="message.php" style="display:inline;">
                <input type="hidden" name="to" value="<?=htmlspecialchars($seller)?>">
                <button type="submit" class="button">문의</button>
            </form>
            <form method="post" action="buy.php" style="display:inline;" onsubmit="return confirmBuy();">
                <input type="hidden" name="product_id" value="<?=$id?>">
                <button type="submit" class="button" style="background:green;color:#fff;">구매</button>
            </form>
        <?php endif; ?>
    <?php elseif ($my_id && $my_id == $seller_id): ?>
        <form method="post" onsubmit="return confirm('정말 삭제하시겠습니까?');" style="display:inline;">
            <input type="hidden" name="delete_product" value="1">
            <button type="submit" class="button" style="background:red;color:#fff;">삭제</button>
        </form>
    <?php endif; ?>
    </div>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
<?php
else:
    echo "해당 상품을 찾을 수 없습니다.";
endif;
?>
