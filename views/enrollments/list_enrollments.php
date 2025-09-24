<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Cấu hình phân trang
$records_per_page = 8; // 8 cards per page for better layout
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_param = '';

if (!empty($search)) {
    $where_clause = "WHERE s.full_name LIKE ? OR c.course_name LIKE ? OR e.status LIKE ?";
    $search_param = "%$search%";
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM enrollments e
              JOIN students s ON e.student_id = s.id
              JOIN courses c ON e.course_id = c.id
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

// Lấy danh sách ghi danh với phân trang
$sql = "SELECT e.id, s.full_name as student_name, c.course_name, c.photo, 
               e.enrollment_date, e.status
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        $where_clause
        ORDER BY e.enrollment_date DESC
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
    <title>Danh sách ghi danh - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Giới hạn chiều rộng theo header */
        .enrollment-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin: 20px auto;
            width: 100%;
            max-width: 1400px; /* bằng với header */
        }

        /* Card */
        .enrollment-card {
            display: flex;
            align-items: center;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            border: 1px solid #e5e7eb;
            padding: 10px;
        }

        .enrollment-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 14px rgba(0,0,0,0.15);
        }

        /* Ảnh khóa học */
        .course-photo {
            width: 200px;
            height: 140px;
            flex-shrink: 0;
            overflow: hidden;
            border-radius: 8px;
            background: #f3f4f6;
        }

        .course-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Thông tin */
        .enrollment-info {
            padding: 15px 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .student-name {
            margin: 0;
            font-size: 1.4rem; /* chữ to hơn */
            font-weight: 700;
            color: #111827;
        }

        .enrollment-detail {
            margin: 0;
            color: #4b5563;
            font-size: 1.05rem; /* chữ chi tiết to hơn */
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .enrollment-detail i {
            width: 18px;
            color: #6b7280;
        }

        /* Badge trạng thái */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-inactive {
            background: #fef2f2;
            color: #991b1b;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .enrollment-actions {
            padding: 15px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            border-left: 1px solid #e5e7eb;
        }
        
        .no-enrollments {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 10px;
            border: 2px dashed #d1d5db;
        }
        
        .no-enrollments i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <!-- Header Section -->
        <div class="header-section">
            <div class="header-row">
                <h2>
                    <i class="fas fa-user-plus"></i>
                    Quản lý ghi danh
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_enrollments.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm ghi danh
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Tổng số: <?= $total_records ?> ghi danh</span>
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
                           placeholder="Tìm theo tên học viên, khóa học hoặc trạng thái..." 
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

        <!-- Enrollment Cards -->
        <div class="enrollment-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photo = (!empty($row['photo']) && file_exists(__DIR__."/../../uploads/courses/".$row['photo']))
                        ? "../../uploads/courses/".$row['photo']
                        : "https://via.placeholder.com/160x110?text=No+Image";
                    
                    // Status styling
                    $status_class = 'status-pending';
                    $status_icon = 'fas fa-clock';
                    switch(strtolower($row['status'])) {
                        case 'active':
                            $status_class = 'status-active';
                            $status_icon = 'fas fa-check-circle';
                            break;
                        case 'inactive':
                            $status_class = 'status-inactive';
                            $status_icon = 'fas fa-times-circle';
                            break;
                    }
                    
                    echo "<div class='enrollment-card'>";
                    echo "<div class='course-photo'>";
                    echo "<img src='$photo' alt='Ảnh khóa học'>";
                    echo "</div>";
                    
                    echo "<div class='enrollment-info'>";
                    echo "<h3 class='student-name'>" . htmlspecialchars($row['student_name']) . "</h3>";
                    echo "<p class='enrollment-detail'>";
                    echo "<i class='fas fa-graduation-cap'></i>";
                    echo "<strong>Khóa học:</strong> " . htmlspecialchars($row['course_name']);
                    echo "</p>";
                    echo "<p class='enrollment-detail'>";
                    echo "<i class='fas fa-calendar-plus'></i>";
                    echo "<strong>Ngày ghi danh:</strong> " . date('d/m/Y', strtotime($row['enrollment_date']));
                    echo "</p>";
                    echo "<div class='enrollment-detail'>";
                    echo "<i class='$status_icon'></i>";
                    echo "<strong>Trạng thái:</strong> ";
                    echo "<span class='status-badge $status_class'>";
                    echo "<i class='$status_icon'></i>";
                    echo ucfirst($row['status']);
                    echo "</span>";
                    echo "</div>";
                    echo "</div>";
                    
                    if ($can_manage) {
                        echo "<div class='enrollment-actions'>";
                        echo "<a href='edit_enrollments.php?id=" . $row['id'] . "' class='btn btn-edit' title='Sửa ghi danh'>";
                        echo "<i class='fas fa-edit'></i> Sửa";
                        echo "</a>";
                        echo "<a href='../../handle/enrollment_process.php?delete_id=" . $row['id'] . "' ";
                        echo "class='btn btn-delete' title='Xóa ghi danh' ";
                        echo "onclick='return confirm(\"Bạn có chắc chắn muốn xóa ghi danh này?\\nHành động này không thể hoàn tác!\")' ";
                        echo ">";
                        echo "<i class='fas fa-trash'></i> Xóa";
                        echo "</a>";
                        echo "</div>";
                    }
                    
                    echo "</div>";
                }
            } else {
                echo "<div class='no-enrollments'>";
                echo "<i class='fas fa-clipboard-list'></i><br>";
                echo (empty($search) ? "Chưa có ghi danh nào trong hệ thống" : "Không tìm thấy ghi danh nào phù hợp");
                echo "</div>";
            }
            $stmt->close();
            ?>
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

</body>
</html>