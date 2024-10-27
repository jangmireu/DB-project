<?php
include 'config.php';
session_start();

if (!isset($_SESSION['last_insert_id'])) {
    echo json_encode(['error' => 'No survey result found.']);
    exit;
}

$id = $_SESSION['last_insert_id'];

// SQL 쿼리 준비
$sql = "SELECT * FROM survey_results WHERE id = :id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $bmi = $result['bmi'];
        $bmiStatus = '';
        $q7 = $result['q7'];
        $nutrients_needed = array_filter(array_map('trim', explode(',', $result['nutrients_needed'])));

        // BMI 상태 결정
        if ($bmi < 18.5) {
            $bmiStatus = '저체중';
        } elseif ($bmi >= 18.5 && $bmi < 24.9) {
            $bmiStatus = '정상 체중';
        } elseif ($bmi >= 25 && $bmi < 29.9) {
            $bmiStatus = '과체중';
        } else {
            $bmiStatus = '비만';
        }

        // 가격 조건 설정 및 테이블 선택
        switch ($q7) {
            case "1만원 이상":
                $price_condition = "BETWEEN 1 AND 10000";
                $table = 'q7-1';
                break;
            case "1~3만원":
                $price_condition = "BETWEEN 10000 AND 30000";
                $table = 'q7-2';
                break;
            case "3~5만원":
                $price_condition = "BETWEEN 30000 AND 50000";
                $table = 'q7-3';
                break;
            case "5만원 이상":
                $price_condition = "BETWEEN 50000 AND 10000000";
                $table = 'q7-4';
                break;
            default:
                $price_condition = "BETWEEN 1 AND 10000000";
                $table = 'q7-1'; // 기본 테이블 설정
                break;
        }

        // final 테이블 초기화
        $pdo->exec("TRUNCATE TABLE final");

        // 각 영양소에 대해 확인하고 데이터 삽입
        foreach ($nutrients_needed as $nutrient) {
            if (empty($nutrient)) {
                continue;
            }
            $sql_suggest = "SELECT name, nutrients, price, link FROM `$table` WHERE nutrients = :nutrient";
            $stmt_suggest = $pdo->prepare($sql_suggest);
            $stmt_suggest->execute([':nutrient' => $nutrient]);

            while ($row_suggest = $stmt_suggest->fetch(PDO::FETCH_ASSOC)) {
                // final 테이블에 데이터 삽입 전 중복 확인
                $name = $row_suggest['name'];
                $nutrients = $row_suggest['nutrients'];
                $price = $row_suggest['price'];
                $link = $row_suggest['link'];

                $check_sql = "SELECT COUNT(*) as count FROM final WHERE name=:name AND price=:price";
                $stmt_check = $pdo->prepare($check_sql);
                $stmt_check->execute([':name' => $name, ':price' => $price]);
                $check_row = $stmt_check->fetch(PDO::FETCH_ASSOC);

                if ($check_row['count'] == 0) {
                    $insert_sql = "INSERT INTO final (name, nutrients, price, link) VALUES (:name, :nutrients, :price, :link)";
                    $stmt_insert = $pdo->prepare($insert_sql);
                    $stmt_insert->execute([':name' => $name, ':nutrients' => $nutrients, ':price' => $price, ':link' => $link]);
                }
            }
        }

        // HTML 출력
        echo "<!DOCTYPE html>
        <html lang='ko'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>영양제 추천</title>
            <link rel='stylesheet' href='styles.css'>
        </head>
        <body>
            <header>
                <a href='index.html' class='logo'>2T1F</a>
                <nav>
                    <ul>
                        <li><a href='login.html'>로그인</a></li>
                        <li><a href='survey.html'>설문조사</a></li>
                        <li><a href='#reviews'>고객리뷰</a></li>
                        <li><a href='store.html'>스토어</a></li>
                    </ul>
                </nav>
            </header>
            <main>
                <section class='product-list'>
                    <div class='recommended-products'>
                        <h2>추천 상품</h2>
                        <div class='product-items'>";

        // final 테이블에서 데이터 가져오기
        $sql_final = "SELECT name, nutrients, price, link FROM final";
        $stmt_final = $pdo->query($sql_final);

        if ($stmt_final->rowCount() > 0) {
            while ($row = $stmt_final->fetch(PDO::FETCH_ASSOC)) {
                // 이미지 링크를 절대 경로로 변환
                $image_link = 'http://localhost/dbdb/' . basename($row['link']);
                echo "<div class='product-item'>";
                echo "<img src='" . $image_link . "' alt='" . $row['name'] . "'>";
                echo "<h3>" . $row['name'] . "</h3>";
                echo "<p>가격: " . $row['price'] . "원</p>";
                echo "</div>";
            }
        } else {
            echo "<p>추천할 영양제가 없습니다.</p>";
        }

        echo "                </div>
                    </div>
                </section>
            </main>
        </body>
        </html>";

    } else {
        echo json_encode(['error' => 'No survey result found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
