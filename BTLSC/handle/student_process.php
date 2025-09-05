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
    $email = $_POST['email'];
    $password = password_hash("123456", PASSWORD_DEFAULT); // Mật khẩu mặc định
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Thêm vào bảng users
    $stmtUser = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'student')");
    $stmtUser->bind_param("sss", $full_name, $email, $password);
    if ($stmtUser->execute()) {
        $user_id = $stmtUser->insert_id;

        // Thêm vào bảng students
        $stmtStudent = $conn->prepare("INSERT INTO students (user_id, phone, address) VALUES (?, ?, ?)");
        $stmtStudent->bind_param("iss", $user_id, $phone, $address);
        $stmtStudent->execute();
        $stmtStudent->close();
    }
    $stmtUser->close();

    header("Location: ../views/students/list_students.php");
    exit();
}

/* ==============================
   CẬP NHẬT HỌC VIÊN
============================== */
if (isset($_POST['update_student'])) {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Lấy user_id từ bảng students
    $stmt = $conn->prepare("SELECT user_id FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if(!$res || !$res['user_id']) {
        header("Location: ../views/students/list_students.php?error=notfound");
        exit();
    }
    $user_id = $res['user_id'];

    // Cập nhật bảng users
    $stmtUser = $conn->prepare("UPDATE users SET full_name=?, email=? WHERE id=?");
    $stmtUser->bind_param("ssi", $full_name, $email, $user_id);
    $stmtUser->execute();
    $stmtUser->close();

    // Cập nhật bảng students
    $stmtStudent = $conn->prepare("UPDATE students SET phone=?, address=? WHERE id=?");
    $stmtStudent->bind_param("ssi", $phone, $address, $id);
    $stmtStudent->execute();
    $stmtStudent->close();

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

    // Xóa học viên trong bảng students
    $stmtDelStudent = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmtDelStudent->bind_param("i", $id);
    $stmtDelStudent->execute();
    $stmtDelStudent->close();

    header("Location: ../views/students/list_students.php");
    exit();
}

// Nếu không có hành động nào, chuyển về danh sách
header("Location: ../views/students/list_students.php");
exit();