<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

$id = $_GET['id'] ?? null;
if(!$id){
    header("Location: list_schedules.php");
    exit();
}

// Lấy dữ liệu lịch học
$sql = "SELECT * FROM schedules WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$schedule = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Lấy danh sách khóa học
$courses = $conn->query("SELECT id, course_name FROM courses");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa lịch học - English Center</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display:block;
            margin-bottom:5px;
            font-weight:600;
        }
        .form-group input,
        .form-group select {
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
            margin-top:15px;
            display:inline-block;
            text-decoration:none;
            color:#2563eb;
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
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Sửa lịch học</h2>
            <form action="../../handle/schedule_process.php" method="POST">
                <input type="hidden" name="update_schedule" value="1">
                <input type="hidden" name="id" value="<?= $schedule['id'] ?>">
                <div class="form-group">
                    <label>Khóa học:</label>
                    <select name="course_id" required>
                        <?php while($row = $courses->fetch_assoc()) { ?>
                            <option value="<?= $row['id'] ?>" <?= $row['id']==$schedule['course_id']?'selected':'' ?>>
                                <?= $row['course_name'] ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày học:</label>
                    <input type="date" name="schedule_date" value="<?= $schedule['schedule_date'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Giờ bắt đầu:</label>
                    <input type="time" name="start_time" value="<?= $schedule['start_time'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Giờ kết thúc:</label>
                    <input type="time" name="end_time" value="<?= $schedule['end_time'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Địa điểm:</label>
                    <input type="text" name="location" value="<?= htmlspecialchars($schedule['location']) ?>" required>
                </div>
                <button type="submit" class="btn-submit">Cập nhật lịch học</button>
            </form>
            <a href="list_schedules.php" class="back-link">← Quay lại danh sách lịch học</a>
        </div>
    </div>
</div>
</body>
</html>