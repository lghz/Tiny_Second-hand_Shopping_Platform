<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// 차단된 유저는 불가
$blocked = $mysqli->prepare("SELECT 1 FROM blocks WHERE blocked_user_id=?");
$blocked->bind_param("i", $_SESSION['user_id']);
$blocked->execute();
$blocked->store_result();
if ($blocked->num_rows > 0) {
    echo "<script>alert('해당 계정은 운영 정책 위반으로 인해 차단되었습니다');history.back();</script>";
    exit;
}

$buyer_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $pid = intval($_POST['product_id']);
    // 상품 정보 조회
    $stmt = $mysqli->prepare("SELECT seller_id, price, is_sold FROM products WHERE id=?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    $stmt->bind_result($seller_id, $price, $is_sold);
    if ($stmt->fetch()) {
        $stmt->close();
        if ($is_sold) {
            $error = "이미 판매된 상품입니다.";
        } elseif ($buyer_id == $seller_id) {
            $error = "자신의 상품은 구매할 수 없습니다.";
        } else {
            // 구매자 포인트 확인
            $stmt2 = $mysqli->prepare("SELECT balance FROM points WHERE user_id=?");
            $stmt2->bind_param("i", $buyer_id);
            $stmt2->execute();
            $stmt2->bind_result($balance);
            $stmt2->fetch();
            $stmt2->close();
            if ($balance < $price) {
                $error = "보유 포인트가 부족합니다.";
            } else {
                // 판매자 포인트 계좌가 없으면 생성
                $stmt_check = $mysqli->prepare("SELECT balance FROM points WHERE user_id=?");
                $stmt_check->bind_param("i", $seller_id);
                $stmt_check->execute();
                $stmt_check->store_result();
                if ($stmt_check->num_rows == 0) {
                    $stmt_insert = $mysqli->prepare("INSERT INTO points (user_id, balance) VALUES (?, 0)");
                    $stmt_insert->bind_param("i", $seller_id);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
                $stmt_check->close();

                $mysqli->begin_transaction();
                $ok1 = $mysqli->query("UPDATE points SET balance=balance-$price WHERE user_id=$buyer_id");
                $ok2 = $mysqli->query("UPDATE points SET balance=balance+$price WHERE user_id=$seller_id");
                $ok3 = $mysqli->prepare("INSERT INTO transactions (from_user, to_user, amount) VALUES (?, ?, ?)");
                $ok3->bind_param("iii", $buyer_id, $seller_id, $price);
                $ok3->execute();
                $ok3->close();
                $ok4 = $mysqli->query("UPDATE products SET is_sold=1 WHERE id=$pid");
                if ($ok1 && $ok2 && $ok4) {
                    $mysqli->commit();
                    $success = "구매가 완료되었습니다!";
                } else {
                    $mysqli->rollback();
                    $error = "구매 처리 중 오류가 발생했습니다.";
                }
            }
        }
    } else {
        $error = "상품 정보를 찾을 수 없습니다.";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>구매 결과</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>구매 결과</h1>
    <nav>
      <a href="products.php">상품 목록</a>
      <a href="add_product.php">상품 등록</a>
      <a href="points.php">포인트</a>
      <a href="../index.php">메인</a>
    </nav>
  </div>
</header>
<main>
  <div class="main-card" style="max-width:400px;">
    <h2>구매 결과</h2>
    <?php
    if (isset($success)) echo "<p class='success'>$success</p>";
    if (isset($error)) echo "<p class='error'>$error</p>";
    ?>
    <a href="products.php" class="button" style="background:#888;">상품 목록으로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>

