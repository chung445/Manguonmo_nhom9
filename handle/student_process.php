<?php
include '../functions/db_connection.php';
$conn = getDbConnection();

ini_set('display_errors', 1);
error_reporting(E_ALL);

/* ==============================
   THÊM HỌC VIÊN
============================== */
if (isset($_POST['add_student'])) {
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("INSERT INTO students (full_name, dob, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $dob, $email, $phone);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/students/list_students.php");
    exit();
}

/* ==============================
   CẬP NHẬT HỌC VIÊN
============================== */
if (isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $stmt = $conn->prepare("UPDATE students SET full_name=?, dob=?, email=?, phone=? WHERE id=?");
    $stmt->bind_param("ssssi", $full_name, $dob, $email, $phone, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../views/students/list_students.php");
    exit();
}

/* ==============================
   XÓA HỌC VIÊN
============================== */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Kiểm tra học viên tồn tại
    $stmt = $conn->prepare("SELECT id FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$res) {
        header("Location: ../views/students/list_students.php?error=notfound");
        exit();
    }

    // Xóa học viên
    $stmtDel = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmtDel->bind_param("i", $id);
    $stmtDel->execute();
    $stmtDel->close();

    header("Location: ../views/students/list_students.php");
    exit();
}

// Nếu không có hành động nào, chuyển về danh sách
header("Location: ../views/students/list_students.php");
exit();
