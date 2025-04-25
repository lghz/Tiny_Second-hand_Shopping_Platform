<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $block_user = htmlspecialchars(trim($_POST['block_user']));
    // 아이디로 유저 id 조회
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $block_user);
    $stmt->execute();
    $stmt->bind_result($block_user_id);
    if ($stmt->fetch()) {
        $stmt->close();
        $stmt2 = $mysqli->prepare("INSERT INTO blocks (blocker_id, blocked_user_id) VALUES (?, ?)");
        $stmt2->bind_param("ii", $_SESSION['user_id'], $block_user_id);
        $stmt2->execute();
        $msg = "차단 완료";
        $stmt2->close();
    } else {
        $stmt->close();
        $msg = "존재하지 않는 아이디입니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>차단</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>유저 차단</h2>
<form method="post">
    <input type="text" name="block_user" placeholder="차단할 유저 아이디" required>
    <button type="submit" class="button">차단</button>
</form>
<?php if (isset($msg)) echo "<p>$msg</p>"; ?>
<a href="../admin.php">관리자 페이지로</a>
</body>
</html>
