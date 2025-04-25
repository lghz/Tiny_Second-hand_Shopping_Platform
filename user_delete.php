<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    $mysqli->query("DELETE FROM points WHERE user_id=$uid");
    $mysqli->query("DELETE FROM products WHERE seller_id=$uid");
    $mysqli->query("DELETE FROM messages WHERE sender_id=$uid OR receiver_id=$uid");
    $mysqli->query("DELETE FROM blocks WHERE blocker_id=$uid OR blocked_user_id=$uid");
    $mysqli->query("DELETE FROM transactions WHERE from_user=$uid OR to_user=$uid");
    $mysqli->query("DELETE FROM users WHERE id=$uid");
    session_destroy();
    header("Location: ../index.php?unjoined=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원탈퇴</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>회원탈퇴</h1>
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
    <h2>회원탈퇴</h2>
    <p>정말로 회원탈퇴 하시겠습니까?<br>(탈퇴시 모든 정보가 삭제됩니다)</p>
    <form method="post">
        <button type="submit" name="confirm_delete" class="button" style="background:red;">회원탈퇴</button>
    </form>
    <a href="../index.php" class="button" style="background:#888;">취소</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
