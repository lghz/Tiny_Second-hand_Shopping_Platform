<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT username, email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>마이 페이지</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>마이페이지</h1>
  </div>
  <nav>
    <a href="products.php">상품 목록</a>
    <a href="add_product.php">상품 등록</a>
    <a href="message.php">메시지</a>
    <a href="points.php">포인트</a>
    <a href="../index.php">메인</a>
  </nav>
</header>
<main>
  <div class="main-card" style="max-width:400px;">
    <h2>내 정보</h2>
    <p><b>아이디:</b> <?=htmlspecialchars($username)?></p>
    <p><b>이메일:</b> <?=htmlspecialchars($email)?></p>
    <a href="changePW.php" class="button" style="background:#00b894;">비밀번호 변경</a>
    <form method="post" action="user_delete.php" style="margin-top:18px;">
      <button type="submit" class="button" style="background:red;">회원탈퇴</button>
    </form>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
