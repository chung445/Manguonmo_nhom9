<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách khóa học để chọn
$courses = $conn->query("SELECT id, course_name FROM courses");

// Xử lý submit form
$errors = [];
if(isset($_POST['submit'])) {
    $course_id     = $_POST['course_id'];
    $schedule_date = $_POST['schedule_date'];
    $start_time    = $_POST['start_time'];
    $end_time      = $_POST['end_time'];
    $location      = trim($_POST['location']);

    if(empty($course_id)) $errors[] = "Vui lòng chọn khóa học";
    if(empty($schedule_date)) $errors[] = "Ngày học không được để trống";
    if(empty($start_time)) $errors[] = "Giờ bắt đầu không được để trống";
    if(empty($end_time)) $errors[] = "Giờ kết thúc không được để trống";
    if(empty($location)) $errors[] = "Địa điểm không được để trống";

    if(empty($errors)){
        $stmt = $conn->prepare("INSERT INTO schedules (course_id, schedule_date, start_time, end_time, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $course_id, $schedule_date, $start_time, $end_time, $location);
        if($stmt->execute()){
            header("Location: list_schedules.php");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm lịch học";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm lịch học - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-container {
            width: 95%;
            max-width: 600px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display:block;
            margin-bottom:5px;
            font-weight: 600;
        }

        input[type="text"], input[type="date"], input[type="time"], select {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:1rem;
        }

        .btn {
            padding:6px 12px;
            text-decoration:none;
            border-radius:6px;
            color:white;
            font-size:0.9rem;
            display:inline-block;
            margin-right:5px;
            cursor:pointer;
            transition: all 0.25s ease;
        }

        .btn-submit { background-color:#10b981; }
        .btn-submit:hover { background-color:#059669; transform: scale(1.05); }

        .error {
            color:red;
            margin-bottom:10px;
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
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="form-container">
            <h2>Thêm lịch học</h2>
            <?php if(!empty($errors)): ?>
                <div class="error">
                    <?= implode('<br>', $errors); ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>Khóa học:</label>
                    <select name="course_id" required>
                        <option value="">-- Chọn khóa học --</option>
                        <?php
                        // Reset lại con trỏ nếu đã fetch trước đó
                        if ($courses && $courses->num_rows > 0) {
                            $courses->data_seek(0);
                            while($row = $courses->fetch_assoc()) {
                        ?>
                            <option value="<?= $row['id'] ?>"><?= $row['course_name'] ?></option>
                        <?php }} ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Ngày học:</label>
                    <input type="date" name="schedule_date" required>
                </div>
                <div class="form-group">
                    <label>Giờ bắt đầu:</label>
                    <input type="time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label>Giờ kết thúc:</label>
                    <input type="time" name="end_time" required>
                </div>
                <div class="form-group">
                    <label>Địa điểm:</label>
                    <input type="text" name="location" required>
                </div>
                <button type="submit" name="submit" class="btn btn-submit">Thêm lịch học</button>
            </form>
            <a href="list_schedules.php" class="back-link">← Quay lại danh sách lịch học</a>
        </div>
    </div>
</div>
</body>
</html>