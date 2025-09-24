<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng ký tài khoản - English Center</title>
    <link rel="stylesheet" href="css/login.css"> 
</head>
<body>
    <div class="background-image"></div>
    <div class="login-wrapper">
        <div class="login-box">
            <h2>Đăng ký tài khoản </h2>

            <?php
                if(isset($_SESSION['error'])){
                    echo "<div class='error'>".$_SESSION['error']."</div>";
                    unset($_SESSION['error']);
                }
                if(isset($_SESSION['success'])){
                    echo "<div class='success'>".$_SESSION['success']."</div>";
                    unset($_SESSION['success']);
                }
            ?>

            <form action="handle/register_process.php" method="POST">
                <div class="input-group">
                    <label for="full_name">Họ tên</label>
                    <input type="text" name="full_name" id="full_name" placeholder="Nhập họ tên" required>
                </div>

                <div class="input-group">
                    <label for="dob">Ngày sinh</label>
                    <input type="date" name="dob" id="dob" required>
                </div>

                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" placeholder="Nhập email" required>
                </div>

                <div class="input-group">
                    <label for="phone">Số điện thoại</label>
                    <input type="text" name="phone" id="phone" placeholder="Nhập số điện thoại" required>
                </div>

                <div class="input-group">
                    <label for="username">Tên đăng nhập</label>
                    <input type="text" name="username" id="username" placeholder="Tạo username" required>
                </div>

                <div class="input-group">
                    <label for="password">Mật khẩu</label>
                    <input type="password" name="password" id="password" placeholder="Tạo password" required>
                </div>

                <button type="submit" class="btn-login">Đăng ký</button>
            </form>

            <!-- Nút quay lại luôn hiển thị -->
            <div style="margin-top:15px; text-align:center;">
                <a href="login.php" class="btn-back">Quay lại trang đăng nhập</a>
            </div>
        </div>
    </div>

    <script>
    // Ẩn thông báo sau 3 giây
    setTimeout(() => {
        document.querySelectorAll('.success, .error').forEach(el => el.style.display = 'none');
    }, 3000);
    </script>
</body>
</html>
