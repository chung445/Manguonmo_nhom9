<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// --- Cấu hình phân trang ---
$records_per_page = 8; // số card mỗi trang
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// --- Tìm kiếm ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where_clause = '';
$search_param = '';

if (!empty($search)) {
    $where_clause = "WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR specialization LIKE ?";
    $search_param = "%$search%";
}

// --- Đếm tổng số bản ghi ---
$count_sql = "SELECT COUNT(*) as total FROM teachers $where_clause";
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param("ssss", $search_param, $search_param, $search_param, $search_param);
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// --- Lấy danh sách giảng viên ---
$sql = "SELECT id, full_name, dob, email, phone, specialization, photo 
        FROM teachers $where_clause 
        ORDER BY id DESC 
        LIMIT ? OFFSET ?";
if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $search_param, $search_param, $search_param, $search_param, $records_per_page, $offset);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// --- Phân quyền ---
$is_admin   = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';
$can_manage = $is_admin;                 // Chỉ admin có thể thêm/sửa/xóa
$can_view   = $is_admin || $is_teacher;  // Admin và teacher xem chi tiết
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách giảng viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content { 
            flex-grow: 1; 
            padding: 30px; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            min-height: 100vh;
        }
        
        /* Header giống học viên */
        .header-section {
            width: 95%; 
            max-width: 1400px; 
            background: linear-gradient(90deg, #2563eb 0%, #1e40af 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(37,99,235,0.2);
        }
        .header-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 12px;
        }
        .header-section h2 {
            margin: 0;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .stats-row {
            display: flex;
            align-items: center;
            gap: 24px;
            font-size: 0.95rem;
            opacity: 0.9;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .controls-section {
            width: 95%;
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 16px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .search-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .search-box { 
            padding: 10px 16px; 
            border-radius: 8px; 
            border: 2px solid #e5e7eb; 
            font-size: 15px; 
            outline: none; 
            background: #f9fafb;
            width: 280px;
            transition: border-color 0.2s;
        }
        .search-box:focus {
            border-color: #2563eb;
        }
        .search-btn {
            padding: 10px 16px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .search-btn:hover {
            background: #1d4ed8;
        }
        
        .btn { 
            padding: 8px 14px; 
            border-radius: 6px; 
            color: white; 
            font-size: 0.85rem; 
            margin-right: 6px; 
            text-decoration: none; 
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-add { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            margin-left: 8px;
        }
        .role-admin { background: #fef3c7; color: #f59e0b; }
        .role-teacher { background: #ddd6fe; color: #7c3aed; }
        
        /* Cards layout */
        .cards-container { 
            width: 95%; 
            max-width: 1400px; 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
            gap: 24px; 
            margin-bottom: 32px;
        }
        
        .teacher-card { 
            background: #fff; 
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08); 
            transition: all 0.3s ease;
            border: 2px solid #f1f5f9;
        }
        .teacher-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            border-color: #e2e8f0;
        }
        
        .teacher-photo-wrapper { 
            width: 100%; 
            height: 200px; 
            overflow: hidden; 
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); 
            display: flex; 
            align-items: center; 
            justify-content: center;
            position: relative;
        }
        .teacher-photo { 
            max-width: 100%; 
            max-height: 100%; 
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .teacher-card:hover .teacher-photo {
            transform: scale(1.05);
        }
        .photo-overlay {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(37, 99, 235, 0.9);
            color: white;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .teacher-info { 
            padding: 20px; 
            text-align: center; 
        }
        .teacher-name { 
            font-size: 1.2rem; 
            font-weight: 700; 
            margin-bottom: 8px; 
            color: #1e293b;
            line-height: 1.3;
        }
        .teacher-specialization { 
            font-size: 0.95rem; 
            color: #64748b; 
            margin-bottom: 6px;
            font-weight: 500;
        }
        .teacher-email {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 16px;
            word-break: break-all;
        }
        
        .teacher-actions {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .detail-link, .btn-edit, .btn-delete { 
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px; 
            border-radius: 8px; 
            font-size: 0.85rem; 
            font-weight: 600; 
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .detail-link { 
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); 
            color: white; 
        }
        .detail-link:hover { 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            transform: translateY(-1px);
        }
        .btn-edit { 
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
            color: white; 
        }
        .btn-edit:hover { 
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            transform: translateY(-1px);
        }
        .btn-delete { 
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); 
            color: white; 
        }
        .btn-delete:hover { 
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            transform: translateY(-1px);
        }
        
        /* No data state */
        .no-data-container {
            width: 95%;
            max-width: 1400px;
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        .no-data-container i {
            font-size: 4rem;
            color: #cbd5e1;
            margin-bottom: 16px;
        }
        .no-data-container h3 {
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: #475569;
        }
        .no-data-container p {
            font-size: 0.95rem;
            color: #64748b;
        }
        
        /* Pagination */
        .pagination-container {
            width: 95%;
            max-width: 1400px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding: 16px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .pagination-info {
            color: #6b7280;
            font-size: 0.9rem;
        }
        .pagination {
            display: flex;
            gap: 6px;
            align-items: center;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .pagination a {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #d1d5db;
        }
        .pagination a:hover {
            background: #e5e7eb;
            transform: translateY(-1px);
        }
        .pagination .current {
            background: #2563eb;
            color: white;
            font-weight: 600;
        }
        .pagination .disabled {
            background: #f9fafb;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        @media (max-width: 768px) {
            .controls-section {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
            .search-box {
                width: 100%;
            }
            .cards-container {
                grid-template-columns: 1fr;
            }
            .pagination-container {
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }
            .teacher-actions {
                flex-direction: column;
            }
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
                    <i class="fas fa-chalkboard-teacher"></i>
                    Quản lý giảng viên
                    <?php if (!$can_manage): ?>
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?> - Chỉ xem
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($can_manage): ?>
                    <a href="add_teachers.php" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm giảng viên
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span>Tổng số: <?= $total_records ?> giảng viên</span>
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
                           placeholder="Tìm theo tên, email, SĐT hoặc chuyên môn..." 
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

        <!-- Cards -->
        <?php if ($result && $result->num_rows > 0): ?>
        <div class="cards-container">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $photo = !empty($row['photo']) ? "../../uploads/teachers/".$row['photo'] : "https://via.placeholder.com/280x200/e5e7eb/9ca3af?text=No+Image";
                ?>
                <div class='teacher-card'>
                    <div class='teacher-photo-wrapper'>
                        <img src='<?= $photo ?>' alt='Ảnh <?= htmlspecialchars($row['full_name']) ?>' class='teacher-photo'>
                        <div class="photo-overlay">
                            <i class="fas fa-user-tie"></i> GV
                        </div>
                    </div>
                    <div class='teacher-info'>
                        <div class='teacher-name'><?= htmlspecialchars($row['full_name']) ?></div>
                        <div class='teacher-specialization'>
                            <i class="fas fa-graduation-cap"></i>
                            <?= htmlspecialchars($row['specialization'] ?? 'Chuyên môn chưa cập nhật') ?>
                        </div>
                        <div class='teacher-email'>
                            <i class="fas fa-envelope"></i>
                            <?= htmlspecialchars($row['email']) ?>
                        </div>
                        
                        <div class="teacher-actions">
                            <?php if ($can_view): ?>
                                <a href='teacher_detail.php?id=<?= $row['id'] ?>' class='detail-link' title="Xem chi tiết">
                                    <i class='fas fa-eye'></i> Xem chi tiết
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($can_manage): ?>
                                <a href='edit_teachers.php?id=<?= $row['id'] ?>' class='btn-edit' title="Sửa thông tin">
                                    <i class='fas fa-edit'></i> Sửa
                                </a>
                                <a href='../../handle/teacher_process.php?delete=<?= $row['id'] ?>' 
                                   class='btn-delete' 
                                   title="Xóa giảng viên"
                                   onclick='return confirm("Bạn có chắc chắn muốn xóa giảng viên này?\nHành động này không thể hoàn tác!")'>
                                    <i class='fas fa-trash'></i> Xóa
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-data-container">
            <i class="fas fa-chalkboard-teacher"></i>
            <h3><?= empty($search) ? "Chưa có giảng viên nào" : "Không tìm thấy giảng viên" ?></h3>
            <p><?= empty($search) ? "Hệ thống chưa có giảng viên nào được thêm vào" : "Không tìm thấy giảng viên phù hợp với từ khóa \"".htmlspecialchars($search)."\"" ?></p>
            <?php if ($can_manage && empty($search)): ?>
                <a href="add_teachers.php" class="btn btn-add" style="margin-top: 16px;">
                    <i class="fas fa-plus"></i> Thêm giảng viên đầu tiên
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

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