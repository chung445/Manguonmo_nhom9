<?php
include '../functions/db_connection.php';
$conn = getDbConnection();

// Thêm giảng viên
if (isset($_POST['add'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Thêm user
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, 'teacher')");
    $stmt->bind_param("sss", $full_name, $email, $password);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // Thêm giảng viên
    $stmt2 = $conn->prepare("INSERT INTO teachers (user_id, phone, specialization) VALUES (?, ?, ?)");
    $stmt2->bind_param("iss", $user_id, $phone, $specialization);
    $stmt2->execute();
    $stmt2->close();

    header("Location: ../views/teachers/list_teacher.php");
    exit();
}

// Sửa giảng viên
if (isset($_POST['edit'])) {
    $id = $_POST['id'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];

    // Cập nhật bảng users
    $sql_user = "UPDATE users 
                 JOIN teachers ON users.id = teachers.user_id 
                 SET users.full_name = ?, users.email = ? 
                 WHERE teachers.id = ?";
    $stmt = $conn->prepare($sql_user);
    $stmt->bind_param("ssi", $full_name, $email, $id);
    $stmt->execute();
    $stmt->close();

    // Cập nhật bảng teachers
    $sql_teacher = "UPDATE teachers SET phone = ?, specialization = ? WHERE id = ?";
    $stmt2 = $conn->prepare($sql_teacher);
    $stmt2->bind_param("ssi", $phone, $specialization, $id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: ../views/teachers/list_teacher.php");
    exit();
}

// Xóa giảng viên
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Lấy user_id của giảng viên
    $sql = "SELECT user_id FROM teachers WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    if ($user_id) {
        // Xóa user trước (tránh lỗi FK)
        $stmt3 = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt3->bind_param("i", $user_id);
        $stmt3->execute();
        $stmt3->close();
    }

    // Sau đó xóa teacher
    $stmt2 = $conn->prepare("DELETE FROM teachers WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    header("Location: ../views/teachers/list_teachers.php?msg=deleted");
    exit();
}
?>
