<?php
session_start();
include __DIR__ . '/../functions/db_connection.php';

$conn = getDbConnection();

// =========================
// Hàm redirect tiện ích
// =========================
function redirect($url) {
    header("Location: $url");
    exit();
}

// =========================
// THÊM GHI DANH (student tự đăng ký hoặc admin thêm 1 sinh viên)
// =========================
if (isset($_POST['add_enrollment'])) {
    $student_id      = intval($_POST['student_id']);
    $course_id       = intval($_POST['course_id']);
    $enrollment_date = !empty($_POST['enrollment_date']) ? $_POST['enrollment_date'] : date('Y-m-d');
    $status          = $_POST['status'] ?? 'active';

    $sql = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $student_id, $course_id, $enrollment_date, $status);
    $stmt->execute();
    $stmt->close();

    redirect("../views/enrollments/list_enrollments.php");
}

// =========================
// CẬP NHẬT GHI DANH
// =========================
if (isset($_POST['update_enrollment'])) {
    $id             = intval($_POST['id']);
    $student_id     = intval($_POST['student_id']);
    $course_id      = intval($_POST['course_id']);
    $enrollment_date = $_POST['enrollment_date'];
    $status          = $_POST['status'];

    $sql = "UPDATE enrollments 
            SET student_id = ?, course_id = ?, enrollment_date = ?, status = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissi", $student_id, $course_id, $enrollment_date, $status, $id);
    $stmt->execute();
    $stmt->close();

    redirect("../views/enrollments/list_enrollments.php");
}

// =========================
// XÓA GHI DANH
// =========================
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $sql = "DELETE FROM enrollments WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    redirect("../views/enrollments/list_enrollments.php");
}

// =========================
// BULK ADD (Admin thêm nhiều sinh viên vào 1 khóa học)
// =========================
if (isset($_POST['action']) && $_POST['action'] === 'bulk_add') {
    $course_id   = intval($_POST['course_id']);
    $student_ids = $_POST['student_ids'] ?? [];
    $date_now    = date('Y-m-d');

    if (!empty($student_ids)) {
        $sql = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status) 
                VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        foreach ($student_ids as $sid) {
            $sid = intval($sid);
            $stmt->bind_param("iis", $sid, $course_id, $date_now);
            $stmt->execute();
        }
        $stmt->close();
    }

    redirect("../views/enrollments/list_enrollments.php");
}

// =========================
// Student tự đăng ký (link từ list_enrollments.php)
// =========================
if (isset($_GET['course_id']) && isset($_GET['student_id'])) {
    $student_id = intval($_GET['student_id']);
    $course_id  = intval($_GET['course_id']);
    $date_now   = date('Y-m-d');

    // Kiểm tra đã tồn tại chưa
    $check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check->bind_param("ii", $student_id, $course_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $sql = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status) 
                VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $student_id, $course_id, $date_now);
        $stmt->execute();
        $stmt->close();
    }
    $check->close();

    redirect("../views/enrollments/list_enrollments.php");
}

?>
