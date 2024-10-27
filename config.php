<?php
$host = 'localhost'; // 데이터베이스 서버 호스트
$db = '2T1F'; // 데이터베이스 이름
$user = 'root'; // 데이터베이스 사용자 이름
$pass = '0122'; // 데이터베이스 비밀번호

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
    exit;
}
?>
