<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách giáo viên để chọn cho khóa học
$teacherResult = $conn->query("SELECT id, full_name FROM teachers");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm khóa học - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 30px 30px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        form {
            width: 95%;
            max-width: 800px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        form .form-group {
            margin-bottom: 15px;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="number"],
        form textarea,
        form select {
            width: 100%;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        form textarea {
            resize: vertical;
        }

        .btn-submit {
            background-color: #10b981;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .btn-submit:hover {
            background-color: #059669;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h2>Thêm khóa học mới</h2>
        <form action="../../handle/course_process.php" method="POST">
            <div class="form-group">
                <label for="course_name">Tên khóa học</label>
                <input type="text" name="course_name" id="course_name" required>
            </div>
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea name="description" id="description" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="fee">Học phí</label>
                <input type="number" name="fee" id="fee" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="teacher_id">Giảng viên</label>
                <select name="teacher_id" id="teacher_id">
                    <option value="">-- Chọn giảng viên --</option>
                    <?php
                    if ($teacherResult && $teacherResult->num_rows > 0) {
                        while ($row = $teacherResult->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['full_name']}</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <button type="submit" name="add_course" class="btn-submit">Thêm khóa học</button>
        </form>
    </div>
</div>
</body>
</html>
