<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Lấy danh sách khóa học kèm tên giáo viên
$sql = "SELECT c.id, c.course_name, c.description, c.fee, t.full_name AS teacher_name
        FROM courses c
        LEFT JOIN teachers t ON c.teacher_id = t.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách khóa học - English Center</title>
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
            <h2>Danh sách khóa học</h2>
            <div style="display:flex;gap:16px;align-items:center;">
                <input type="text" id="courseSearch" placeholder="Tìm khóa học..." style="padding:7px 12px;border-radius:6px;border:1px solid #ccc;font-size:15px;outline:none;background:#f9fafb;" onkeyup="filterTable('courseSearch','courseTable')">
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="add_courses.php" class="btn btn-add">+ Thêm khóa học</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên khóa học</th>
                        <th>Mô tả</th>
                        <th>Học phí</th>
                        <th>Giảng viên</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="courseTable">
                    <?php
                    if ($result && $result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>".$row['id']."</td>
                    <td>".$row['course_name']."</td>
                    <td>".$row['description']."</td>
                    <td>".$row['fee']."</td>
                    <td>".($row['teacher_name'] ?? '<span style=\'color:#888\'>Chưa có</span>')."</td>
                    <td>";
                            if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                                echo "<a href='edit_courses.php?id=".$row['id']."' class='btn btn-edit'>Sửa</a>
                                      <a href='../../handle/course_process.php?delete_id=".$row['id']."' class='btn btn-delete' onclick='return confirm(\"Xóa khóa học này?\")'>Xóa</a>";
                            }
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding:15px; font-style:italic; color:#555;'>Chưa có khóa học nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            <script>
            function filterTable(inputId, tableId) {
                var input = document.getElementById(inputId);
                var filter = input.value.toLowerCase();
                var tbody = document.getElementById(tableId);
                var tr = tbody.getElementsByTagName('tr');
                for (var i = 0; i < tr.length; i++) {
                    var txt = tr[i].textContent || tr[i].innerText;
                    tr[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
                }
            }
            </script>
        </div>
    </div>
</div>
</body>
</html>