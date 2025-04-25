<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// 내 포인트 조회
$stmt = $mysqli->prepare("SELECT balance FROM points WHERE user_id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>포인트 관리</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .msg-badge {
        position: relative;
        display: inline-block;
    }
    .msg-badge img {
        position: absolute;
        top: -10px;
        right: -10px;
        width: 20px;
        height: 20px;
        pointer-events: none;
    }
    </style>
</head>
<body>
<header>
  <div class="container">
    <h1>포인트 내역</h1>
  </div>
  <nav>
    <a href="products.php">상품 목록</a>
    <a href="add_product.php">상품 등록</a>
    <span class="msg-badge">
      <a href="message.php">메시지</a>
      <!-- 메시지 알림은 index.php에서만 필요하므로 points.php에는 미표시 -->
    </span>
    <a href="points.php" class="active">포인트</a>
    <a href="../index.php">메인</a>
  </nav>
</header>
<main>
  <div class="main-card" style="max-width:700px;">
    <h2>내 포인트: <span style="color:#00b894;"><?=number_format($balance)?>P</span></h2>
    <h3>포인트 사용 내역 (구매)</h3>
    <table>
    <tr>
      <th>구매 상품</th><th>판매자</th><th>소모 포인트</th><th>날짜</th><th>문의</th>
    </tr>
    <?php
    // 구매 내역: from_user = 본인
    $res = $mysqli->query(
        "SELECT t.amount, t.created_at, p.title, u.username AS seller, p.description
         FROM transactions t
         JOIN products p ON t.to_user = p.seller_id AND p.id = (
             SELECT id FROM products WHERE seller_id = t.to_user AND price = t.amount LIMIT 1
         )
         JOIN users u ON t.to_user = u.id
         WHERE t.from_user = $user_id
         ORDER BY t.created_at DESC"
    );
    while ($row = $res->fetch_assoc()):
    ?>
    <tr>
        <td><?=htmlspecialchars($row['title'])?></td>
        <td><?=htmlspecialchars($row['seller'])?></td>
        <td><?=htmlspecialchars($row['description'])?></td>
        <td><?=number_format($row['amount'])?>P</td>
        <td><?=$row['created_at']?></td>
        <td>
            <form method="get" action="message.php" style="display:inline;">
                <input type="hidden" name="to" value="<?=htmlspecialchars($row['seller'])?>">
                <button type="submit" class="button">문의</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>
    <h3>포인트 받은 내역 (판매)</h3>
    <table>
    <tr>
      <th>판매 상품</th><th>구매자</th><th>받은 포인트</th><th>날짜</th><th>문의</th>
    </tr>
    <?php
    // 판매 내역: to_user = 본인
    $res = $mysqli->query(
        "SELECT t.amount, t.created_at, p.title, u.username AS buyer, p.description
         FROM transactions t
         JOIN products p ON t.to_user = p.seller_id AND p.id = (
             SELECT id FROM products WHERE seller_id = t.to_user AND price = t.amount LIMIT 1
         )
         JOIN users u ON t.from_user = u.id
         WHERE t.to_user = $user_id
         ORDER BY t.created_at DESC"
    );
    while ($row = $res->fetch_assoc()):
    ?>
    <tr>
        <td><?=htmlspecialchars($row['title'])?></td>
        <td><?=htmlspecialchars($row['buyer'])?></td>
        <td><?=number_format($row['amount'])?>P</td>
        <td><?=$row['created_at']?></td>
        <td>
            <form method="get" action="message.php" style="display:inline;">
                <input type="hidden" name="to" value="<?=htmlspecialchars($row['buyer'])?>">
                <button type="submit" class="button">문의</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>
    <a href="../index.php" class="button" style="background:#888;">메인으로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
