<?php
include 'config.php';

function recommendSupplements($answers) {
    $nutrients = [];

    if (in_array($answers['q1'], ['불규칙한 식습관', '패스트푸드를 자주 먹음'])) {
        $nutrients = array_merge($nutrients, ['칼슘', '비타민 A', '비타민 D', '엽산', '철']);
    }
    if ($answers['q2'] === '전혀 하지 않음') {
        $nutrients = array_merge($nutrients, ['비타민 D']);
    }
    if (in_array($answers['q3'], ['6-8시간', '8시간 이상'])) {
        $nutrients = array_merge($nutrients, ['비타민 D', '오메가 3']);
    }
    if (in_array($answers['q4'], ['탄산음료', '커피'])) {
        $nutrients = array_merge($nutrients, ['칼슘', '마그네슘']);
    }
    if (in_array($answers['q5'], ['반 갑 정도(약 10개비)', '한 갑 정도(약 20개비)', '그 이상'])) {
        $nutrients = array_merge($nutrients, ['비타민 C']);
    }
    if (in_array($answers['q6'], ['자주 받음', '매우 많이 받음'])) {
        $nutrients = array_merge($nutrients, ['비타민 C', '마그네슘', '테아닌']);
    }

    $nutrients = array_unique($nutrients);

    $priceRange = $answers['q7'];
    $supplementRecommendation = getSupplementRecommendation($priceRange);

    return [
        'nutrients_needed' => implode(', ', $nutrients),
        'supplement_recommendation' => $supplementRecommendation
    ];
}

function getSupplementRecommendation($priceRange) {
    $recommendations = [
        '1만원 이하' => 'https://example.com/supplement1',
        '1~3만원' => 'https://example.com/supplement2',
        '3~5만원' => 'https://example.com/supplement3',
        '5만원 이상' => 'https://example.com/supplement4'
    ];

    return $recommendations[$priceRange];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form 데이터 가져오기
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $q1 = $_POST['q1'];
    $q2 = $_POST['q2'];
    $q3 = $_POST['q3'];
    $q4 = $_POST['q4'];
    $q5 = $_POST['q5'];
    $q6 = $_POST['q6'];
    $q7 = $_POST['q7'];

    // BMI 계산
    $height_m = $height / 100;  // cm를 m로 변환
    $bmi = $weight / ($height_m * $height_m);

    // 영양제 추천
    $answers = compact('q1', 'q2', 'q3', 'q4', 'q5', 'q6', 'q7');
    $recommendation = recommendSupplements($answers);

    // SQL 쿼리 준비
    $sql = "INSERT INTO survey_results (name, age, gender, height, weight, bmi, q1, q2, q3, q4, q5, q6, q7, nutrients_needed, supplement_recommendation) 
            VALUES (:name, :age, :gender, :height, :weight, :bmi, :q1, :q2, :q3, :q4, :q5, :q6, :q7, :nutrients_needed, :supplement_recommendation)";

    try {
        // PDO prepare와 execute 사용하여 데이터 삽입
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':age' => $age,
            ':gender' => $gender,
            ':height' => $height,
            ':weight' => $weight,
            ':bmi' => $bmi,
            ':q1' => $q1,
            ':q2' => $q2,
            ':q3' => $q3,
            ':q4' => $q4,
            ':q5' => $q5,
            ':q6' => $q6,
            ':q7' => $q7,
            ':nutrients_needed' => $recommendation['nutrients_needed'],
            ':supplement_recommendation' => $recommendation['supplement_recommendation']
        ]);

        // 세션에 마지막 삽입된 ID 저장
        session_start();
        $_SESSION['last_insert_id'] = $pdo->lastInsertId();

        
        echo "설문을 제출하였습니다.";
        
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
