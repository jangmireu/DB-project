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
        $supplementRecommendation = $result['supplement_recommendation'];

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

        // 결과 반환
        echo json_encode([
            'name' => $result['name'],
            'age' => $result['age'],
            'gender' => $result['gender'],
            'height' => $result['height'],
            'weight' => $result['weight'],
            'bmi' => $result['bmi'],
            'bmiStatus' => $bmiStatus,
            'nutrients_needed' => $result['nutrients_needed'],
            'supplement_recommendation' => $result['supplement_recommendation']
        ]);
    } else {
        echo json_encode(['error' => 'No survey result found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>
