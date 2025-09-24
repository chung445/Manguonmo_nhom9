<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy ID học viên
$id = $_GET['id'] ?? null;
if(!$id){
    header("Location: list_students.php");
    exit();
}

// Lấy thông tin học viên
$sql = "SELECT * FROM students WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$student){
    echo "Học viên không tồn tại!";
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa học viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-container {
            width: 95%;
            max-width: 600px;
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.1);
        }
        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #111827;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display:block;
            margin-bottom:5px;
            font-weight:600;
        }
        .form-group input {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size: 1rem;
        }
        .btn-submit {
            display:block;
            width:100%;
            background-color:#10b981;
            color:white;
            padding:10px;
            border:none;
            border-radius:6px;
            font-size:1rem;
            cursor:pointer;
            transition: all 0.25s ease;
        }
        .btn-submit:hover {
            background-color:#059669;
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
        @media(max-width:768px){
            .form-container {
                width: 100%;
                padding:20px;
            }
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Sửa học viên</h2>
            <form action="../../handle/student_process.php" method="POST">
                <input type="hidden" name="update_student" value="1">
                <input type="hidden" name="id" value="<?= $student['id'] ?>">

                <div class="form-group">
                    <label>Họ tên:</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Ngày sinh:</label>
                    <input type="date" name="dob" value="<?= $student['dob'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Số điện thoại:</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>
                </div>

                <button type="submit" class="btn-submit">Cập nhật học viên</button>
            </form>
            <a href="list_students.php" class="back-link">← Quay lại danh sách học viên</a>
        </div>
    </div>
</div>
</body>
</html>
