<?php
session_start();
include __DIR__ . '/../functions/db_connection.php';
$conn = getDbConnection();

// helper upload ảnh
function uploadImage($file, $uploadDir, $prefix = '') {
    $allowed = ['jpg','jpeg','png','gif', 'webp'];
    $maxSize = 4 * 1024 * 1024; // 4MB

    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > $maxSize) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return false;

    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $safeName = $prefix . time() . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', basename($file['name']));
    $target = $uploadDir . $safeName;

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $safeName;
    }
    return false;
}

/* ========== Thêm khóa học ========== */
if (isset($_POST['add_course'])) {
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $fee = floatval($_POST['fee'] ?? 0);
    $teacher_id = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : null;

    $photoName = null;
    $uploadDir = __DIR__ . "/../uploads/courses/";
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['photo'], $uploadDir, 'course_');
        if ($uploaded) $photoName = $uploaded;
    }

    $stmt = $conn->prepare("INSERT INTO courses (course_name, description, fee, teacher_id, photo) VALUES (?, ?, ?, ?, ?)");
    // bind types: s s d i s
    $stmt->bind_param("ssdis", $course_name, $description, $fee, $teacher_id, $photoName);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) header("Location: ../views/courses/list_courses.php");
    else echo "Lỗi khi thêm khóa học: " . $conn->error;
    exit();
}

/* ========== Cập nhật khóa học ========== */
if (isset($_POST['update_course'])) {
    $id = intval($_POST['id']);
    $course_name = trim($_POST['course_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $fee = floatval($_POST['fee'] ?? 0);
    $teacher_id = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== '' ? intval($_POST['teacher_id']) : null;

    // lấy ảnh cũ
    $oldPhoto = null;
    $s = $conn->prepare("SELECT photo FROM courses WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $resOld = $s->get_result();
    if ($rowOld = $resOld->fetch_assoc()) $oldPhoto = $rowOld['photo'];
    $s->close();

    $uploadDir = __DIR__ . "/../uploads/courses/";
    $newPhoto = $oldPhoto;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploaded = uploadImage($_FILES['photo'], $uploadDir, 'course_');
        if ($uploaded) {
            $newPhoto = $uploaded;
            // xóa file cũ (nếu có)
            if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                @unlink($uploadDir . $oldPhoto);
            }
        }
    }

    $stmt = $conn->prepare("UPDATE courses SET course_name = ?, description = ?, fee = ?, teacher_id = ?, photo = ? WHERE id = ?");
    // types: s s d i s i => "ssdisi"
    $stmt->bind_param("ssdisi", $course_name, $description, $fee, $teacher_id, $newPhoto, $id);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) header("Location: ../views/courses/course_detail.php?id=" . $id);
    else echo "Lỗi khi cập nhật: " . $conn->error;
    exit();
}

/* ========== Xóa khóa học ========== */
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // lấy ảnh để xóa file
    $s = $conn->prepare("SELECT photo FROM courses WHERE id = ?");
    $s->bind_param("i", $id);
    $s->execute();
    $res = $s->get_result();
    $photo = null;
    if ($r = $res->fetch_assoc()) $photo = $r['photo'];
    $s->close();

    // xóa db
    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();
    $stmt->close();

    if ($ok) {
        if ($photo) {
            $uploadDir = __DIR__ . "/../uploads/courses/";
            if (file_exists($uploadDir . $photo)) @unlink($uploadDir . $photo);
        }
        header("Location: ../views/courses/list_courses.php");
        exit();
    } else {
        echo "Xóa thất bại: " . $conn->error;
    }
}
