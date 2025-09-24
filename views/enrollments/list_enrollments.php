<?php
session_start();
include '../../functions/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Phân quyền
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$is_student = isset($_SESSION['role']) && $_SESSION['role'] === 'student';
$is_teacher = isset($_SESSION['role']) && $_SESSION['role'] === 'teacher';

// Kiểm tra thông báo thành công
$success_message = '';
if (isset($_SESSION['enrollment_success'])) {
    $success_message = $_SESSION['enrollment_success'];
    unset($_SESSION['enrollment_success']);
}

// Cấu hình phân trang
$records_per_page = 8;
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

// Nếu là student, chỉ hiển thị khóa học active
if ($is_student) {
    $where_clause = empty($where_clause) ? "WHERE c.status = 'active'" : $where_clause . " AND c.status = 'active'";
}

// Đếm tổng số bản ghi
$count_sql = "SELECT COUNT(*) as total FROM courses c $where_clause";
if (!empty($search)) {
    $count_stmt = $conn->prepare($count_sql);
    if ($is_student) {
        // Nếu là student và có search, cần điều chỉnh bind_param
        $count_stmt->bind_param("ss", $search_param, $search_param);
    } else {
        $count_stmt->bind_param("ss", $search_param, $search_param);
    }
    $count_stmt->execute();
    $total_records = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();
} else {
    $total_records = $conn->query($count_sql)->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// Lấy danh sách khóa học với thông tin số học viên đã đăng ký
$sql = "SELECT c.*, 
               (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id AND e.status = 'active') as enrolled_count
        FROM courses c 
        $where_clause
        ORDER BY c.created_at DESC
        LIMIT ? OFFSET ?";

if (!empty($search)) {
    $stmt = $conn->prepare($sql);
    if ($is_student) {
        $stmt->bind_param("ssii", $search_param, $search_param, $records_per_page, $offset);
    } else {
        $stmt->bind_param("ssii", $search_param, $search_param, $records_per_page, $offset);
    }
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $records_per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Nếu là student, lấy danh sách khóa học đã đăng ký
$enrolled_courses = [];
if ($is_student) {
    $enrolled_sql = "SELECT course_id FROM enrollments WHERE student_id = ? AND status IN ('active', 'pending')";
    $enrolled_stmt = $conn->prepare($enrolled_sql);
    $enrolled_stmt->bind_param("i", $_SESSION['user_id']);
    $enrolled_stmt->execute();
    $enrolled_result = $enrolled_stmt->get_result();
    while ($row = $enrolled_result->fetch_assoc()) {
        $enrolled_courses[] = $row['course_id'];
    }
    $enrolled_stmt->close();
}

// Lấy danh sách students để admin có thể thêm vào khóa học
$students = [];
if ($is_admin) {
    $students_sql = "SELECT id, full_name, email FROM students WHERE status = 'active' ORDER BY full_name";
    $students_result = $conn->query($students_sql);
    if ($students_result) {
        while ($student = $students_result->fetch_assoc()) {
            $students[] = $student;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách khóa học - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .courses-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin: 20px auto;
            max-width: 1400px;
        }

        .course-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            border: 1px solid #e5e7eb;
            position: relative;
        }

        .course-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 14px rgba(0,0,0,0.15);
        }

        .course-photo {
            width: 100%;
            height: 200px;
            overflow: hidden;
            background: #f3f4f6;
        }

        .course-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-info {
            padding: 20px;
        }

        .course-title {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.3;
        }

        .course-description {
            color: #6b7280;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 15px;
        }

        .course-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: #4b5563;
        }

        .course-detail i {
            width: 16px;
            color: #6b7280;
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
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

        .course-actions {
            padding: 15px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .enrolled-badge {
            background: #dbeafe;
            color: #1e40af;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: inline-block;
        }

        /* Modal cho thêm sinh viên */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: #111827;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #6b7280;
            padding: 5px;
        }

        .modal-body {
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
        }

        .student-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            margin-bottom: 8px;
            background: #f9fafb;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: background-color 0.2s;
        }

        .student-checkbox:hover {
            background: #f3f4f6;
        }

        .student-checkbox input[type="checkbox"] {
            width: 16px;
            height: 16px;
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 600;
            color: #111827;
        }

        .student-email {
            font-size: 0.9rem;
            color: #6b7280;
        }

        .modal-footer {
            padding: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .no-courses {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 10px;
            border: 2px dashed #d1d5db;
            grid-column: 1 / -1;
        }

        .no-courses i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #9ca3af;
        }

        /* Đảm bảo button không bị disable */
        .btn.btn-primary {
            background: #3b82f6 !important;
            color: white !important;
            border: none !important;
            cursor: pointer !important;
            opacity: 1 !important;
        }

        .btn.btn-primary:hover {
            background: #2563eb !important;
        }

        .btn.btn-primary:disabled {
            background: #9ca3af !important;
            cursor: not-allowed !important;
        }
        .btn-cancel {
    background-color: #ef4444; /* đỏ */
    color: #fff;              /* chữ trắng */
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.btn-cancel:hover {
    background-color: #dc2626; /* đỏ đậm hơn khi hover */
}


        /* Success message */
        .success-message {
            background: #dcfce7;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
        }

        .success-message i {
            color: #16a34a;
            font-size: 1.2rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Auto hide after 5 seconds */
        .success-message.auto-hide {
            animation: slideDown 0.3s ease-out, fadeOut 0.3s ease-out 4.7s forwards;
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-10px);
            }
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <?php include __DIR__ . '/../menu.php'; ?>

    <div class="main-content">
        <!-- Success Message -->
        <?php if (!empty($success_message)): ?>
        <div class="success-message auto-hide" id="successMessage">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($success_message) ?></span>
            <button onclick="hideSuccessMessage()" style="margin-left: auto; background: none; border: none; color: #166534; cursor: pointer; font-size: 1.2rem;">&times;</button>
        </div>
        <?php endif; ?>

        <!-- Header Section -->
        <div class="header-section">
            <div class="header-row">
                <h2>
                    <i class="fas fa-graduation-cap"></i>
                    <?php if ($is_admin): ?>
                        Quản lý ghi danh
                    <?php else: ?>
                        Danh sách khóa học
                        <span class="role-badge role-<?= $_SESSION['role'] ?>">
                            <?= ucfirst($_SESSION['role']) ?>
                        </span>
                    <?php endif; ?>
                </h2>
                <?php if ($is_admin): ?>
                    <a href="../add_enrollments" class="btn btn-add">
                        <i class="fas fa-plus"></i> Thêm khóa học
                    </a>
                <?php endif; ?>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <i class="fas fa-list"></i>
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
                           placeholder="Tìm kiếm khóa học..." 
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

        <!-- Course Cards -->
        <div class="courses-list">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $photo = (!empty($row['photo']) && file_exists(__DIR__."/../../uploads/courses/".$row['photo']))
                        ? "../../uploads/courses/".$row['photo']
                        : "https://via.placeholder.com/350x200?text=No+Image";
                    
                    $status_class = $row['status'] === 'active' ? 'status-active' : 'status-inactive';
                    $is_enrolled = in_array($row['id'], $enrolled_courses);
                    
                    echo "<div class='course-card'>";
                    
                    // Status badge
                    echo "<div class='status-badge $status_class'>";
                    echo ucfirst($row['status']);
                    echo "</div>";
                    
                    // Course photo
                    echo "<div class='course-photo'>";
                    echo "<img src='$photo' alt='Ảnh khóa học'>";
                    echo "</div>";
                    
                    // Course info
                    echo "<div class='course-info'>";
                    
                    // Enrolled badge for students
                    if ($is_student && $is_enrolled) {
                        echo "<span class='enrolled-badge'><i class='fas fa-check-circle'></i> Đã đăng ký</span>";
                    }
                    
                    echo "<h3 class='course-title'>" . htmlspecialchars($row['course_name']) . "</h3>";
                    
                    if (!empty($row['description'])) {
                        echo "<p class='course-description'>" . htmlspecialchars($row['description']) . "</p>";
                    }
                    
                    echo "<div class='course-meta'>";
                    
                    if (!empty($row['duration'])) {
                        echo "<div class='course-detail'>";
                        echo "<i class='fas fa-clock'></i>";
                        echo "<span><strong>Thời lượng:</strong> " . htmlspecialchars($row['duration']) . "</span>";
                        echo "</div>";
                    }
                    
                    if (!empty($row['price'])) {
                        echo "<div class='course-detail'>";
                        echo "<i class='fas fa-tag'></i>";
                        echo "<span><strong>Học phí:</strong> " . number_format($row['price'], 0, ',', '.') . " VNĐ</span>";
                        echo "</div>";
                    }
                    
                    echo "<div class='course-detail'>";
                    echo "<i class='fas fa-users'></i>";
                    echo "<span><strong>Đã đăng ký:</strong> " . $row['enrolled_count'] . " học viên</span>";
                    echo "</div>";
                    
                    if (!empty($row['created_at'])) {
                        echo "<div class='course-detail'>";
                        echo "<i class='fas fa-calendar-plus'></i>";
                        echo "<span><strong>Ngày tạo:</strong> " . date('d/m/Y', strtotime($row['created_at'])) . "</span>";
                        echo "</div>";
                    }
                    
                    echo "</div>"; // End course-meta
                    echo "</div>"; // End course-info
                    
                    // Actions
                    echo "<div class='course-actions'>";
                    
                    // Debug info
                    if ($is_admin) {
                        echo "<!-- Admin detected: " . $_SESSION['role'] . " -->";
                    }
                    
                    if ($is_admin) {
                        // Admin actions
                        echo "<a href='edit_enrollments.php?id=" . $row['id'] . "' class='btn btn-edit'>";
                        echo "<i class='fas fa-edit'></i> Sửa";
                        echo "</a>";
                        
                        echo "<button onclick='openAddStudentsModal(" . $row['id'] . ", \"" . htmlspecialchars($row['course_name'], ENT_QUOTES) . "\")' class='btn btn-primary' style='background: #3b82f6; color: white; border: none;'>";
                        echo "<i class='fas fa-user-plus'></i> Thêm SV";
                        echo "</button>";
                        
                        echo "<a href='../../handle/course_process.php?delete_id=" . $row['id'] . "' ";
                        echo "class='btn btn-delete' ";
                        echo "onclick='return confirm(\"Bạn có chắc chắn muốn xóa khóa học này?\\nHành động này không thể hoàn tác!\")' ";
                        echo "title='Xóa khóa học'>";
                        echo "<i class='fas fa-trash'></i> Xóa";
                        echo "</a>";
                        
                    } elseif ($is_student) {
                        echo "<!-- Student detected: " . $_SESSION['role'] . " -->";
                        // Student actions
                        if (!$is_enrolled && $row['status'] === 'active') {
                            echo "<a href='../../handle/enrollment_process.php?course_id=" . $row['id'] . "&student_id=" . $_SESSION['user_id'] . "' ";
                            echo "class='btn btn-primary' ";
                            echo "onclick='return confirm(\"Bạn có chắc chắn muốn đăng ký khóa học này?\")' ";
                            echo ">";
                            echo "<i class='fas fa-user-plus'></i> Đăng ký";
                            echo "</a>";
                        } elseif ($is_enrolled) {
                            echo "<span class='btn btn-success' style='cursor: default;'>";
                            echo "<i class='fas fa-check'></i> Đã đăng ký";
                            echo "</span>";
                        }
                    } else {
                        echo "<!-- Role: " . ($_SESSION['role'] ?? 'undefined') . " -->";
                    }
                    
                    echo "</div>"; // End course-actions
                    echo "</div>"; // End course-card
                }
            } else {
                echo "<div class='no-courses'>";
                echo "<i class='fas fa-graduation-cap'></i><br>";
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
                <?php if ($page > 1): ?>
                    <a href="?page=1<?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang đầu">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page-1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang trước">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>

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

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?= $page+1 ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang sau">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search='.urlencode($search) : '' ?>" title="Trang cuối">
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

<!-- Modal thêm sinh viên (chỉ cho admin) -->
<?php if ($is_admin && !empty($students)): ?>
<div id="addStudentsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Thêm sinh viên vào khóa học</h3>
            <button class="close-btn" onclick="closeAddStudentsModal()">&times;</button>
        </div>
        <form id="addStudentsForm" action="../../handle/enrollment_process.php" method="POST">
            <input type="hidden" id="modal_course_id" name="course_id" value="">
            <input type="hidden" name="action" value="bulk_add">
            
            <div class="modal-body">
                <p><strong>Khóa học:</strong> <span id="modal_course_name"></span></p>
                <p style="margin-bottom: 20px; color: #6b7280;">Chọn sinh viên để thêm vào khóa học:</p>
                
                <div style="margin-bottom: 15px;">
                    <label>
                        <input type="checkbox" id="selectAll" onchange="toggleAllStudents(this)">
                        <strong>Chọn tất cả</strong>
                    </label>
                </div>
                
                <?php foreach ($students as $student): ?>
                <div class="student-checkbox">
                    <input type="checkbox" name="student_ids[]" value="<?= $student['id'] ?>" class="student-checkbox-item">
                    <div class="student-info">
                        <div class="student-name"><?= htmlspecialchars($student['full_name']) ?></div>
                        <div class="student-email"><?= htmlspecialchars($student['email']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel" onclick="closeAddStudentsModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm sinh viên
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Modal functions cho admin
function openAddStudentsModal(courseId, courseName) {
    document.getElementById('modal_course_id').value = courseId;
    document.getElementById('modal_course_name').textContent = courseName;
    document.getElementById('addStudentsModal').style.display = 'block';
    
    // Reset checkboxes
    document.getElementById('selectAll').checked = false;
    const checkboxes = document.querySelectorAll('.student-checkbox-item');
    checkboxes.forEach(cb => cb.checked = false);
}

function closeAddStudentsModal() {
    document.getElementById('addStudentsModal').style.display = 'none';
}

function toggleAllStudents(selectAllCheckbox) {
    const checkboxes = document.querySelectorAll('.student-checkbox-item');
    checkboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
}

// Success message functions
function hideSuccessMessage() {
    const message = document.getElementById('successMessage');
    if (message) {
        message.style.animation = 'fadeOut 0.3s ease-out forwards';
        setTimeout(() => message.remove(), 300);
    }
}

// Auto hide success message after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
        setTimeout(hideSuccessMessage, 5000);
    }
});

// Đóng modal khi click bên ngoài
window.onclick = function(event) {
    const modal = document.getElementById('addStudentsModal');
    if (event.target === modal) {
        closeAddStudentsModal();
    }
}

// Kiểm tra form trước khi submit
document.getElementById('addStudentsForm')?.addEventListener('submit', function(e) {
    const checkedBoxes = document.querySelectorAll('.student-checkbox-item:checked');
    if (checkedBoxes.length === 0) {
        e.preventDefault();
        alert('Vui lòng chọn ít nhất một sinh viên!');
        return false;
    }
    
    return confirm('Bạn có chắc chắn muốn thêm ' + checkedBoxes.length + ' sinh viên vào khóa học này?');
});
</script>

</body>
</html>
