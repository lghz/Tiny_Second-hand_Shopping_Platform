<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 차단된 유저는 등록 불가
$blocked = $mysqli->prepare("SELECT 1 FROM blocks WHERE blocked_user_id=?");
$blocked->bind_param("i", $_SESSION['user_id']);
$blocked->execute();
$blocked->store_result();
if ($blocked->num_rows > 0) {
    echo "<script>alert('해당 계정은 운영 정책 위반으로 인해 차단되었습니다');history.back();</script>";
    exit;
}

$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars(trim($_POST['title']));
    $desc = htmlspecialchars(trim($_POST['description']));
    $price = intval($_POST['price']);
    $img_path = null;
    $error = null;

    // 이미지 업로드 처리
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg','jpeg','png','gif'];
        if (!in_array($ext, $allow)) {
            $error = "이미지 파일(jpg, jpeg, png, gif)만 첨부 가능합니다.";
        } else {
            $img_name = uniqid("img_").".".$ext;
            $dest = $upload_dir . $img_name;
            if (function_exists('imagecreatefromjpeg')) {
                $src_img = null;
                if ($ext === 'jpg' || $ext === 'jpeg') $src_img = @imagecreatefromjpeg($tmp);
                elseif ($ext === 'png') $src_img = @imagecreatefrompng($tmp);
                elseif ($ext === 'gif') $src_img = @imagecreatefromgif($tmp);

                if ($src_img) {
                    $dst_img = imagecreatetruecolor(300, 300);
                    $w = imagesx($src_img);
                    $h = imagesy($src_img);
                    imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, 300, 300, $w, $h);
                    if ($ext === 'jpg' || $ext === 'jpeg') imagejpeg($dst_img, $dest);
                    elseif ($ext === 'png') imagepng($dst_img, $dest);
                    elseif ($ext === 'gif') imagegif($dst_img, $dest);
                    imagedestroy($src_img);
                    imagedestroy($dst_img);
                    $img_path = $img_name;
                } else {
                    $error = "이미지 처리 중 오류가 발생했습니다.";
                }
            } else {
                move_uploaded_file($tmp, $dest);
                $img_path = $img_name;
            }
        }
    }

    if (!$error && $title && $price > 0 && $price % 10 === 0) {
        $stmt = $mysqli->prepare("INSERT INTO products (seller_id, title, description, price, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $_SESSION['user_id'], $title, $desc, $price, $img_path);
        $stmt->execute();
        header("Location: products.php");
        exit;
    } elseif (!$error) {
        $error = "올바르지 않은 금액입니다";
    }
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>상품 등록</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header>
  <div class="container">
    <h1>상품 등록</h1>
    <nav>
      <a href="products.php">상품 목록</a>
      <a href="add_product.php" class="active">상품 등록</a>
      <a href="message.php">메시지</a>
      <a href="points.php">포인트</a>
      <a href="../index.php">메인</a>
    </nav>
  </div>
</header>
<main>
  <div class="main-card" style="max-width:400px;">
    <h2>새 상품 등록</h2>
    <form method="post" enctype="multipart/form-data">
        <input type="text" name="title" placeholder="상품명" required>
        <textarea name="description" placeholder="상품 설명(사진 필수))"></textarea>
        <input type="number" name="price" placeholder="가격(10P 단위)" min="10" step="10" required>
        <input type="file" name="image" accept="image/*" required>
        <button type="submit" class="button">등록</button>
    </form>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <a href="products.php" class="button" style="background:#888;">목록으로</a>
  </div>
</main>
<footer>
  <p>© 2025 Tiny Second-hand Shopping Platform</p>
</footer>
</body>
</html>
