<?php
session_start();
include 'functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$conn = getDbConnection();

$student_count = $conn->query("SELECT COUNT(*) as total FROM students")->fetch_assoc()['total'];
$teacher_count = $conn->query("SELECT COUNT(*) as total FROM teachers")->fetch_assoc()['total'];
$course_count  = $conn->query("SELECT COUNT(*) as total FROM courses")->fetch_assoc()['total'];
$schedule_count = $conn->query("SELECT COUNT(*) as total FROM schedules")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - English Center</title>   
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</head>
<body>
    <div class="dashboard-bg-decor">
        <img src="images/anh1.jpg" class="bg-decor-img bg-decor-top-left" alt="Decor">
    </div>
    <div class="dashboard-admin-info" style="position:fixed;top:8px;right:40px;z-index:9999;font-size:1rem;color:#fff;background:#2563eb;padding:12px 24px;border-radius:16px;box-shadow:0 4px 16px rgba(37,99,235,0.18);">
        <i class="fas fa-user-shield"></i> <?= $_SESSION['username'] ?> (<?= $_SESSION['role'] ?>)
    </div>
    <div class="dashboard-wrapper">
        <?php include __DIR__ . '/views/menu.php'; ?>
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <p>Xin chào, đã đến English Center</p>
            </div>
            <div class="cards">
                <div class="card" onclick="window.location.href='views/students/list_students.php'">
                    <div class="card-icon"><i class="fas fa-user-graduate"></i></div>
                    <h3>Học viên</h3>
                    <p><?= $student_count ?> học viên</p>
                </div>
                <div class="card" onclick="window.location.href='views/teachers/list_teachers.php'">
                    <div class="card-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h3>Giảng viên</h3>
                    <p><?= $teacher_count ?> giảng viên</p>
                </div>
                <div class="card" onclick="window.location.href='views/courses/list_courses.php'">
                    <div class="card-icon"><i class="fas fa-book"></i></div>
                    <h3>Khóa học</h3>
                    <p><?= $course_count ?> khóa học</p>
                </div>
                <div class="card" onclick="window.location.href='views/schedules/list_schedules.php'">
                    <div class="card-icon"><i class="fas fa-calendar-alt"></i></div>
                    <h3>Lịch học</h3>
                    <p><?= $schedule_count ?> lịch học</p>
                </div>
            </div>
            <div class="charts-row">
                <div class="chart-box">
                    <div class="chart-title">Tỉ lệ thành phần hệ thống</div>
                    <canvas id="pieChart"></canvas>
                </div>
                <div class="chart-box">
                    <div class="chart-title">Thống kê số lượng</div>
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Pie Chart
        const pieCtx = document.getElementById('pieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Học viên', 'Giảng viên', 'Khóa học', 'Lịch học'],
                datasets: [{
                    data: [<?= $student_count ?>, <?= $teacher_count ?>, <?= $course_count ?>, <?= $schedule_count ?>],
                    backgroundColor: [
                        '#2563eb', '#10b981', '#f59e0b', '#dc2626'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Bar Chart
        const barCtx = document.getElementById('barChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: ['Học viên', 'Giảng viên', 'Khóa học', 'Lịch học'],
                datasets: [{
                    label: 'Số lượng',
                    data: [<?= $student_count ?>, <?= $teacher_count ?>, <?= $course_count ?>, <?= $schedule_count ?>],
                    backgroundColor: [
                        '#2563eb', '#10b981', '#f59e0b', '#dc2626'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>