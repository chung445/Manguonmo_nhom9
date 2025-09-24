<?php
session_start();
include '../../functions/db_connection.php';

// Kiểm tra đăng nhập
if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách học viên
$students = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
// Lấy danh sách khóa học
$courses = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name");
// Lấy danh sách giảng viên
$teachers = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm điểm - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <!-- Thêm Font Awesome để hiển thị icon -->
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
        .form-group select,
        .form-group textarea {
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
            display:block;
            margin-top:15px;
            text-align:center;
            color:#2563eb;
            text-decoration:none;
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
    <?php include '../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Thêm điểm</h2>
            <form method="post" action="../../handle/grade_process.php">

                <div class="form-group">
                    <label>Học viên:</label>
                    <select name="student_id" required>
                        <option value="">-- Chọn học viên --</option>
                        <?php while($s = $students->fetch_assoc()): ?>
                            <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Khóa học:</label>
                    <select name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php while($c = $courses->fetch_assoc()): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Giảng viên chấm:</label>
                    <select name="teacher_id">
                        <option value="">-- Không chọn --</option>
                        <?php while($t = $teachers->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['full_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Điểm (0 - 100):</label>
                    <input type="number" step="0.01" min="0" max="100" name="score" required>
                </div>

                <div class="form-group">
                    <label>Ghi chú:</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>

                <button type="submit" name="submit" class="btn-submit">Lưu điểm</button>
            </form>
            <a href="list_grades.php" class="back-link">← Quay lại danh sách điểm</a>
        </div>
    </div>
</div>
</body>
</html>
