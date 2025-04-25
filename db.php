<?php
// php/db.php
$mysqli = new mysqli("localhost", "root", "", "tiny_secondhand");
if ($mysqli->connect_errno) {
    die("DB 연결 실패: " . $mysqli->connect_error);
}
?>