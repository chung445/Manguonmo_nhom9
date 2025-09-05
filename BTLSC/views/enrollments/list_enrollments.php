<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách ghi danh
$sql = "SELECT e.id, s.full_name as student_name, c.course_name, e.enrollment_date, e.status
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        ORDER BY e.enrollment_date DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách ghi danh - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 30px 30px 50px;
            overflow-x: auto;
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
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>
    <div class="main-content">
        <div class="header-row">
            <h2>Danh sách ghi danh</h2>
            <a href="add_enrollments.php" class="btn btn-add"><i class="fas fa-plus"></i> Thêm ghi danh</a>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Ngày ghi danh</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && $result->num_rows > 0){
                        while($row = $result->fetch_assoc()){ ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= $row['student_name'] ?></td>
                                <td><?= $row['course_name'] ?></td>
                                <td><?= $row['enrollment_date'] ?></td>
                                <td><?= ucfirst($row['status']) ?></td>
                                <td>
                                    <a href="edit_enrollments.php?id=<?= $row['id'] ?>" class="btn btn-edit">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <a href="../../handle/enrollment_process.php?delete_id=<?= $row['id'] ?>" class="btn btn-delete" onclick="return confirm('Xóa ghi danh này?')">
                                        <i class="fas fa-trash-alt"></i> Xóa
                                    </a>
                                </td>
                            </tr>
                    <?php } } else { ?>
                        <tr><td colspan="6" style="text-align:center; padding:15px; font-style:italic; color:#555;">Chưa có ghi danh nào</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>