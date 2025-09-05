<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Xử lý submit form
$errors = [];
if(isset($_POST['submit'])) {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $phone     = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $password  = $_POST['password'];

    if(empty($full_name)) $errors[] = "Họ tên không được để trống";
    if(empty($email)) $errors[] = "Email không được để trống";
    if(empty($password)) $errors[] = "Mật khẩu không được để trống";

    if(empty($errors)){
        $stmt = $conn->prepare("INSERT INTO teachers (full_name, email, phone, specialization) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $phone, $specialization);
        if($stmt->execute()){
            header("Location: list_teachers.php");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm giảng viên";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm giảng viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-container {
            width: 95%;
            max-width: 600px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display:block;
            margin-bottom:5px;
            font-weight: 600;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:1rem;
        }

        .btn {
            padding:6px 12px;
            text-decoration:none;
            border-radius:6px;
            color:white;
            font-size:0.9rem;
            display:inline-block;
            margin-right:5px;
            cursor:pointer;
            transition: all 0.25s ease;
        }

        .btn-submit { background-color:#10b981; }
        .btn-submit:hover { background-color:#059669; transform: scale(1.05); }

        .error {
            color:red;
            margin-bottom:10px;
        }

        .back-link {
            margin-top:15px;
            display:inline-block;
            text-decoration:none;
            color:#2563eb;
        }

        .back-link:hover {
            text-decoration:underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../menu.php'; ?>

        <div class="main-content">
            <div class="form-container">
                <h2>Thêm giảng viên</h2>

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php echo implode('<br>', $errors); ?>
                    </div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label>Họ tên:</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>SĐT:</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Chuyên môn:</label>
                        <input type="text" name="specialization">
                    </div>
                    <div class="form-group">
                        <label>Mật khẩu:</label>
                        <input type="password" name="password" required>
                    </div>
                    <button type="submit" name="submit" class="btn btn-submit">Thêm giảng viên</button>
                </form>
                <a href="list_teachers.php" class="back-link">← Quay lại danh sách giảng viên</a>
            </div>
        </div>
    </div>
</body>
</html>
