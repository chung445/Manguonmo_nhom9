<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy ID khóa học
$id = $_GET['id'] ?? null;
if(!$id){
    header("Location: list_courses.php");
    exit();
}

// Lấy thông tin khóa học (thêm photo)
$sql = "SELECT id, course_name, description, fee, teacher_id, photo FROM courses WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$course){
    echo "Khóa học không tồn tại!";
    exit();
}

// Lấy danh sách giáo viên
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
        .main-content { flex-grow:1; padding:30px; display:flex; flex-direction:column; align-items:center; }
        .form-container { width:95%; max-width:700px; background:#fff; padding:25px 30px; border-radius:10px; box-shadow:0 2px 12px rgba(0,0,0,0.1); }
        .form-container h2 { text-align:center; margin-bottom:20px; color:#111827; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:600; }
        .form-group input, .form-group textarea, .form-group select { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size:1rem; }
        .course-photo-preview { width:150px; height:110px; object-fit:cover; border-radius:8px; border:1px solid #ddd; display:block; margin-bottom:10px; }
        .btn-submit { display:block; width:100%; background-color:#10b981; color:white; padding:10px; border:none; border-radius:6px; font-size:1rem; cursor:pointer; }
        .btn-submit:hover { background-color:#059669; }
        .back-link { margin-top:15px; display:inline-block; text-decoration:none; color:#2563eb; }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Sửa khóa học</h2>

            <form action="../../handle/course_process.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="update_course" value="1">
                <input type="hidden" name="id" value="<?= (int)$course['id'] ?>">

                <div class="form-group">
                    <label>Tên khóa học:</label>
                    <input type="text" name="course_name" value="<?= htmlspecialchars($course['course_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả:</label>
                    <textarea name="description" rows="4"><?= htmlspecialchars($course['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Học phí:</label>
                    <input type="number" step="0.01" name="fee" value="<?= htmlspecialchars($course['fee']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Giáo viên:</label>
                    <select name="teacher_id">
                        <option value="">-- Chọn giáo viên --</option>
                        <?php while($t = $teachers->fetch_assoc()): ?>
                            <option value="<?= $t['id'] ?>" <?= $t['id']==$course['teacher_id']?'selected':'' ?>>
                                <?= htmlspecialchars($t['full_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Ảnh hiện tại</label><br>
                    <?php if (!empty($course['photo'])): ?>
                        <img src="../../uploads/courses/<?= htmlspecialchars($course['photo']) ?>" alt="Ảnh khóa học" class="course-photo-preview">
                    <?php else: ?>
                        <span style="color:#888; font-style:italic;">Chưa có ảnh</span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Đổi ảnh (nếu muốn)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

                <button type="submit" class="btn-submit">Cập nhật khóa học</button>
            </form>

            <a href="list_courses.php" class="back-link">← Quay lại danh sách khóa học</a>
        </div>
    </div>
</div>
</body>
</html>
