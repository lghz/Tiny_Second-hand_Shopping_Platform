<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'];
    $role = 'user';

    if ($username && $email && $password) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "이미 존재하는 아이디입니다";
        } else {
            $stmt2 = $mysqli->prepare("SELECT id FROM users WHERE email=?");
            $stmt2->bind_param("s", $email);
            $stmt2->execute();
            $stmt2->store_result();

            if ($stmt2->num_rows > 0) {
                $error = "이미 존재하는 이메일입니다";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt3 = $mysqli->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $stmt3->bind_param("ssss", $username, $email, $hash, $role);
                if ($stmt3->execute()) {
                    $user_id = $stmt3->insert_id;
                    $init_point = 10000;
                    $stmt4 = $mysqli->prepare("INSERT INTO points (user_id, balance) VALUES (?, ?)");
                    $stmt4->bind_param("ii", $user_id, $init_point);
                    $stmt4->execute();
                    header("Location: login.php?joined=1");
                    exit;
                } else {
                    $error = "회원가입에 실패했습니다. 다시 시도해 주세요.";
                }
            }
            $stmt2->close();
        }
        $stmt->close();
    } else {
        $error = "모든 항목을 올바르게 입력하세요.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회원가입</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>회원가입</h1>
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
    <h2>회원가입</h2>
    <p style="color:green; font-weight:bold;">가입 시 10000P 증정!</p>
    <form method="post">
        <input type="text" name="username" placeholder="아이디" required>
        <input type="email" name="email" placeholder="이메일" required>
        <input type="password" name="password" placeholder="비밀번호" required>
        <button type="submit" class="button">가입하기</button>
    </form>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <a href="login.php" class="button" style="background:#888;">로그인</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
