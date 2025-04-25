<?php
session_start();
$show_new = false;
if (isset($_SESSION['user_id'])) {
    include_once 'php/db.php';
    $user_id = $_SESSION['user_id'];
    // 마지막 메시지 확인 시각 가져오기
    $stmt = $mysqli->prepare("SELECT last_message_check FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($last_check);
    $stmt->fetch();
    $stmt->close();

    if ($last_check) {
        $stmt2 = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND created_at > ?");
        $stmt2->bind_param("is", $user_id, $last_check);
        $stmt2->execute();
        $stmt2->bind_result($msgcnt);
        $stmt2->fetch();
        $stmt2->close();
        if ($msgcnt > 0) $show_new = true;
    } else {
        $stmt2 = $mysqli->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $stmt2->bind_result($msgcnt);
        $stmt2->fetch();
        $stmt2->close();
        if ($msgcnt > 0) $show_new = true;
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>중고거래 플랫폼</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
    .msg-badge {
        position: relative;
        display: inline-block;
    }
    .msg-badge img {
        position: absolute;
        top: -13px;
        right: -13px;
        width: 30px;
        height: 30px;
        pointer-events: none;
    }
    </style>
</head>
<body>
<?php if (isset($_GET['unjoined'])): ?>
  <script>alert('회원탈퇴가 완료되었습니다. 그동안 이용해주셔서 감사합니다.');</script>
<?php endif; ?>
<header>
  <div class="container header-flex">
    <img src="TSSPlogo.png" alt="TSSP 로고" class="header-logo">
    <h1>Tiny Second-hand Shopping Platform</h1>
  </div>
  <nav>
    <a href="php/products.php">상품 목록</a>
    <a href="php/add_product.php">상품 등록</a>
    <span class="msg-badge">
      <a href="php/message.php">메시지</a>
      <?php if($show_new): ?>
        <img src="new.png" alt="새 메시지">
      <?php endif; ?>
    </span>
    <a href="php/points.php">포인트</a>
    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
      <a href="php/admin.php">관리자</a>
    <?php endif; ?>
    <?php if(isset($_SESSION['user_id'])): ?>
      <a href="php/sanction.php">제재내역</a>
      <a href="php/mypage.php">마이 페이지</a>
      <a href="php/logout.php">로그아웃</a>
    <?php else: ?>
      <a href="php/login.php">로그인</a>
    <?php endif; ?>
  </nav>
</header>
<main>
  <div class="main-card">
    <h2>
      <?php if(isset($_SESSION['username'])): ?>
        <span class="username">
          <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') echo '(관리자)'; ?>
          <?=htmlspecialchars($_SESSION['username'])?>
        </span>님 환영합니다!
      <?php else: ?>
        환영합니다!
      <?php endif; ?>
    </h2>
    <p>중고 상품을 사고팔고, 안전하게 소통하세요.</p>
    <img src="TSSPlogofull.png" alt="전체로고" class="main-visual">
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>