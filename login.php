<?php
session_start();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập - English Center</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="background-image"></div>
    <div class="login-wrapper">
        <div class="login-box">
            <div class="login-logo-wrapper" style="text-align:center; margin-bottom:12px;">
                <img src="images/logo3.png" alt="Logo" style="max-width:70px; height:auto; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.10);">
            </div>
            <h2>Đăng nhập</h2>

            <?php
            if(isset($_SESSION['error'])){
                echo "<div class='error'>".$_SESSION['error']."</div>";
                unset($_SESSION['error']);
            }
            ?>

            <form action="handle/login_process.php" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" placeholder="Nhập username" required>
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Nhập password" required>
                </div>

                <button type="submit" class="btn-login">Đăng nhập</button>
            </form>
            <div class="register-link">
                <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
        </div>
        <div class="login-bottom-image-wrapper">
            <img src="images/anh2.jpg" alt="Login Bottom Image" class="login-bottom-image">
        </div>
    </div>
    <script>
    $(document).ready(function(){
        // Hiệu ứng hover cho logo và ảnh dưới form
        $(".login-logo-wrapper img, .login-bottom-image").hover(
            function(){
                $(this).animate({ marginLeft: "10px" }, 200)
                       .animate({ marginLeft: "0px" }, 200);
            }
        );

        // Hiệu ứng khi click nút đăng nhập
        $(".btn-login").click(function(){
            $(this).fadeOut(100).fadeIn(200);
        });

        // Ẩn thông báo lỗi sau 3 giây (nếu có)
        setTimeout(() => {
            $(".error").fadeOut("slow");
        }, 3000);
    });
    </script>
    <script>
    $(document).ready(function(){
        $("form").submit(function(e){
            let user = $("#username").val().trim();
            let pass = $("#password").val().trim();

            if(user === "" || pass === ""){
                alert("Vui lòng nhập đầy đủ Username và Password!");
                e.preventDefault(); // chặn submit
            }
        });
    });
    </script>

</body>
</html>
