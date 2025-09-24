<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// --- Phân trang ---
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// --- Tìm kiếm ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_param = '';

if (!empty($search)) {
    $where_clause = "WHERE s.full_name LIKE ? OR c.course_name LIKE ? OR t.full_name LIKE ?";
    $search_param = "%$search%";
}

// --- Đếm tổng số bản ghi ---
$count_sql = "SELECT COUNT(*) AS total 
              FROM grades g
              JOIN students s ON g.student_id = s.id
              JOIN courses c ON g.course_id = c.id
              LEFT JOIN teachers t ON g.teacher_id = t.id
              $where_clause";

if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// --- Lấy dữ liệu ---
$sql = "SELECT g.id, s.full_name AS student_name, c.course_name, 
               t.full_name AS teacher_name, g.score, g.grade_letter, g.graded_at, g.notes
        FROM grades g
        JOIN students s ON g.student_id = s.id
        JOIN courses c ON g.course_id = c.id
        LEFT JOIN teachers t ON g.teacher_id = t.id
        $where_clause
        ORDER BY g.graded_at DESC
        LIMIT ? OFFSET ?";

if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// --- Phân quyền ---
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$can_manage = $is_admin; // chỉ admin được thêm/sửa/xóa
$can_view_details = $is_admin || $is_teacher;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách điểm - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <div class="header-section">
            <div class="header-row">
                <h2>
                    <i class="fas fa-star"></i>
                    Quản lý điểm
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_grades.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm điểm
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item"><i class="fas fa-users"></i> Tổng số: <?= $total_records ?> bản ghi</div>
                <div class="stat-item"><i class="fas fa-file-alt"></i> Trang <?= $page ?>/<?= $total_pages ?></div>
                <?php if (!empty($search)): ?>
                    <div class="stat-item"><i class="fas fa-search"></i> Kết quả cho: "<?= htmlspecialchars($search) ?>"</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-section">
            <form method="GET" style="display:flex;gap:12px;align-items:center;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm theo tên SV, khóa học, GV..." class="search-box">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                <?php if (!empty($search)): ?>
                    <a href="?" class="btn" style="background:#6b7280;"><i class="fas fa-times"></i> Xóa lọc</a>
                <?php endif; ?>
                <input type="hidden" name="page" value="1">
            </form>
            <div style="color:#6b7280;font-size:0.9rem;">
                <i class="fas fa-info-circle"></i>
                Hiển thị <?= min($offset+1,$total_records) ?>-<?= min($offset+$records_per_page,$total_records) ?> / <?= $total_records ?>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th>STT</th>
                    <th>Sinh viên</th>
                    <th>Khóa học</th>
                    <th>Giảng viên</th>
                    <th>Điểm</th>
                    <th>Grade</th>
                    <th>Ngày chấm</th>
                    <th>Ghi chú</th>
                    <?php if ($can_manage): ?><th>Hành động</th><?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $stt = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>".$stt."</strong></td>";
                        echo "<td>".htmlspecialchars($row['student_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['course_name'])."</td>";
                        echo "<td>".(!empty($row['teacher_name']) ? htmlspecialchars($row['teacher_name']) : "<span style='color:#9ca3af'>N/A</span>")."</td>";
                        echo "<td>".htmlspecialchars($row['score'])."</td>";
                        echo "<td>".htmlspecialchars($row['grade_letter'])."</td>";
                        echo "<td>".date('d/m/Y', strtotime($row['graded_at']))."</td>";
                        echo "<td>".htmlspecialchars($row['notes'])."</td>";

                        if ($can_manage) {
                            echo "<td>
                                    <a href='edit_grades.php?id=".$row['id']."' class='btn btn-edit'><i class='fas fa-edit'></i></a>
                                    <a href='../../handle/grade_process.php?delete=".$row['id']."' class='btn btn-delete' onclick='return confirm(\"Xóa bản ghi này?\\nHành động không thể hoàn tác!\")'><i class='fas fa-trash'></i></a>
                                  </td>";
                        }
                        echo "</tr>";
                        $stt++;
                    }
                } else {
                    $colspan = $can_manage ? 9 : 8;
                    echo "<tr><td colspan='$colspan' class='no-data'>
                            <i class='fas fa-ban'></i><br>".
                            (empty($search) ? "Chưa có dữ liệu điểm" : "Không tìm thấy kết quả phù hợp").
                          "</td></tr>";
                }
                $stmt->close();
                ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <div class="pagination-info">
                <i class="fas fa-info-circle"></i> Trang <?= $page ?>/<?= $total_pages ?>
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search)?'&search='.urlencode($search):'' ?>"><i class="fas fa-angle-double-left"></i></a>
                    <a href="?page=<?= $page-1 ?><?= !empty($search)?'&search='.urlencode($search):'' ?>"><i class="fas fa-angle-left"></i></a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page-2);
                $end_page = min($total_pages, $page+2);
                if ($start_page > 1) echo "<span>...</span>";
                for ($i=$start_page;$i<=$end_page;$i++) {
                    if ($i==$page) echo "<span class='current'>$i</span>";
                    else echo "<a href='?page=$i".(!empty($search)?'&search='.urlencode($search):'')."'>$i</a>";
                }
                if ($end_page < $total_pages) echo "<span>...</span>";
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= !empty($search)?'&search='.urlencode($search):'' ?>"><i class="fas fa-angle-right"></i></a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search)?'&search='.urlencode($search):'' ?>"><i class="fas fa-angle-double-right"></i></a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-right"></i></span>
                    <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
