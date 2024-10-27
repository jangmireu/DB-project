<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form 데이터 가져오기
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // 비밀번호 해시화
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];

    // SQL 쿼리 준비
    $sql = "INSERT INTO users (username, password, email, name, phone, birthdate, gender) VALUES (:username, :password, :email, :name, :phone, :birthdate, :gender)";

    try {
        // PDO prepare와 execute 사용하여 데이터 삽입
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':name' => $name,
            ':phone' => $phone,
            ':birthdate' => $birthdate,
            ':gender' => $gender,
        ]);
        // 회원가입 성공 시 login.html로 리디렉션
        header("Location: login.html");
        exit();
    } catch (PDOException $e) {
        echo "회원가입에 실패했습니다: " . $e->getMessage();
    }
}
?>
