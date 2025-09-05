<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

if (!isset($_GET['id'])) {
    header("Location: list_courses.php");
    exit();
}

$id = $_GET['id'];

// Lấy thông tin khóa học
$sql = "SELECT id, course_name, description, fee, teacher_id FROM courses WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    echo "Khóa học không tồn tại";
    exit();
}
$course = $result->fetch_assoc();

// Lấy danh sách giáo viên để chọn
$teachers = $conn->query("SELECT id, full_name FROM teachers");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa khóa học - English Center</title>
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
            width: 90%;
            max-width: 600px;
            background: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], textarea, select {
            width: 100%;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        textarea { resize: vertical; }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            margin-right: 10px;
            display: inline-block;
        }

        .btn-save { background-color: #2563eb; }
        .btn-save:hover { background-color: #1d4ed8; }

        .btn-cancel { background-color: #ef4444; }
        .btn-cancel:hover { background-color: #dc2626; }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/../menu.php'; ?>

        <div class="main-content">
            <div class="form-container">
                <h2>Sửa khóa học</h2>
                <form action="../../handle/course_process.php" method="POST">
                    <input type="hidden" name="id" value="<?= $course['id'] ?>">

                    <div class="form-group">
                        <label>Tên khóa học</label>
                        <input type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Mô tả</label>
                        <textarea name="description" rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Học phí</label>
                        <input type="text" name="fee" value="<?= htmlspecialchars($course['fee']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Giáo viên</label>
                        <select name="teacher_id">
                            <option value="">-- Chọn giáo viên --</option>
                            <?php while($t = $teachers->fetch_assoc()): ?>
                                <option value="<?= $t['id'] ?>" <?= $t['id']==$course['teacher_id']?'selected':'' ?>>
                                    <?= htmlspecialchars($t['full_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div style="text-align:center; margin-top:20px;">
                        <button type="submit" name="update_course" class="btn btn-save">Lưu thay đổi</button>
                        <a href="list_courses.php" class="btn btn-cancel">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
--- a/file:///c%3A/xampp/htdocs/BTL/views/teachers/edit_teachers.php