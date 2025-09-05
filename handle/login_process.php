<?php
session_start();

// include file kết nối
include '../functions/db_connection.php';

// Tạo kết nối
$conn = getDbConnection();  // <-- gọi hàm để có $conn

if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password' LIMIT 1";
    $result = $conn->query($sql);  // $conn bây giờ đã được định nghĩa

    if($result->num_rows == 1){
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = "Sai username hoặc password!";
        header("Location: ../login.php");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
