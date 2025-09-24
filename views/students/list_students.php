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
    $where_clause = "WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ?";
    $search_param = "%$search%";
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM students $where_clause";
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

// Lấy danh sách học viên với phân trang
$sql = "SELECT id, full_name, dob, email, phone FROM students $where_clause ORDER BY id DESC LIMIT ? OFFSET ?";
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Phân quyền
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$can_manage = $is_admin; // Chỉ admin mới có thể thêm/sửa/xóa
$can_view_details = $is_admin || $is_teacher; // Admin và teacher có thể xem chi tiết
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách học viên - English Center</title>
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
                    <i class="fas fa-user-graduate"></i>
                    Quản lý học viên
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_students.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm học viên
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-users"></i>
                    <span>Tổng số: <?= $total_records ?> học viên</span>
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
                <form method="GET" action="" style="display: flex; gap: 12px; align-items: center;">
                    <input type="text" 
                           name="search" 
                           value="<?= htmlspecialchars($search) ?>"
                           placeholder="Tìm theo tên, email hoặc số điện thoại..." 
                           class="search-box">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="?" class="btn" style="background: #6b7280;">
                            <i class="fas fa-times"></i> Xóa lọc
                        </a>
                    <?php endif; ?>
                    <input type="hidden" name="page" value="1">
                </form>
            </div>
            
            <div style="color: #6b7280; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i>
                Hiển thị <?= min($offset + 1, $total_records) ?>-<?= min($offset + $records_per_page, $total_records) ?> trong tổng số <?= $total_records ?>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <table>
                <thead>
                <tr>
                    <th style="width: 60px;">STT</th>
                    <th>Họ tên</th>
                    <th style="width: 120px;">Ngày sinh</th>
                    <th>Email</th>
                    <th style="width: 120px;">SĐT</th>
                    <?php if ($can_view_details): ?>
                        <th style="width: 150px;">Khóa học</th>
                    <?php endif; ?>
                    <?php if ($can_manage): ?>
                        <th style="width: 160px;">Hành động</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    $stt = $offset + 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . $stt . "</strong></td>";
                        echo "<td><strong>" . htmlspecialchars($row['full_name']) . "</strong></td>";
                        echo "<td>" . date('d/m/Y', strtotime($row['dob'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                        
                        // Hiển thị khóa học (chỉ admin và teacher)
                        if ($can_view_details) {
                            echo "<td>";
                            // Lấy danh sách khóa học cho từng học viên
                            $sql_courses = "SELECT c.course_name 
                                            FROM enrollments e 
                                            JOIN courses c ON e.course_id = c.id 
                                            WHERE e.student_id = ?";
                            $stmt_courses = $conn->prepare($sql_courses);
                            $stmt_courses->bind_param("i", $row['id']);
                            $stmt_courses->execute();
                            $res_courses = $stmt_courses->get_result();
                            $courses = [];
                            while ($c = $res_courses->fetch_assoc()) { 
                                $courses[] = $c['course_name']; 
                            }
                            $stmt_courses->close();

                            if (!empty($courses)) {
                                echo "<button class='toggle-btn' onclick='toggleCourses(".$row['id'].")'>
                                        <i class='fas fa-eye'></i> Xem (" . count($courses) . ")
                                      </button>
                                      <div id='courses-".$row['id']."' class='course-list'>";
                                foreach ($courses as $course) {
                                    echo "<div class='course-item'><i class='fas fa-book' style='color: #2563eb;'></i>" . htmlspecialchars($course) . "</div>";
                                }
                                echo "</div>";
                            } else {
                                echo "<span style='color:#9ca3af; font-style: italic;'>
                                        <i class='fas fa-minus-circle'></i> Chưa đăng ký
                                      </span>";
                            }
                            echo "</td>";
                        }

                        // Hành động (chỉ admin)
                        if ($can_manage) {
                            echo "<td>";
                            echo "<a href='edit_students.php?id=".$row['id']."' class='btn btn-edit' title='Sửa thông tin'>
                                    <i class='fas fa-edit'></i>
                                  </a>";
                            echo "<a href='../../handle/student_process.php?delete=".$row['id']."' 
                                     class='btn btn-delete' 
                                     title='Xóa học viên'
                                     onclick='return confirm(\"Bạn có chắc chắn muốn xóa học viên này?\\nHành động này không thể hoàn tác!\")'>
                                    <i class='fas fa-trash'></i>
                                  </a>";
                            echo "</td>";
                        }
                        
                        echo "</tr>";
                        $stt++;
                    }
                } else {
                    $colspan = 5;
                    if ($can_view_details) $colspan++;
                    if ($can_manage) $colspan++;
                    
                    echo "<tr><td colspan='$colspan' class='no-data'>
                            <i class='fas fa-user-slash'></i><br>
                            " . (empty($search) ? "Chưa có học viên nào trong hệ thống" : "Không tìm thấy học viên nào phù hợp") . "
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
                <!-- First page -->
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                <?php endif; ?>

                <!-- Previous page -->
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page-1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang trước">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>

                <!-- Page numbers -->
                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);
                
                if ($start_page > 1) {
                    echo "<span>...</span>";
                }
                
                for ($i = $start_page; $i <= $end_page; $i++):
                    if ($i == $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>"><?= $i ?></a>
                    <?php endif;
                endfor;
                
                if ($end_page < $total_pages) {
                    echo "<span>...</span>";
                }
                ?>

                <!-- Next page -->
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang sau">
                        <i class="fas fa-angle-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-right"></i></span>
                <?php endif; ?>

                <!-- Last page -->
                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang cuối">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function toggleCourses(id) {
        const el = document.getElementById('courses-' + id);
        const btn = el.previousElementSibling;
        
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'block';
            btn.innerHTML = '<i class="fas fa-eye-slash"></i> Ẩn';
        } else {
            el.style.display = 'none';
            btn.innerHTML = '<i class="fas fa-eye"></i> Xem (' + el.children.length + ')';
        }
    }
</script>
</body>
</html>