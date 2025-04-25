<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
include 'db.php';

// 차단 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['block_user'])) {
    $block_user_id = intval($_POST['block_user']);
    // 이미 차단된 유저인지 확인
    $stmt = $mysqli->prepare("SELECT 1 FROM blocks WHERE blocked_user_id=?");
    $stmt->bind_param("i", $block_user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows == 0) {
        $stmt2 = $mysqli->prepare("INSERT INTO blocks (blocker_id, blocked_user_id, created_at) VALUES (?, ?, NOW())");
        $stmt2->bind_param("ii", $_SESSION['user_id'], $block_user_id);
        $stmt2->execute();
        $stmt2->close();
        // 차단된 계정의 게시물 삭제
        $mysqli->query("DELETE FROM products WHERE seller_id=$block_user_id");
        $msg = "차단 완료 및 해당 유저 게시물 삭제";
    } else {
        echo "<script>alert('이미 차단된 아이디입니다');location.href='admin.php';</script>";
        exit;
    }
    $stmt->close();
    header("Location: admin.php");
    exit;
}

// 차단 취소 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unblock_user'])) {
    $unblock_user_id = intval($_POST['unblock_user']);
    $stmt = $mysqli->prepare("DELETE FROM blocks WHERE blocked_user_id=?");
    $stmt->bind_param("i", $unblock_user_id);
    $stmt->execute();
    $stmt->close();
    $msg = "차단 취소 완료";
    header("Location: admin.php");
    exit;
}

// 포인트 소멸 처리 및 로그 기록
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zero_point'])) {
    $zero_user_id = intval($_POST['zero_point']);
    $mysqli->query("UPDATE points SET balance=0 WHERE user_id=$zero_user_id");
    // 로그 기록
    $mysqli->query("INSERT INTO points_zero_log (user_id, zeroed_at) VALUES ($zero_user_id, NOW())");
    $msg = "포인트 소멸 완료";
    header("Location: admin.php");
    exit;
}

// 삭제 처리
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $uid = intval($_POST['delete_user']);
    $mysqli->query("DELETE FROM points WHERE user_id=$uid");
    $mysqli->query("DELETE FROM products WHERE seller_id=$uid");
    $mysqli->query("DELETE FROM messages WHERE sender_id=$uid OR receiver_id=$uid");
    $mysqli->query("DELETE FROM blocks WHERE blocker_id=$uid OR blocked_user_id=$uid");
    $mysqli->query("DELETE FROM transactions WHERE from_user=$uid OR to_user=$uid");
    $mysqli->query("DELETE FROM users WHERE id=$uid");
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>관리자 페이지</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        table { margin-bottom: 30px; }
        th, td { padding: 5px 10px; }
        form { display: inline; }
    </style>
</head>
<body>
<header>
  <div class="container">
    <h1>관리자 페이지</h1>
    <nav>
      <a href="../index.php">메인으로</a>
    </nav>
  </div>
</header>
<main>
  <div class="main-card" style="max-width:900px;">
    <h2>유저 관리</h2>
    <?php if (isset($msg)) echo "<p class='success'>$msg</p>"; ?>
    <table>
    <tr>
        <th>ID</th><th>아이디</th><th>이메일</th><th>권한</th>
        <th>차단</th><th>차단일시</th><th>차단취소</th><th>삭제</th>
    </tr>
    <?php
    $res = $mysqli->query("SELECT id, username, email, role FROM users");
    while ($row = $res->fetch_assoc()):
        $blocked = 0;
        $block_time = "";
        $stmt = $mysqli->prepare("SELECT created_at FROM blocks WHERE blocked_user_id=? ORDER BY created_at DESC LIMIT 1");
        $stmt->bind_param("i", $row['id']);
        $stmt->execute();
        $stmt->bind_result($created_at);
        if ($stmt->fetch()) {
            $blocked = 1;
            $block_time = date('Y-m-d / H:i', strtotime($created_at));
        }
        $stmt->close();
    ?>
    <tr>
        <td><?=$row['id']?></td>
        <td><?=htmlspecialchars($row['username'])?></td>
        <td><?=htmlspecialchars($row['email'])?></td>
        <td><?=$row['role']?></td>
        <td>
            <?php if ($row['role'] !== 'admin' && !$blocked): ?>
            <form method="post" onsubmit="return confirm('정말 차단할까요?');" style="display:inline;">
                <input type="hidden" name="block_user" value="<?=$row['id']?>">
                <button type="submit" class="button" style="background:#444;color:#fff;">차단</button>
            </form>
            <?php elseif ($blocked): ?>
                <span style="color:green;font-weight:bold;">차단됨</span>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($blocked): ?>
                <?=$block_time?>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($row['role'] !== 'admin' && $blocked): ?>
            <form method="post" onsubmit="return confirm('정말 차단을 취소할까요?');" style="display:inline;">
                <input type="hidden" name="unblock_user" value="<?=$row['id']?>">
                <button type="submit" class="button" style="background:#888;color:#fff;">차단취소</button>
            </form>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($row['role'] !== 'admin'): ?>
            <form method="post" onsubmit="return confirm('정말 삭제할까요?');" style="display:inline;">
                <input type="hidden" name="delete_user" value="<?=$row['id']?>">
                <button type="submit" class="button">삭제</button>
            </form>
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
    </table>
