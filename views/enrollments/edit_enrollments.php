<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy id từ GET
if(!isset($_GET['id'])){
    header("Location: list_enrollments.php");
    exit();
}

$id = $_GET['id'];

// Lấy thông tin ghi danh hiện tại
$sql = "SELECT * FROM enrollments WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows == 0){
    header("Location: list_enrollments.php");
    exit();
}

$enrollment = $res->fetch_assoc();
$stmt->close();

// Lấy danh sách học viên và khóa học
$students = $conn->query("SELECT id, full_name FROM students");
$courses  = $conn->query("SELECT id, course_name FROM courses");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa ghi danh - English Center</title>
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
        .form-group input,
        .form-group select {
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
            <h2>Sửa ghi danh</h2>
            <form action="../../handle/enrollment_process.php" method="POST">
                <input type="hidden" name="update_enrollment" value="1">
                <input type="hidden" name="id" value="<?= $enrollment['id'] ?>">

                <div class="form-group">
                    <label>Học viên:</label>
                    <select name="student_id" required>
                        <option value="">-- Chọn học viên --</option>
                        <?php while($row = $students->fetch_assoc()){ ?>
                            <option value="<?= $row['id'] ?>" <?= $row['id']==$enrollment['student_id'] ? 'selected' : '' ?>>
                                <?= $row['full_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Khóa học:</label>
                    <select name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php while($row = $courses->fetch_assoc()){ ?>
                            <option value="<?= $row['id'] ?>" <?= $row['id']==$enrollment['course_id'] ? 'selected' : '' ?>>
                                <?= $row['course_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày ghi danh:</label>
                    <input type="date" name="enrollment_date" value="<?= $enrollment['enrollment_date'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Trạng thái:</label>
                    <select name="status">
                        <option value="active" <?= $enrollment['status']=='active' ? 'selected' : '' ?>>Active</option>
                        <option value="completed" <?= $enrollment['status']=='completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $enrollment['status']=='cancelled' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">Cập nhật ghi danh</button>
            </form>
            <a href="list_enrollments.php" class="back-link">← Quay lại danh sách ghi danh</a>
        </div>
    </div>
</div>
</body>
</html>