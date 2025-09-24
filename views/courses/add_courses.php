<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách giáo viên
$sql_teachers = "SELECT id, full_name FROM teachers";
$teacherResult = $conn->query($sql_teachers);
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

        form input[type="file"] {
            border: none;
            margin-top: 6px;
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
        <h2>Thêm khóa học</h2>

        <!-- Thêm enctype để upload file -->
        <form action="../../handle/course_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="course_name">Tên khóa học</label>
                <input type="text" id="course_name" name="course_name" required>
            </div>

            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>

            <div class="form-group">
                <label for="fee">Học phí</label>
                <input type="number" id="fee" name="fee" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="teacher_id">Giảng viên phụ trách</label>
                <select id="teacher_id" name="teacher_id">
                    <option value="">-- Chưa gán giảng viên --</option>
                    <?php
                    if ($teacherResult && $teacherResult->num_rows > 0) {
                        while ($t = $teacherResult->fetch_assoc()) {
                            echo "<option value='".$t['id']."'>".$t['full_name']."</option>";
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="photo">Ảnh khóa học</label>
                <input type="file" id="photo" name="photo" accept="image/*">
            </div>

            <button type="submit" name="add_course" class="btn-submit">Thêm khóa học</button>
        </form>
    </div>
</div>
</body>
</html>