<!-- 메시지 관리 -->
<h2>메시지 관리</h2>
<table>
<tr><th>ID</th><th>보낸이</th><th>받는이</th><th>내용</th><th>시간</th><th>삭제</th></tr>
<?php
$res = $mysqli->query("SELECT m.id, u1.username AS sender, u2.username AS receiver, m.content, m.created_at FROM messages m JOIN users u1 ON m.sender_id=u1.id JOIN users u2 ON m.receiver_id=u2.id");
while ($row = $res->fetch_assoc()): ?>
<tr>
    <td><?=$row['id']?></td>
    <td><?=htmlspecialchars($row['sender'])?></td>
    <td><?=htmlspecialchars($row['receiver'])?></td>
    <td><?=htmlspecialchars($row['content'])?></td>
    <td><?=$row['created_at']?></td>
    <td>
        <form method="post" onsubmit="return confirm('정말 삭제할까요?');">
            <input type="hidden" name="delete_message" value="<?=$row['id']?>">
            <button type="submit" class="button">삭제</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<!-- 차단 관리 -->
<h2>차단 관리</h2>
<table>
<tr><th>ID</th><th>차단자</th><th>차단유저</th><th>차단상품</th><th>시간</th><th>삭제</th></tr>
<?php
$res = $mysqli->query("SELECT b.id, u1.username AS blocker, u2.username AS blocked_user, b.blocked_product_id, b.created_at FROM blocks b LEFT JOIN users u1 ON b.blocker_id=u1.id LEFT JOIN users u2 ON b.blocked_user_id=u2.id");
while ($row = $res->fetch_assoc()): ?>
<tr>
    <td><?=$row['id']?></td>
    <td><?=htmlspecialchars($row['blocker'])?></td>
    <td><?=htmlspecialchars($row['blocked_user'])?></td>
    <td><?=$row['blocked_product_id']?></td>
    <td><?=$row['created_at']?></td>
    <td>
        <form method="post" onsubmit="return confirm('정말 삭제할까요?');">
            <input type="hidden" name="delete_block" value="<?=$row['id']?>">
            <button type="submit" class="button">삭제</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<!-- 포인트/거래 관리 -->
<h2>포인트 관리</h2>
<table>
<tr><th>유저ID</th><th>아이디</th><th>포인트</th></tr>
<?php
$res = $mysqli->query("SELECT p.user_id, u.username, p.balance FROM points p JOIN users u ON p.user_id=u.id");
while ($row = $res->fetch_assoc()): ?>
<tr>
    <td><?=$row['user_id']?></td>
    <td><?=htmlspecialchars($row['username'])?></td>
    <td><?=number_format($row['balance'])?>P</td>
</tr>
<?php endwhile; ?>
</table>
<!-- 포인트 관리 -->
<h2>포인트 관리</h2>
<table>
<tr><th>유저ID</th><th>아이디</th><th>포인트</th><th>소멸</th></tr>
<?php
$res = $mysqli->query("SELECT p.user_id, u.username, p.balance FROM points p JOIN users u ON p.user_id=u.id");
while ($row = $res->fetch_assoc()): ?>
<tr>
    <td><?=$row['user_id']?></td>
    <td><?=htmlspecialchars($row['username'])?></td>
    <td><?=number_format($row['balance'])?>P</td>
    <td>
        <form method="post" onsubmit="return confirm('정말 소멸시키시겠습니까?');" style="display:inline;">
            <input type="hidden" name="zero_point" value="<?=$row['user_id']?>">
            <button type="submit" class="button" style="background:red;color:#fff;">소멸</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

<h2>포인트 거래 내역</h2>
<table>
<tr><th>ID</th><th>보낸이</th><th>받는이</th><th>금액</th><th>시간</th><th>삭제</th></tr>
<?php
$res = $mysqli->query("SELECT t.id, u1.username AS from_user, u2.username AS to_user, t.amount, t.created_at FROM transactions t JOIN users u1 ON t.from_user=u1.id JOIN users u2 ON t.to_user=u2.id");
while ($row = $res->fetch_assoc()): ?>
<tr>
    <td><?=$row['id']?></td>
    <td><?=htmlspecialchars($row['from_user'])?></td>
    <td><?=htmlspecialchars($row['to_user'])?></td>
    <td><?=number_format($row['amount'])?>P</td>
    <td><?=$row['created_at']?></td>
    <td>
        <form method="post" onsubmit="return confirm('정말 삭제할까요?');">
            <input type="hidden" name="delete_transaction" value="<?=$row['id']?>">
            <button type="submit" class="button">삭제</button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</table>

</body>
</html>
