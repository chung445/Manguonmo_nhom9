<?php
session_start();
include 'functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$conn = getDbConnection();

// Thống kê cơ bản
$student_count = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$teacher_count = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc()['total'];
$course_count  = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];
$schedule_count = $conn->query("SELECT COUNT(*) as total FROM schedules")->fetch_assoc()['total'];
$enrollment_count = $conn->query("SELECT COUNT(*) as total FROM enrollments")->fetch_assoc()['total'];

// Thống kê nâng cao
$active_courses = $conn->query("SELECT COUNT(*) as total FROM courses WHERE id IN (SELECT DISTINCT course_id FROM enrollments)")->fetch_assoc()['total'];
$revenue_month = $conn->query("SELECT COALESCE(SUM(c.fee), 0) as total FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE MONTH(e.enrollment_date) = MONTH(NOW()) AND YEAR(e.enrollment_date) = YEAR(NOW())")->fetch_assoc()['total'];

// Top khóa học phổ biến
$popular_courses = $conn->query("SELECT c.course_name, COUNT(e.id) as enrollments FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id GROUP BY c.id ORDER BY enrollments DESC LIMIT 5");

// Hoạt động gần đây
$recent_enrollments = $conn->query("SELECT s.full_name, c.course_name, e.enrollment_date FROM enrollments e JOIN students s ON e.student_id = s.id JOIN courses c ON e.course_id = c.id ORDER BY e.enrollment_date DESC LIMIT 5");

