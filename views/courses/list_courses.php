<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Cấu hình phân trang
$records_per_page = 6; // 6 cards per page for better layout
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_param = '';

if (!empty($search)) {
    $where_clause = "WHERE c.course_name LIKE ? OR c.description LIKE ?";
    $search_param = "%$search%";
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM courses c $where_clause";
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

// Lấy danh sách khóa học với phân trang
$sql = "SELECT c.id, c.course_name, c.description, c.fee, c.photo,
               t.full_name AS teacher_name,
               COUNT(e.student_id) AS total_students
        FROM courses c
        LEFT JOIN teachers t ON c.teacher_id = t.id
        LEFT JOIN enrollments e ON c.id = e.course_id
        $where_clause
        GROUP BY c.id, c.course_name, c.description, c.fee, c.photo, t.full_name
        ORDER BY c.id DESC 
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
$can_manage = $is_admin; // Chỉ admin mới có thể thêm/sửa/xóa
$can_view_details = $is_admin || $is_teacher; // Admin và teacher có thể xem chi tiết
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách khóa học - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
.cards-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 card / hàng */
    gap: 20px;
    margin-top: 20px;
}
@media (max-width: 992px) {
    .cards-container {
        grid-template-columns: repeat(2, 1fr); /* Tablet: 2 card */
    }
}

@media (max-width: 576px) {
    .cards-container {
        grid-template-columns: 1fr; /* Mobile: 1 card */
    }
}

        
        .course-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            border: 1px solid #e5e7eb;
        }
        
        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        }
        
        .course-photo-wrapper {
            width: 100%;
            height: 180px;
            overflow: hidden;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .course-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .course-info {
            padding: 20px;
        }
        
        .course-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
            color: #111827;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .course-teacher {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .course-students {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .course-desc {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 15px;
            line-height: 1.4;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .course-fee {
            font-size: 1.1rem;
            font-weight: 700;
            color: #059669;
            margin-bottom: 15px;
        }
        
        .card-actions {
            display: flex;
            gap: 10px;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-link {
            padding: 8px 16px;
            background: #2563eb;
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background 0.2s;
        }
        
        .detail-link:hover {
            background: #1d4ed8;
        }
        
        .card-admin-actions {
            display: flex;
            gap: 8px;
        }
        
        .no-courses {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 10px;
            border: 2px dashed #d1d5db;
        }
        
        .no-courses i {
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
                    <i class="fas fa-graduation-cap"></i>
                    Quản lý khóa học
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_courses.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm khóa học
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-book-open"></i>
                    <span>Tổng số: <?= $total_records ?> khóa học</span>
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
                           placeholder="Tìm theo tên khóa học hoặc mô tả..." 
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

        <!-- Cards Container -->
        <div class="cards-container">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photo = !empty($row['photo']) ? "../../uploads/courses/".$row['photo'] : "https://via.placeholder.com/400x180?text=No+Image";
                    $fee = number_format($row['fee'], 0, ',', '.') . ' VNĐ';
                    
                    echo "<div class='course-card'>";
                    echo "<div class='course-photo-wrapper'>";
                    echo "<img src='$photo' alt='Ảnh khóa học' class='course-photo'>";
                    echo "</div>";
                    echo "<div class='course-info'>";
                    echo "<div class='course-name'>" . htmlspecialchars($row['course_name']) . "</div>";
                    
                    echo "<div class='course-meta'>";
                    echo "<div class='course-teacher'>";
                    echo "<i class='fas fa-chalkboard-teacher'></i>";
                    echo "<span>" . (htmlspecialchars($row['teacher_name']) ?: 'Chưa phân công') . "</span>";
                    echo "</div>";
                    echo "<div class='course-students'>";
                    echo "<i class='fas fa-user-graduate'></i>";
                    echo "<span>" . $row['total_students'] . " học viên</span>";
                    echo "</div>";
                    echo "</div>";
                    
                    echo "<div class='course-desc'>" . htmlspecialchars($row['description']) . "</div>";
                    echo "<div class='course-fee'>$fee</div>";
                    
                    echo "<div class='card-actions'>";
                    echo "<a href='course_detail.php?id=" . $row['id'] . "' class='detail-link'>";
                    echo "<i class='fas fa-eye'></i> Xem chi tiết";
                    echo "</a>";
                    
                    if ($can_manage) {
                        echo "<div class='card-admin-actions'>";
                        echo "<a href='edit_courses.php?id=" . $row['id'] . "' class='btn btn-edit' title='Sửa khóa học'>";
                        echo "<i class='fas fa-edit'></i>";
                        echo "</a>";
                        echo "<a href='../../handle/course_process.php?delete=" . $row['id'] . "' ";
                        echo "class='btn btn-delete' title='Xóa khóa học' ";
                        echo "onclick='return confirm(\"Bạn có chắc chắn muốn xóa khóa học này?\\nHành động này không thể hoàn tác!\")' ";
                        echo ">";
                        echo "<i class='fas fa-trash'></i>";
                        echo "</a>";
                        echo "</div>";
                    }
                    echo "</div>";
                    
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='no-courses'>";
                echo "<i class='fas fa-book-open'></i><br>";
                echo (empty($search) ? "Chưa có khóa học nào trong hệ thống" : "Không tìm thấy khóa học nào phù hợp");
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