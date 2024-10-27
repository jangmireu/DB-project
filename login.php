<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // SQL 쿼리 준비
    $sql = "SELECT * FROM users WHERE username = :username";
    try {
        // PDO prepare와 execute 사용하여 데이터 가져오기
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // 로그인 성공
                $_SESSION['username'] = $username;
                echo "<script>
                        alert('성공했습니다');
                        window.location.href = 'survey.html';
                      </script>";
                exit();
            } else {
                // 비밀번호가 틀림
                echo "<script>
                        alert('비밀번호가 틀렸습니다.');
                        window.location.href = 'login.html';
                      </script>";
            }
        } else {
            // 사용자 이름이 틀림
            echo "<script>
                    alert('아이디가 존재하지 않습니다.');
                    window.location.href = 'login.html';
                  </script>";
        }
    } catch (PDOException $e) {
        echo "로그인에 실패했습니다: " . $e->getMessage();
    }
}
?>
