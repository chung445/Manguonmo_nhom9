<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy ID grade từ GET
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: list_grades.php");
    exit();
}
$id = intval($_GET['id']);

// Lấy dữ liệu grade theo ID
$stmt = $conn->prepare("SELECT * FROM grades WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$grade = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$grade){
    echo "Không tìm thấy bản ghi!";
    exit();
}

// Lấy danh sách sinh viên, khóa học, giảng viên
$students = $conn->query("SELECT id, full_name FROM students ORDER BY full_name");
$courses  = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name");
$teachers = $conn->query("SELECT id, full_name FROM teachers ORDER BY full_name");

// Xử lý khi submit form
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $student_id   = intval($_POST['student_id']);
    $course_id    = intval($_POST['course_id']);
    $teacher_id   = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : NULL;
    $score        = floatval($_POST['score']);
    $grade_letter = trim($_POST['grade_letter']);
    $notes        = trim($_POST['notes']);

    $stmt = $conn->prepare("UPDATE grades 
                            SET student_id = ?, course_id = ?, teacher_id = ?, score = ?, grade_letter = ?, notes = ?
                            WHERE id = ?");
    $stmt->bind_param("iiidssi", $student_id, $course_id, $teacher_id, $score, $grade_letter, $notes, $id);

    if($stmt->execute()){
        // redirect về danh sách (dùng đường dẫn tương đối với cấu trúc của bạn)
        header("Location: /BTLSC/views/grades/list_grades.php?msg=updated");
        exit();
    } else {
        $error = "Cập nhật thất bại: " . $stmt->error;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chỉnh sửa điểm - English Center</title>
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
        .form-group { margin-bottom: 15px; }
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
            font-size:1rem;
            box-sizing: border-box;
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
        .btn-submit:hover { background-color:#059669; }
        .back-link {
            margin-top:15px;
            display:inline-block;
            text-decoration:none;
            color:#2563eb;
        }
        .back-link:hover { text-decoration:underline; }
        .error {
            color:red;
            margin-bottom:15px;
            text-align:center;
        }
        @media(max-width:768px){
            .form-container { width: 100%; padding:20px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar / menu -->
        <?php include __DIR__ . '/../menu.php'; ?>

        <!-- Main content -->
        <div class="main-content">
            <div class="form-container">
                <h2>Chỉnh sửa điểm</h2>

                <?php if(isset($error)): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-group">
                        <label>Sinh viên</label>
                        <select name="student_id" required>
                            <?php
                            // rewind result set in case it was used earlier
                            $students->data_seek(0);
                            while($s = $students->fetch_assoc()){
                                $sel = ($s['id'] == $grade['student_id']) ? 'selected' : '';
                                echo '<option value="'. $s['id'] .'" '. $sel .'>'. htmlspecialchars($s['full_name']) .'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Khóa học</label>
                        <select name="course_id" required>
                            <?php
                            $courses->data_seek(0);
                            while($c = $courses->fetch_assoc()){
                                $sel = ($c['id'] == $grade['course_id']) ? 'selected' : '';
                                echo '<option value="'. $c['id'] .'" '. $sel .'>'. htmlspecialchars($c['course_name']) .'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Giảng viên</label>
                        <select name="teacher_id">
                            <option value="">-- Chưa chọn --</option>
                            <?php
                            $teachers->data_seek(0);
                            while($t = $teachers->fetch_assoc()){
                                $sel = ($t['id'] == $grade['teacher_id']) ? 'selected' : '';
                                echo '<option value="'. $t['id'] .'" '. $sel .'>'. htmlspecialchars($t['full_name']) .'</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Điểm số</label>
                        <input type="number" name="score" step="0.01" min="0" max="100" required
                               value="<?= htmlspecialchars($grade['score']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Grade letter</label>
                        <input type="text" name="grade_letter" maxlength="2" value="<?= htmlspecialchars($grade['grade_letter']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Ghi chú</label>
                        <textarea name="notes" rows="3"><?= htmlspecialchars($grade['notes']) ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Cập nhật điểm</button>
                </form>

                <a href="list_grades.php" class="back-link">← Quay lại danh sách điểm</a>
            </div>
        </div>
    </div>
</body>
</html>
