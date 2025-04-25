<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
// 메시지함에 들어오면 마지막 확인 시각 갱신
$mysqli->query("UPDATE users SET last_message_check=NOW() WHERE id=$user_id");

// 차단된 유저는 등록 불가
$blocked = $mysqli->prepare("SELECT 1 FROM blocks WHERE blocked_user_id=?");
$blocked->bind_param("i", $_SESSION['user_id']);
$blocked->execute();
$blocked->store_result();
if ($blocked->num_rows > 0) {
    echo "<script>alert('해당 계정은 운영 정책 위반으로 인해 차단되었습니다');history.back();</script>";
    exit;
}

// 메시지 보내기
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['to_username'])) {
    $to_username_post = htmlspecialchars(trim($_POST['to_username']));
    $msg = htmlspecialchars(trim($_POST['message']));
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=?");
    $stmt->bind_param("s", $to_username_post);
    $stmt->execute();
    $stmt->bind_result($to_id);
    $found = $stmt->fetch();
    $stmt->close();

    if ($found && $msg) {
        $stmt_chk = $mysqli->prepare("SELECT 1 FROM blocks WHERE blocked_user_id=?");
        $stmt_chk->bind_param("i", $to_id);
        $stmt_chk->execute();
        $stmt_chk->store_result();
        if ($stmt_chk->num_rows > 0) {
            $error = "해당 수신자는 운영 정책 위반으로 차단된 아이디입니다.";
        } else {
            $stmt2 = $mysqli->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $user_id, $to_id, $msg);
            $stmt2->execute();
            $success = "메시지 전송 완료";
            $stmt2->close();
        }
        $stmt_chk->close();
    } else {
        $error = "받는이 아이디가 존재하지 않거나 메시지 내용이 없습니다.";
    }
}

// 받은 메시지 삭제
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_message'])) {
    $mid = intval($_POST['delete_message']);
    $stmt = $mysqli->prepare("DELETE FROM messages WHERE id=? AND receiver_id=?");
    $stmt->bind_param("ii", $mid, $user_id);
    $stmt->execute();
    $stmt->close();
    $success = "메시지 삭제 완료";
}

// 받은 메시지 조회
$stmt = $mysqli->prepare("SELECT m.id, m.content, u.username, m.created_at FROM messages m JOIN users u ON m.sender_id=u.id WHERE m.receiver_id=? ORDER BY m.created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($mid, $content, $sender, $created);
$messages = [];
while ($stmt->fetch()) {
    $messages[] = ['id'=>$mid,'content'=>$content,'sender'=>$sender,'created'=>$created];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>메시지함</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>메시지함</h1>
  </div>
  <nav>
    <a href="products.php">상품 목록</a>
    <a href="add_product.php">상품 등록</a>
    <a href="message.php" class="active">메시지</a>
    <a href="points.php">포인트</a>
    <a href="../index.php">메인</a>
  </nav>
</header>
<main>
  <div class="main-card" style="max-width:540px;">
    <h2>받은 메시지</h2>
    <table>
        <tr><th>보낸이</th><th>내용</th><th>시간</th><th>삭제</th></tr>
        <?php foreach ($messages as $msg): ?>
        <tr>
            <td><?=htmlspecialchars($msg['sender'])?></td>
            <td><?=htmlspecialchars($msg['content'])?></td>
            <td><?=$msg['created']?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="delete_message" value="<?=$msg['id']?>">
                    <button type="submit" class="button" onclick="return confirm('정말 삭제할까요?');">삭제</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <h3>메시지 보내기</h3>
    <form method="post" style="text-align:center;">
        <input type="text" name="to_username" placeholder="받는이(아이디)" value="<?=isset($to_username)?htmlspecialchars($to_username):''?>" required>
        <input type="text" name="message" placeholder="내용" required>
        <button type="submit" class="button">보내기</button>
    </form>
    <?php
    if (isset($error)) echo "<p class='error'>$error</p>";
    if (isset($success)) echo "<p class='success'>$success</p>";
    ?>
    <a href="../index.php" class="button" style="background:#888;">메인으로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
