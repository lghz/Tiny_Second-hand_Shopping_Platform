<?php
include 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $stmt = $mysqli->prepare("SELECT id, password_hash, role FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($id, $hash, $role);
    if ($stmt->fetch() && password_verify($password, $hash)) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $id;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $username;
        header("Location: ../index.php");
        exit;
    } else {
        $error = "로그인 실패";
    }
}
?>
<?php if (isset($_GET['joined'])): ?>
  <script>alert('회원가입이 완료되었습니다. 로그인 해주세요!');</script>
<?php endif; ?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>로그인</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>로그인</h1>
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
  <form method="post" class="login-form">
    <h2>로그인</h2>
    <input type="text" name="username" placeholder="아이디" required>
    <input type="password" name="password" placeholder="비밀번호" required>
    <button type="submit" class="button">로그인</button>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <a href="register.php" class="button" style="background:#888;">회원가입</a>
  </form>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
