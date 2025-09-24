<?php
session_start();
include '../functions/db_connection.php'; // file kết nối DB


$conn = getDbConnection();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $dob       = $_POST['dob'];
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $username  = trim($_POST['username']);
    $password  = trim($_POST['password']);

    // Kiểm tra username đã tồn tại chưa
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Username đã tồn tại, vui lòng chọn tên khác.";
        header("Location: ../register.php");
        exit;
    }

    // Thêm vào bảng users (role = student)
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student')");
    $stmt->bind_param("ss", $username, $password); // ⚠️ bạn nên hash password trong thực tế
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        // Thêm vào bảng students và liên kết user_id
        $stmt2 = $conn->prepare("INSERT INTO students (full_name, dob, email, phone, user_id) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssssi", $full_name, $dob, $email, $phone, $user_id);
        $stmt2->execute();

        $_SESSION['success'] = "Đăng ký thành công! Vui lòng đăng nhập.";
        header("Location: ../register.php");
        exit;
    } else {
        $_SESSION['error'] = "Có lỗi xảy ra, vui lòng thử lại.";
        header("Location: ../register.php");
        exit;
    }
}
