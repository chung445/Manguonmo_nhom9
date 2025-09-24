<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy ID giáo viên từ GET
if(!isset($_GET['id']) || empty($_GET['id'])){
    header("Location: list_teachers.php");
    exit();
}
$id = intval($_GET['id']);

// Lấy dữ liệu giáo viên theo ID (có thêm introduction)
$stmt = $conn->prepare("SELECT id, full_name, dob, email, phone, specialization, photo, introduction FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if(!$teacher){
    echo "Giảng viên không tồn tại!";
    exit();
}

// Xử lý submit
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $full_name      = $_POST['full_name'];
    $dob            = $_POST['dob'];
    $email          = $_POST['email'];
    $phone          = $_POST['phone'];
    $specialization = $_POST['specialization'];
    $introduction   = $_POST['introduction'];

    $photoName = $teacher['photo']; // giữ ảnh cũ

    // Kiểm tra nếu có upload ảnh mới
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK){
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $newName = "teacher_".$id."_".time().".".$ext;
        $uploadDir = __DIR__ . "/../../uploads/teachers/";
        if(!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $uploadPath = $uploadDir . $newName;

        if(move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)){
            $photoName = $newName;
        }
    }

    $stmt = $conn->prepare("UPDATE teachers SET full_name=?, dob=?, email=?, phone=?, specialization=?, introduction=?, photo=? WHERE id=?");
    $stmt->bind_param("sssssssi", $full_name, $dob, $email, $phone, $specialization, $introduction, $photoName, $id);

    if($stmt->execute()){
        header("Location: list_teachers.php");
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
    <title>Chỉnh sửa giảng viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content { flex-grow: 1; padding: 30px; display: flex; justify-content: center; }
        .form-container { width: 100%; max-width: 700px; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.1); }
        .form-container h2 { text-align: center; margin-bottom: 25px; color: #111827; }
        .profile-header { text-align: center; margin-bottom: 20px; }
        .teacher-photo { width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #ddd; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display:block; margin-bottom:6px; font-weight:600; color:#374151; }
        .form-group input, .form-group textarea { width:100%; padding:10px; border:1px solid #ccc; border-radius:6px; font-size: 1rem; }
        textarea { min-height:100px; resize:vertical; }
        .btn-submit { display:block; width:100%; background-color:#10b981; color:white; padding:12px; border:none; border-radius:6px; font-size:1rem; cursor:pointer; transition: all 0.25s ease; }
        .btn-submit:hover { background-color:#059669; }
        .back-link { margin-top:15px; display:inline-block; text-decoration:none; color:#2563eb; }
        .back-link:hover { text-decoration:underline; }
        .error { color:red; margin-bottom:15px; text-align:center; }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Chỉnh sửa giảng viên</h2>

            <?php if(isset($error)){ echo "<div class='error'>$error</div>"; } ?>

            <div class="profile-header">
                <?php if(!empty($teacher['photo'])): ?>
                    <img src="../../uploads/teachers/<?= $teacher['photo'] ?>" alt="Ảnh giảng viên" class="teacher-photo">
                <?php else: ?>
                    <img src="https://via.placeholder.com/120x120?text=No+Photo" alt="Ảnh mặc định" class="teacher-photo">
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Họ tên</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($teacher['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Ngày sinh</label>
                    <input type="date" name="dob" value="<?= $teacher['dob'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($teacher['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($teacher['phone']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Chuyên môn</label>
                    <input type="text" name="specialization" value="<?= htmlspecialchars($teacher['specialization']) ?>">
                </div>
                <div class="form-group">
                    <label>Giới thiệu bản thân</label>
                    <textarea name="introduction"><?= htmlspecialchars($teacher['introduction'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Đổi ảnh</label>
                    <input type="file" name="photo" accept="image/*">
                </div>
                <button type="submit" class="btn-submit">Cập nhật giảng viên</button>
            </form>

            <a href="list_teachers.php" class="back-link">← Quay lại danh sách giảng viên</a>
        </div>
    </div>
</div>
</body>
</html>
