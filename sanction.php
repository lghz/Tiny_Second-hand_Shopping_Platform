<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>제재내역</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>제재내역</h1>
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
  <div class="main-card" style="max-width:400px;">
    <h2>제재내역</h2>
    <h3>차단 내역</h3>
    <table>
    <tr><th>차단 일시</th></tr>
    <?php
    $res = $mysqli->prepare("SELECT created_at FROM blocks WHERE blocked_user_id=? ORDER BY created_at DESC");
    $res->bind_param("i", $user_id);
    $res->execute();
    $res->bind_result($block_time);
    $found = false;
    while ($res->fetch()):
        $found = true;
    ?>
    <tr>
        <td><?=date('Y-m-d / H:i', strtotime($block_time))?></td>
    </tr>
    <?php endwhile;
    $res->close();
    if (!$found) echo "<tr><td>차단 기록 없음</td></tr>";
    ?>
    </table>

    <h3>포인트 소멸 내역</h3>
    <table>
    <tr><th>소멸 일시</th></tr>
    <?php
    $res = $mysqli->prepare("SELECT zeroed_at FROM points_zero_log WHERE user_id=? ORDER BY zeroed_at DESC");
    $res->bind_param("i", $user_id);
    $res->execute();
    $res->bind_result($zero_time);
    $found = false;
    while ($res->fetch()):
        $found = true;
    ?>
    <tr>
        <td><?=date('Y-m-d / H:i', strtotime($zero_time))?></td>
    </tr>
    <?php endwhile;
    $res->close();
    if (!$found) echo "<tr><td>포인트 소멸 기록 없음</td></tr>";
    ?>
    </table>
    <a href="../index.php" class="button" style="background:#888;">메인으로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
