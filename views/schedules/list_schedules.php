<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Cấu hình phân trang
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_param = '';

if (!empty($search)) {
    $where_clause = "WHERE c.course_name LIKE ? OR s.location LIKE ?";
    $search_param = "%$search%";
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total 
              FROM schedules s 
              LEFT JOIN courses c ON s.course_id = c.id 
              $where_clause";
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("ss", $search_param, $search_param);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// Lấy danh sách lịch học với phân trang
$sql = "SELECT s.id, c.course_name, s.schedule_date, s.start_time, s.end_time, s.location,
               COUNT(e.student_id) AS total_students
        FROM schedules s
        LEFT JOIN courses c ON s.course_id = c.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        $where_clause
        GROUP BY s.id, c.course_name, s.schedule_date, s.start_time, s.end_time, s.location
        ORDER BY s.schedule_date ASC, s.start_time ASC
        LIMIT ? OFFSET ?";
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Phân quyền
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$can_manage = $is_admin; // chỉ admin mới thêm/sửa/xóa
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách lịch học - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-row">
                <h2>
                    <i class="fas fa-calendar-alt"></i>
                    Quản lý lịch học
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_schedules.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm lịch học
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-calendar"></i>
                    <span>Tổng số: <?= $total_records ?> lịch học</span>
                </div>
                <div class="stat-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Trang <?= $page ?>/<?= $total_pages ?></span>
                </div>
                <?php if (!empty($search)): ?>
                <div class="stat-item">
                    <i class="fas fa-search"></i>
                    <span>Kết quả cho: "<?= htmlspecialchars($search) ?>"</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
            <div class="search-section">
                <form method="GET" action="" style="display:flex;gap:12px;align-items:center;">
                    <input type="text" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Tìm theo tên khóa học hoặc địa điểm..." 
                           class="search-box">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="?" class="btn" style="background:#6b7280;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </a>
                    <?php endif; ?>
                    <input type="hidden" name="page" value="1">
                </form>
            </div>
            
            <div style="color:#6b7280;font-size:0.9rem;">
                <i class="fas fa-info-circle"></i>
                Hiển thị <?= min($offset+1,$total_records) ?>-<?= min($offset+$records_per_page,$total_records) ?> 
                trong tổng số <?= $total_records ?>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Khóa học</th>
                        <th>Ngày</th>
                        <th>Bắt đầu</th>
                        <th>Kết thúc</th>
                        <th>Địa điểm</th>
                        <th>Số SV</th>
                        <?php if ($can_manage): ?>
                            <th>Hành động</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $stt = $offset+1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>$stt</strong></td>";
                        echo "<td>".htmlspecialchars($row['course_name'])."</td>";
                        echo "<td>".date('d/m/Y', strtotime($row['schedule_date']))."</td>";
                        echo "<td>".$row['start_time']."</td>";
                        echo "<td>".$row['end_time']."</td>";
                        echo "<td>".htmlspecialchars($row['location'])."</td>";
                        echo "<td>
                                <a href='../courses/students_in_course.php?type=schedule&id=".$row['id']."' 
                                   style='color:#2563eb;font-weight:500;text-decoration:none;'>
                                   ".$row['total_students']."
                                </a>
                              </td>";
                        if ($can_manage) {
                            echo "<td>
                                    <a href='edit_schedules.php?id=".$row['id']."' class='btn btn-edit'>
                                        <i class='fas fa-edit'></i>
                                    </a>
                                    <a href='../../handle/schedule_process.php?delete_id=".$row['id']."' 
                                       class='btn btn-delete' 
                                       onclick='return confirm(\"Xóa lịch học này?\")'>
                                        <i class='fas fa-trash'></i>
                                    </a>
                                  </td>";
                        }
                        echo "</tr>";
                        $stt++;
                    }
                } else {
                    $colspan = $can_manage ? 8 : 7;
                    echo "<tr><td colspan='$colspan' class='no-data'>
                            <i class='fas fa-calendar-times'></i><br>
                            ".(empty($search) ? "Chưa có lịch học nào" : "Không tìm thấy lịch học phù hợp")."
                          </td></tr>";
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
                <i class="fas fa-info-circle"></i>
                Trang <?= $page ?> trong tổng số <?= $total_pages ?> trang
            </div>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search)?'&search='.urlencode($search):'' ?>" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page-1 ?><?= !empty($search)?'&search='.urlencode($search):'' ?>" title="Trang trước">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page-2);
                $end_page = min($total_pages, $page+2);
                if ($start_page > 1) echo "<span>...</span>";
                for ($i=$start_page; $i<=$end_page; $i++):
                    if ($i==$page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= !empty($search)?'&search='.urlencode($search):'' ?>"><?= $i ?></a>
                    <?php endif;
                endfor;
                if ($end_page<$total_pages) echo "<span>...</span>";
                ?>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= !empty($search)?'&search='.urlencode($search):'' ?>" title="Trang sau">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search)?'&search='.urlencode($search):'' ?>" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
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
