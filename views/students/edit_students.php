<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Kiểm tra ID học viên từ URL
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: list_students.php");
    exit();
}

$student_id = intval($_GET['id']);

// Lấy thông tin học viên
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if(!$student){
    echo "Học viên không tồn tại!";
    exit();
}

// Xử lý submit form
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name = $_POST['full_name'];
    $dob = $_POST['dob'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $update_sql = "UPDATE students SET full_name=?, dob=?, email=?, phone=? WHERE id=?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $full_name, $dob, $email, $phone, $student_id);

    if($update_stmt->execute()){
        header("Location: list_students.php");
        exit();
    } else {
        $error = "Cập nhật thất bại!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa học viên - English Center</title>
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
            background-color:#2563eb;
            color:white;
            padding:10px;
            border:none;
            border-radius:6px;
            font-size:1rem;
            cursor:pointer;
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background-color:#1e40af;
        }

        .error {
            color:red;
            margin-bottom:15px;
            text-align:center;
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
        <!-- Sidebar -->
        <?php include '../menu.php'; ?>

        <!-- Main content -->
        <div class="main-content">
            <div class="form-container">
                <h2>Chỉnh sửa học viên</h2>
                <?php if(isset($error)){ echo "<div class='error'>$error</div>"; } ?>
                <form method="POST">
                    <div class="form-group">
                        <label>Họ tên</label>
                        <input type="text" name="full_name" value="<?= htmlspecialchars($student['full_name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh</label>
                        <input type="date" name="dob" value="<?= $student['dob'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>SĐT</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>
                    </div>
                    <button type="submit" class="btn-submit">Cập nhật học viên</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
