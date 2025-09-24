<?php
session_start();
include '../../functions/db_connection.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Kiểm tra có id trên URL không
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: list_teachers.php");
    exit();
}

$id = intval($_GET['id']);

// Chuẩn bị câu lệnh DELETE
$stmt = $conn->prepare("DELETE FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);

if($stmt->execute()){
    // Xóa thành công
    header("Location: list_teachers.php?msg=deleted");
    exit();
} else {
    echo "Xóa thất bại: " . $conn->error;
}
