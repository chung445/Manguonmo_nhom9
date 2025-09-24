<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy id khóa học từ URL
if (!isset($_GET['id'])) {
    header("Location: list_courses.php");
    exit();
}
$course_id = intval($_GET['id']);

// Lấy thông tin khóa học + giảng viên + số sinh viên
$sql = "SELECT c.id, c.course_name, c.description, c.fee, c.photo,
               t.full_name AS teacher_name,
               COUNT(e.student_id) AS total_students
        FROM courses c
        LEFT JOIN teachers t ON c.teacher_id = t.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        WHERE c.id = ?
        GROUP BY c.id, c.course_name, c.description, c.fee, c.photo, t.full_name";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course) {
    die("Khóa học không tồn tại");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết khóa học - <?= htmlspecialchars($course['course_name']) ?></title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content { 
            flex-grow: 1; 
            padding: 30px; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex; 
            justify-content: center;   /* căn giữa ngang */
            align-items: flex-start;   /* giữ card dính trên, không giữa dọc */
        }

        .course-container {
            width: 100%;
            max-width: 1300px;   /* tăng thêm để card rộng hơn */
            margin: 0 auto;      /* căn giữa */
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .course-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4f46e5, #06b6d4, #10b981);
        }

        .course-header {
            background: linear-gradient(135deg, #4f46e5 0%, #06b6d4 100%);
            padding: 40px 30px 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .course-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .course-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .course-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
            z-index: 1;
        }

        .course-content {
            display: grid;
            grid-template-columns: 400px 1fr;
            gap: 40px;
            padding: 40px 30px;
        }

        .course-image-section {
            position: relative;
        }

        .course-photo {
            width: 100%;
            height: 300px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            position: relative;
            transition: transform 0.3s ease;
        }

        .course-photo:hover {
            transform: scale(1.02);
        }

        .course-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .course-photo::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(79,70,229,0.1), rgba(6,182,212,0.1));
            pointer-events: none;
        }

        .image-placeholder {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 1.2rem;
        }

        .course-details {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .description-section {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 25px;
            border-radius: 15px;
            border-left: 4px solid #4f46e5;
            position: relative;
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
        }

        .course-description {
            line-height: 1.7;
            color: #374151;
            font-size: 1.05rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .info-card::before {
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

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .info-card:hover::before {
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
            font-size: 1.1rem;
        }

        .info-value {
            color: #1e293b;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .fee-value {
            color: #059669;
            font-size: 1.4rem;
            font-weight: 700;
        }

        .students-count {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .count-badge {
            background: linear-gradient(135deg, #4f46e5, #06b6d4);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .action-section {
            background: #fefefe;
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #e2e8f0;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
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

        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
        }

        .btn-students {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-students:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.6);
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

        .admin-actions {
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            margin-top: 20px;
        }

        .admin-label {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        @media (max-width: 1024px) {
            .course-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }
            
            .course-photo {
                height: 250px;
                max-width: 400px;
                margin: 0 auto;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 15px;
            }
            
            .course-title {
                font-size: 2rem;
            }
            
            .course-content {
                padding: 30px 20px;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                justify-content: center;
            }
        }

        .loading-skeleton {
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
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <div class="course-container">
            
            <!-- Header Section -->
            <div class="course-header">
                <div class="course-title"><?= htmlspecialchars($course['course_name']) ?></div>
                <div class="course-subtitle">
                    <i class="fas fa-graduation-cap"></i>
                    Thông tin chi tiết khóa học
                </div>
            </div>

            <!-- Content Section -->
            <div class="course-content">
                
                <!-- Image Section -->
                <div class="course-image-section">
                    <div class="course-photo">
                        <?php if (!empty($course['photo'])): ?>
                            <img src="../../uploads/courses/<?= htmlspecialchars($course['photo']) ?>" 
                                 alt="<?= htmlspecialchars($course['course_name']) ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="image-placeholder">
                                <div style="text-align: center;">
                                    <i class="fas fa-image" style="font-size: 3rem; margin-bottom: 10px; opacity: 0.5;"></i>
                                    <div>Chưa có hình ảnh</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Details Section -->
                <div class="course-details">
                    
                    <!-- Description -->
                    <div class="description-section">
                        <div class="section-title">
                            <i class="fas fa-align-left"></i>
                            Mô tả khóa học
                        </div>
                        <div class="course-description">
                            <?php if (!empty($course['description'])): ?>
                                <?= nl2br(htmlspecialchars($course['description'])) ?>
                            <?php else: ?>
                                <em style="color: #64748b;">
                                    <i class="fas fa-info-circle"></i>
                                    Chưa có mô tả cho khóa học này
                                </em>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Course Information -->
                    <div class="section-title">
                        <i class="fas fa-info-circle"></i>
                        Thông tin khóa học
                    </div>
                    
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">
                                <i class="fas fa-money-bill-wave"></i>
                                Học phí
                            </div>
                            <div class="info-value fee-value">
                                <?= number_format($course['fee'], 0, ',', '.') ?> VNĐ
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-label">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Giảng viên
                            </div>
                            <div class="info-value">
                                <?= $course['teacher_name'] ? htmlspecialchars($course['teacher_name']) : 'Chưa được gán' ?>
                            </div>
                        </div>

                        <div class="info-card">
                            <div class="info-label">
                                <i class="fas fa-users"></i>
                                Học viên đã đăng ký
                            </div>
                            <div class="info-value students-count">
                                <span><?= intval($course['total_students']) ?> học viên</span>
                                <?php if($course['total_students'] > 0): ?>
                                    <span class="count-badge"><?= $course['total_students'] ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Section -->
                    <div class="action-section">
                        <div class="section-title">
                            <i class="fas fa-cogs"></i>
                            Thao tác
                        </div>
                        
                        <div class="action-buttons">
                            <a href="students_in_course.php?course_id=<?= $course['id'] ?>" class="btn btn-students">
                                <i class="fas fa-users"></i>
                                Xem danh sách học viên
                            </a>
                            
                            <a href="list_courses.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i>
                                Quay lại danh sách
                            </a>
                        </div>

                        <!-- Admin Actions -->
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <div class="admin-actions">
                                <div class="admin-label">
                                    <i class="fas fa-user-shield"></i>
                                    Quản trị viên
                                </div>
                                <div class="action-buttons">
                                    <a href="edit_courses.php?id=<?= $course['id'] ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Chỉnh sửa khóa học
                                    </a>
                                    
                                    <a href="../../handle/course_process.php?delete_id=<?= $course['id'] ?>" 
                                       class="btn btn-delete" 
                                       onclick="return confirm('Bạn có chắc chắn muốn xóa khóa học này không?\n\nLưu ý: Thao tác này không thể hoàn tác!')">
                                        <i class="fas fa-trash-alt"></i>
                                        Xóa khóa học
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Thêm hiệu ứng loading cho hình ảnh
    const img = document.querySelector('.course-photo img');
    if (img) {
        img.classList.add('loading-skeleton');
        img.addEventListener('load', function() {
            img.classList.remove('loading-skeleton');
        });
        img.addEventListener('error', function() {
            img.classList.remove('loading-skeleton');
            // Có thể thêm fallback image ở đây
        });
    }

    // Smooth scroll cho các liên kết nội bộ
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Confirmation dialog với style đẹp hơn
    const deleteBtn = document.querySelector('.btn-delete');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const courseName = "<?= htmlspecialchars($course['course_name']) ?>";
            if (confirm(`Bạn có chắc chắn muốn xóa khóa học "${courseName}" không?\n\n⚠️ Cảnh báo: Thao tác này sẽ xóa vĩnh viễn khóa học và không thể khôi phục!`)) {
                window.location.href = this.href;
            }
        });
    }
});
</script>
</body>
</html>