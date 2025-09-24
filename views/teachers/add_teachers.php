<?php
session_start();
include '../../functions/db_connection.php';

if(!isset($_SESSION['user_id'])){
    header("Location: ../../login.php");
    exit();
}

$conn = getDbConnection();

// Xử lý submit form
$errors = [];
if (isset($_POST['submit'])) {
    $full_name      = trim($_POST['full_name']);
    $dob            = trim($_POST['dob']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);

    if (empty($full_name)) $errors[] = "Họ tên không được để trống";
    if (empty($dob)) $errors[] = "Ngày sinh không được để trống";
    if (empty($email)) $errors[] = "Email không được để trống";

    // Xử lý upload ảnh
    $photo = null;
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../../uploads/teachers/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $targetFile = $targetDir . $fileName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png", "gif"];

        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFile)) {
                $photo = $fileName;
            } else {
                $errors[] = "Lỗi khi upload ảnh.";
            }
        } else {
            $errors[] = "Chỉ chấp nhận ảnh JPG, PNG, GIF.";
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO teachers (full_name, dob, email, phone, specialization, photo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $full_name, $dob, $email, $phone, $specialization, $photo);

        if ($stmt->execute()) {
            header("Location: list_teachers.php");
            exit();
        } else {
            $errors[] = "Lỗi khi thêm giảng viên: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm giảng viên - English Center</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content {
            flex-grow: 1;
            padding: 30px 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .form-container {
            width: 95%;
            max-width: 600px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display:block;
            margin-bottom:5px;
            font-weight: 600;
        }

        input[type="text"], input[type="email"], input[type="date"], input[type="file"] {
            width:100%;
            padding:10px;
            border:1px solid #ccc;
            border-radius:6px;
            font-size:1rem;
        }

        .btn {
            padding:6px 12px;
            text-decoration:none;
            border-radius:6px;
            color:white;
            font-size:0.9rem;
            display:inline-block;
            margin-right:5px;
            cursor:pointer;
            transition: all 0.25s ease;
        }

        .btn-submit { background-color:#10b981; }
        .btn-submit:hover { background-color:#059669; transform: scale(1.05); }

        .error {
            color:red;
            margin-bottom:10px;
        }

        .back-link {
            margin-top:15px;
            display:inline-block;
            text-decoration:none;
            color:#2563eb;
        }

        .back-link:hover {
            text-decoration:underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../menu.php'; ?>

        <div class="main-content">
            <div class="form-container">
                <h2>Thêm giảng viên</h2>

                <?php if(!empty($errors)): ?>
                    <div class="error">
                        <?php echo implode('<br>', $errors); ?>
                    </div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Họ tên:</label>
                        <input type="text" name="full_name" required>
                    </div>
                    <div class="form-group">
                        <label>Ngày sinh:</label>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>SĐT:</label>
                        <input type="text" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Chuyên môn:</label>
                        <input type="text" name="specialization">
                    </div>
                    <div class="form-group">
                        <label>Ảnh giảng viên:</label>
                        <input type="file" name="photo" accept="image/*">
                    </div>
                    <button type="submit" name="submit" class="btn btn-submit">Thêm giảng viên</button>
                </form>
                <a href="list_teachers.php" class="back-link">← Quay lại danh sách giảng viên</a>
            </div>
        </div>
    </div>
</body>
</html>
