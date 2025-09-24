<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

$course_id = null;
$course_name = "";
$schedule_id = null;

// Trường hợp gọi từ khóa học
if (isset($_GET['course_id'])) {
    $course_id = intval($_GET['course_id']);
}
// Trường hợp gọi từ lịch học
elseif (isset($_GET['type']) && $_GET['type'] === 'schedule' && isset($_GET['id'])) {
    $schedule_id = intval($_GET['id']);
    // Lấy course_id từ schedule
    $stmt = $conn->prepare("SELECT course_id FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $stmt->bind_result($course_id);
    if (!$stmt->fetch()) {
        die("Không tìm thấy lịch học");
    }
    $stmt->close();
}
else {
    die("Thiếu tham số");
}

// Lấy tên khóa học
$sql_course = "SELECT course_name FROM courses WHERE id = ?";
$stmt = $conn->prepare($sql_course);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$stmt->bind_result($course_name);
$stmt->fetch();
$stmt->close();

// Lấy danh sách sinh viên
$sql_students = "SELECT s.id, s.full_name, s.email, s.phone
                 FROM enrollments e
                 JOIN students s ON e.student_id = s.id
                 WHERE e.course_id = ?";
$stmt2 = $conn->prepare($sql_students);
$stmt2->bind_param("i", $course_id);
$stmt2->execute();
$result_students = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sinh viên trong khóa học</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 30px 30px 50px;
            overflow-x: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .table-container {
            width: 95%;
            max-width: 1000px;
            margin: 20px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        thead tr {
            background-color: #2563eb;
            color: white;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
        }
        tbody tr {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
        }
        tbody tr:hover {
            background: #f9fafb;
        }
        td {
            border: none;
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <h2>Danh sách sinh viên trong khóa học: 
            <span style="color:#2563eb;"><?php echo htmlspecialchars($course_name); ?></span>
        </h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_students && $result_students->num_rows > 0) {
                        while ($row = $result_students->fetch_assoc()) {
                            echo "<tr>
                                    <td>".$row['id']."</td>
                                    <td>".$row['full_name']."</td>
                                    <td>".$row['email']."</td>
                                    <td>".$row['phone']."</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr>
                                <td colspan='4' style='text-align:center;color:#555;'>
                                    Chưa có sinh viên nào đăng ký
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php if ($schedule_id): ?>
            <p><a href="../schedules/list_schedules.php" style="color:#2563eb;">← Quay lại danh sách lịch học</a></p>
        <?php else: ?>
            <p><a href="list_courses.php" style="color:#2563eb;">← Quay lại danh sách khóa học</a></p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
