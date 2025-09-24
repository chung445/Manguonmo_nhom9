<?php
session_start();
include '../functions/db_connection.php'; // sửa lại 1 dấu ..

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php");
    exit();
}

$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $student_id  = isset($_POST['student_id']) ? intval($_POST['student_id']) : null;
    $course_id   = isset($_POST['course_id']) ? intval($_POST['course_id']) : null;
    $teacher_id  = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;
    $score       = isset($_POST['score']) ? floatval($_POST['score']) : null;
    $notes       = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    // Kiểm tra dữ liệu đầu vào
    if ($student_id === null || $course_id === null || $score === null) {
        echo "Thiếu thông tin bắt buộc.";
        exit();
    }

    // Tính điểm chữ
    $grade_letter = match (true) {
        $score >= 85 => "A",
        $score >= 70 => "B",
        $score >= 55 => "C",
        $score >= 40 => "D",
        default      => "F",
    };

    $stmt = $conn->prepare("
        INSERT INTO grades (student_id, course_id, teacher_id, score, grade_letter, notes) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        echo "Lỗi chuẩn bị truy vấn: " . $conn->error;
        exit();
    }

    $stmt->bind_param("iiidss", $student_id, $course_id, $teacher_id, $score, $grade_letter, $notes);

    if ($stmt->execute()) {
        header("Location: ../views/grades/list_grades.php?msg=success");
        exit();
    } else {
        echo "Lỗi khi thêm điểm: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
