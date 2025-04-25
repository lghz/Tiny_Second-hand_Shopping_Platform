<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'db.php';
$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $stmt = $mysqli->prepare("SELECT password_hash FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();
    if (!password_verify($old, $hash)) {
        $error = "현재 비밀번호가 일치하지 않습니다.";
    } elseif (strlen($new) < 4) {
        $error = "새 비밀번호는 4자 이상이어야 합니다.";
    } else {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt2 = $mysqli->prepare("UPDATE users SET password_hash=? WHERE id=?");
        $stmt2->bind_param("si", $new_hash, $user_id);
        $stmt2->execute();
        $stmt2->close();
        $success = "비밀번호가 변경되었습니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>비밀번호 변경</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container header-flex">
    <h1>비밀번호 변경</h1>
    <img src="../TSSPlogo.png" alt="TSSP 로고" class="header-logo">
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
    <h2>비밀번호 변경</h2>
    <form method="post">
      <input type="password" name="old_password" placeholder="현재 비밀번호" required>
      <input type="password" name="new_password" placeholder="새 비밀번호" required>
      <button type="submit" class="button">변경</button>
    </form>
    <?php
    if (isset($error)) echo "<p class='error'>$error</p>";
    if (isset($success)) echo "<p class='success'>$success</p>";
    ?>
    <a href="mypage.php" class="button" style="background:#888;">마이페이지로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
