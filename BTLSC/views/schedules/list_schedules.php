<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách lịch học, join với tên khóa học
$sql = "SELECT s.id, c.course_name, s.schedule_date, s.start_time, s.end_time, s.location
        FROM schedules s
        LEFT JOIN courses c ON s.course_id = c.id
        ORDER BY s.schedule_date ASC, s.start_time ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách lịch học - English Center</title>
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
        .header-row {
            width: 95%;
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .table-container {
            width: 95%;
            max-width: 1400px;
            margin: 0 auto;
        }
        .table-container table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        thead tr {
            background-color:#2563eb;
            color:white;
        }
        th, td {
            padding:12px 15px;
            text-align:left;
        }
        tbody tr {
            background-color:#fff;
            box-shadow:0 2px 6px rgba(0,0,0,0.1);
            margin-bottom:10px;
            border-radius:8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        tbody tr:hover {
            transform: translateY(-3px);
            box-shadow:0 4px 12px rgba(0,0,0,0.15);
        }
        td {
            border:none;
        }
        .btn {
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 6px;
            color: white;
            font-size: 0.9rem;
            display: inline-block;
            margin-right: 5px;
            transition: all 0.25s ease;
        }
        .btn-add { background-color: #10b981; }
        .btn-add:hover { background-color: #059669; transform: scale(1.05); }
        .btn-edit { background-color: #f59e0b; }
        .btn-edit:hover { background-color: #d97706; transform: scale(1.05); }
        .btn-delete { background-color: #ef4444; }
        .btn-delete:hover { background-color: #dc2626; transform: scale(1.05); }
        @media (max-width:1200px){
            .header-row h2 { font-size:24px; }
        }
        .main-content {
            flex-grow: 1;
            padding: 30px 30px 30px 50px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <?php include __DIR__ . '/../menu.php'; ?>

        <!-- Main content -->
        <div class="main-content">
            <div class="header-row">
                <h2>Danh sách lịch học</h2>
                <div class="btn-add-wrapper">
                    <a href="add_schedules.php" class="btn btn-add">+ Thêm lịch học</a>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Khóa học</th>
                            <th>Ngày</th>
                            <th>Thời gian bắt đầu</th>
                            <th>Thời gian kết thúc</th>
                            <th>Địa điểm</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                        <td>".$row['id']."</td>
                                        <td>".$row['course_name']."</td>
                                        <td>".$row['schedule_date']."</td>
                                        <td>".$row['start_time']."</td>
                                        <td>".$row['end_time']."</td>
                                        <td>".$row['location']."</td>
                                        <td>
                                            <a href='edit_schedules.php?id=".$row['id']."' class='btn btn-edit'>Sửa</a>
                                            <a href='../../handle/schedule_process.php?delete_id=".$row['id']."' class='btn btn-delete' onclick='return confirm(\"Xóa lịch học này?\")'>Xóa</a>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align:center; padding:15px; font-style:italic; color:#555;'>Chưa có lịch học nào</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
