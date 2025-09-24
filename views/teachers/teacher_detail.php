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

// Lấy thông tin giảng viên
$stmt = $conn->prepare("SELECT id, full_name, dob, email, phone, specialization, photo, introduction FROM teachers WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();

if(!$teacher){
    echo "Giảng viên không tồn tại!";
    exit();
}

// Lấy danh sách khóa học
$stmt = $conn->prepare("SELECT course_name FROM courses WHERE teacher_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res_courses = $stmt->get_result();
$courses = [];
while ($c = $res_courses->fetch_assoc()) { $courses[] = $c['course_name']; }
$stmt->close();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông tin giảng viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <!-- Font Awesome cho icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .main-content { 
            flex-grow: 1; 
            padding: 20px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; 
            justify-content: center; 
        }
        
        .card { 
            max-width: 1000px; 
            width: 100%; 
            background: #fff; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #06b6d4, #10b981);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            padding: 40px 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .profile-top { 
            display: flex; 
            gap: 30px; 
            align-items: center; 
            position: relative;
            z-index: 1;
        }
        
        .teacher-photo { 
            width: 160px; 
            height: 160px; 
            object-fit: cover; 
            border-radius: 50%; 
            border: 4px solid rgba(255,255,255,0.3);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .teacher-photo:hover {
            transform: scale(1.05);
        }
        
        .teacher-info {
            flex: 1;
        }

        .teacher-name { 
            font-size: 2.2rem; 
            font-weight: 700; 
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .teacher-spec { 
            font-size: 1.1rem;
            opacity: 0.9; 
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .status-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
        }

        .card-body {
            padding: 30px;
        }

        .intro-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 4px solid #4f46e5;
            position: relative;
        }

        .intro-section::before {
            content: '"';
            font-size: 4rem;
            color: #4f46e5;
            opacity: 0.2;
            position: absolute;
            top: -10px;
            left: 15px;
            font-family: Georgia, serif;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #1e293b;
        }

        .section-title i {
            color: #4f46e5;
            font-size: 1.1rem;
        }
        
        .info-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); 
            gap: 20px; 
            margin-bottom: 30px; 
        }
        
        .info-box { 
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 20px; 
            border-radius: 12px; 
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #4f46e5, #06b6d4);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .info-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .info-box:hover::before {
            transform: scaleX(1);
        }
        
        .info-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b; 
            font-weight: 600; 
            margin-bottom: 8px;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-label i {
            color: #4f46e5;
        }

        .info-value {
            color: #1e293b;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .courses-section { 
            background: linear-gradient(135deg, #fefefe 0%, #f1f5f9 100%);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
        }

        .course-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }

        .course-tag {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: transform 0.2s ease;
        }

        .course-tag:hover {
            transform: translateY(-2px);
        }

        .no-courses {
            color: #64748b;
            font-style: italic;
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border-radius: 10px;
            border: 2px dashed #cbd5e1;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn { 
            padding: 12px 20px; 
            border-radius: 10px; 
            color: white; 
            text-decoration: none; 
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        
        .btn-edit { 
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
        }
        
        .btn-delete { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.6);
        }
        
        .btn-back { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
        }

        @media (max-width: 768px) {
            .main-content { padding: 15px; }
            .profile-top { flex-direction: column; text-align: center; }
            .teacher-photo { width: 120px; height: 120px; }
            .teacher-name { font-size: 1.8rem; }
            .info-grid { grid-template-columns: 1fr; }
            .action-buttons { justify-content: center; }
        }

        .loading-placeholder {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include '../menu.php'; ?>
    <div class="main-content">
        <div class="card">
            
            <!-- Header với ảnh và thông tin cơ bản -->
            <div class="profile-header">
                <div class="profile-top">
                    <?php if(!empty($teacher['photo'])): ?>
                        <img src="../../uploads/teachers/<?= $teacher['photo'] ?>" alt="Ảnh giảng viên" class="teacher-photo">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/160?text=No+Photo" alt="Chưa có ảnh" class="teacher-photo">
                    <?php endif; ?>

                    <div class="teacher-info">
                        <div class="teacher-name"><?= htmlspecialchars($teacher['full_name']) ?></div>
                        <div class="teacher-spec">
                            <i class="fas fa-graduation-cap"></i>
                            <?= htmlspecialchars($teacher['specialization']) ?>
                            <span class="status-badge">
                                <i class="fas fa-check-circle"></i> Hoạt động
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body">
 
                </div>

                <!-- Thông tin cá nhân -->
                <div class="section-title">
                    <i class="fas fa-user-circle"></i>
                    Thông tin cá nhân
                </div>
                <div class="info-grid">
                    <div class="info-box">
                        <div class="info-label">
                            <i class="fas fa-birthday-cake"></i>
                            Ngày sinh
                        </div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($teacher['dob'])) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">
                            <i class="fas fa-envelope"></i>
                            Email
                        </div>
                        <div class="info-value"><?= htmlspecialchars($teacher['email']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">
                            <i class="fas fa-phone"></i>
                            Số điện thoại
                        </div>
                        <div class="info-value"><?= htmlspecialchars($teacher['phone']) ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">
                            <i class="fas fa-medal"></i>
                            Chuyên môn
                        </div>
                        <div class="info-value"><?= htmlspecialchars($teacher['specialization']) ?></div>
                    </div>
                </div>

                <!-- Khóa học đang dạy -->
                <div class="courses-section">
                    <div class="section-title">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Khóa học đang dạy
                        <?php if(!empty($courses)): ?>
                            <span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 500;">
                                <?= count($courses) ?> khóa học
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(!empty($courses)): ?>
                        <div class="course-list">
                            <?php foreach($courses as $c): ?>
                                <div class="course-tag">
                                    <i class="fas fa-book"></i>
                                    <?= htmlspecialchars($c) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-courses">
                            <i class="fas fa-book-open"></i>
                            Chưa được phân công dạy khóa học nào
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hành động -->
                <div class="action-buttons">
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="edit_teachers.php?id=<?= $teacher['id'] ?>" class="btn btn-edit">
                            <i class="fas fa-edit"></i>
                            Chỉnh sửa thông tin
                        </a>
                        <a href="../../handle/teacher_process.php?delete=<?= $teacher['id'] ?>" class="btn btn-delete" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa giảng viên này không?')">
                            <i class="fas fa-trash-alt"></i>
                            Xóa giảng viên
                        </a>
                    <?php endif; ?>
                    
                    <a href="list_teachers.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i>
                        Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Thêm hiệu ứng loading cho ảnh
document.addEventListener('DOMContentLoaded', function() {
    const img = document.querySelector('.teacher-photo');
    if (img) {
        img.classList.add('loading-placeholder');
        img.onload = function() {
            img.classList.remove('loading-placeholder');
        }
    }
});
</script>
</body>
</html>