// Lịch học hôm nay
$today_schedules = $conn->query("SELECT s.start_time, s.end_time, c.course_name, t.full_name as teacher_name 
    FROM schedules s 
    JOIN courses c ON s.course_id = c.id 
    JOIN teachers t ON c.teacher_id = t.id 
    WHERE s.schedule_date = CURDATE() 
    ORDER BY s.start_time");

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - English Center</title>   
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/views/menu.php'; ?>
        <div class="main-content">
            <div class="content-container">
            
            <!-- Header Section -->
            <div class="dashboard-header">
                <div class="admin-info">
                    <i class="fas fa-user-shield"></i>
                    <?= $_SESSION['username'] ?> (<?= $_SESSION['role'] ?>)
                </div>
                <div class="header-content">
                    <div class="welcome-title">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </div>
                    <div class="welcome-subtitle">
                        <i class="fas fa-clock"></i>
                        <span>Xin chào, chào mừng đến với Carrington English Center</span>
                    </div>
                </div>

                <!-- Quick Actions -->
                <?php if ($_SESSION['role'] != 'student'): ?>
                    <div class="quick-actions">
                        <a href="views/students/add_students.php" class="action-btn action-add">
                            <i class="fas fa-plus"></i>
                            Thêm học viên mới
                        </a>
                        <a href="views/courses/add_courses.php" class="action-btn action-manage">
                            <i class="fas fa-plus"></i>
                            Tạo khóa học mới
                        </a>
                        <a href="views/schedules/add_schedules.php" class="action-btn action-schedule">
                            <i class="fas fa-calendar-plus"></i>
                            Lập lịch học
                        </a>
                        <a href="views/grades/list_grades.php" class="action-btn action-report">
                            <i class="fas fa-chart-bar"></i>
                            Xem báo cáo điểm số
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card students" onclick="window.location.href='views/students/list_students.php'">
                    <div class="stat-trend">+12%</div>
                    <div class="stat-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-number"><?= number_format($student_count) ?></div>
                    <div class="stat-label">Học viên</div>
                </div>

                <div class="stat-card teachers" onclick="window.location.href='views/teachers/list_teachers.php'">
                    <div class="stat-trend">+5%</div>
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-number"><?= number_format($teacher_count) ?></div>
                    <div class="stat-label">Giảng viên</div>
                </div>

                <div class="stat-card courses" onclick="window.location.href='views/courses/list_courses.php'">
                    <div class="stat-trend">+8%</div>
                    <div class="stat-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-number"><?= number_format($course_count) ?></div>
                    <div class="stat-label">Khóa học</div>
                </div>

                <div class="stat-card schedules" onclick="window.location.href='views/schedules/list_schedules.php'">
                    <div class="stat-trend">+15%</div>
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-number"><?= number_format($schedule_count) ?></div>
                    <div class="stat-label">Lịch học</div>
                </div>

                <div class="stat-card enrollments" onclick="window.location.href='views/enrollments/list_enrollments.php'">
                    <div class="stat-trend">+22%</div>
                    <div class="stat-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="stat-number"><?= number_format($enrollment_count) ?></div>
                    <div class="stat-label">Đăng ký khóa học</div>
                </div>

                <div class="stat-card revenue">
                    <div class="stat-trend">+18%</div>
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-number"><?= number_format($revenue_month/1000000, 1) ?>M</div>
                    <div class="stat-label">Doanh thu tháng (VNĐ)</div>
                </div>
            </div>

            <!-- Content Sections -->
            <div class="content-sections">
                
                <!-- Left Column: Recent Activities & Popular Courses -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-history"></i>
                        Hoạt động gần đây
                    </div>
                    
                    <?php if($recent_enrollments->num_rows > 0): ?>
                        <?php while($enrollment = $recent_enrollments->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="activity-avatar">
                                <?= strtoupper(substr($enrollment['full_name'], 0, 1)) ?>
                            </div>
                            <div class="activity-info">
                                <div class="activity-name"><?= htmlspecialchars($enrollment['full_name']) ?></div>
                                <div class="activity-detail">đã đăng ký khóa <?= htmlspecialchars($enrollment['course_name']) ?></div>
                            </div>
                            <div class="activity-time">
                                <?= date('d/m H:i', strtotime($enrollment['enrollment_date'])) ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>Chưa có hoạt động gần đây</p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="section-title" style="margin-top: 30px;">
                        <i class="fas fa-star"></i>
                        Khóa học phổ biến
                    </div>
                    
                    <?php if($popular_courses->num_rows > 0): ?>
                        <?php while($course = $popular_courses->fetch_assoc()): ?>
                        <div class="course-item">
                            <div class="course-avatar">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="course-info">
                                <div class="course-name"><?= htmlspecialchars($course['course_name']) ?></div>
                                <div class="course-detail"><?= $course['enrollments'] ?> học viên đăng ký</div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open"></i>
                            <p>Chưa có dữ liệu khóa học</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Column: Today's Schedule -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-calendar-day"></i>
                        Lịch học hôm nay
                    </div>
                    
                    <?php if($today_schedules->num_rows > 0): ?>
                        <?php while($schedule = $today_schedules->fetch_assoc()): ?>
                        <div class="schedule-item">
                            <div class="schedule-time">
                                <?= date('H:i', strtotime($schedule['start_time'])) ?> - <?= date('H:i', strtotime($schedule['end_time'])) ?>
                            </div>
                            <div class="course-info">
                                <div class="course-name"><?= htmlspecialchars($schedule['course_name']) ?></div>
                                <div class="course-detail">GV: <?= htmlspecialchars($schedule['teacher_name']) ?></div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <p>Không có lịch học hôm nay</p>
                        </div>
                    <?php endif; ?>

                    <!-- System Status -->
                    <div class="section-title" style="margin-top: 30px;">
                        <i class="fas fa-server"></i>
                        Trạng thái hệ thống
                    </div>
                    
                    <div style="display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;">
                            <span>Cơ sở dữ liệu</span>
                            <span style="color: #10b981; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Hoạt động tốt
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #f0fdf4; border-radius: 8px; border-left: 4px solid #10b981;">
                            <span>Server</span>
                            <span style="color: #10b981; font-weight: 600;">
                                <i class="fas fa-check-circle"></i> Online
                            </span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: #fffbeb; border-radius: 8px; border-left: 4px solid #f59e0b;">
                            <span>Backup</span>
                            <span style="color: #f59e0b; font-weight: 600;">
                                <i class="fas fa-clock"></i> 2 giờ trước
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>