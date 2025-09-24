
<h2 align="center">
    <a href="https://dainam.edu.vn/vi/khoa-cong-nghe-thong-tin">
    🎓 Faculty of Information Technology (DaiNam University)
    </a>
</h2>
<h2 align="center">
    PLATFORM ERP
</h2>
<div align="center">
    <p align="center">
        <img src="images/aiotlab_logo.png" alt="AIoTLab Logo" width="170"/>
        <img src="images/fitdnu_logo.png" alt="AIoTLab Logo" width="180"/>
        <img src="images/dnu_logo.png" alt="DaiNam University Logo" width="200"/>
    </p>

[![AIoTLab](https://img.shields.io/badge/AIoTLab-green?style=for-the-badge)](https://www.facebook.com/DNUAIoTLab)
[![Faculty of Information Technology](https://img.shields.io/badge/Faculty%20of%20Information%20Technology-blue?style=for-the-badge)](https://dainam.edu.vn/vi/khoa-cong-nghe-thong-tin)
[![DaiNam University](https://img.shields.io/badge/DaiNam%20University-orange?style=for-the-badge)](https://dainam.edu.vn)

</div>

## 📖 1. Giới thiệu
Đề tài được lựa chọn xuất phát từ nhu cầu thực tiễn trong việc quản lý tại các trung tâm tiếng Anh, nơi có khối lượng lớn thông tin cần được tổ chức và theo dõi như học viên, giảng viên, khóa học, lịch học và quá trình ghi danh. Việc quản lý thủ công dễ dẫn đến sai sót, tốn thời gian và làm giảm hiệu quả vận hành. Do đó, xây dựng một hệ thống quản lý số hóa, tích hợp và an toàn sẽ không chỉ hỗ trợ admin trong việc thêm, sửa, xóa dữ liệu mà còn giúp theo dõi tiến độ học tập, lịch giảng dạy một cách khoa học, đồng thời mang lại trải nghiệm tốt hơn cho học viên, phụ huynh và giáo viên. Đây cũng là giải pháp có tính mở rộng, phù hợp với xu hướng ứng dụng công nghệ trong giáo dục hiện nay.
Quản lý học viên (CRUD: thêm, sửa, xóa, xem danh sách) → liên quan bảng students

Quản lý giảng viên (CRUD) → liên quan bảng teachers

Quản lý khóa học (CRUD) → liên quan bảng courses

Quản lý lịch học (CRUD) → liên quan bảng schedules

Quản lý ghi danh (CRUD) → liên quan bảng enrollments

Quản lý sinh viên (CRUD) → liên quan bảng students

Quản lý báo cáo thống kê → liên quan bảng report_logs (tổng hợp từ nhiều bảng khác: payments, attendance, enrollments)

Đăng nhập/Đăng xuất → liên quan bảng users

Phân quyền người dùng → dựa trên cột role trong bảng users (admin, teacher, student)

Dashboard tổng quan → hiển thị thống kê: số lượng học viên, số khóa học, doanh thu, sever...

## 🔧 2. Các công nghệ được sử dụng
<div align="center">

### Hệ điều hành
[![Windows](https://img.shields.io/badge/Windows-0078D6?style=for-the-badge&logo=windows&logoColor=white)](https://www.microsoft.com/en-us/windows/)
[![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)](https://ubuntu.com/)
[![macOS](https://img.shields.io/badge/macOS-000000?style=for-the-badge&logo=apple&logoColor=white)](https://www.apple.com/macos/)

### Công nghệ chính
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/HTML)
[![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)
[![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)](https://developer.mozilla.org/en-US/docs/Web/JavaScript)

### Web Server & Database
[![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white)](https://httpd.apache.org/)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)](https://www.apachefriends.org/)
[![MySQL Workbench](https://img.shields.io/badge/MySQL_Workbench-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://dev.mysql.com/downloads/workbench/)

</div>

## ⚙️ 3. Cài đặt và Sử dụng

### 3.1. Yêu cầu hệ thống

- **Web Server**: Apache/Nginx
- **PHP**: Version 7.4 trở lên
- **Database**: MySQL 5.7+ hoặc MariaDB
- **XAMPP** (khuyến nghị cho Windows)
- **MySQL Workbench** (để quản lý database)
## 3.2 Sử dụng hệ thống
 <p align="center">
###Dashboard      
<img src="images/dash.jpg" />
### Trang xem danh sách sinh viên
<img src="images/student.png" />
### Trang xem danh sách giảng viên
<img src="images/giangvien.png" />
### Trang xem khóa học
<img src="images/khoahoc.png" />
### Trang xem lịch học
<img src="images/lichhoc.png" />
### Trang xem đăng kí khóa học
<img src="images/dangki.png" />
### Trang xem điểm
<img src="images/diem.png" />
     
    </p>

" />

## ⚙️ 4. Cài đặt

### 4.1. Cài đặt công cụ, môi trường và các thư viện cần thiết

- Tải và cài đặt **XAMPP**  
  👉 https://www.apachefriends.org/download.html  
  (Khuyến nghị bản XAMPP với PHP 8.x)

- Cài đặt **Visual Studio Code** và các extension:
  - PHP Intelephense  
  - MySQL  
  - Prettier – Code Formatter  
### 4.2. Tải project
Clone project về thư mục `htdocs` của XAMPP (ví dụ ổ C):

```bash
cd C:\xampp\htdocs
(https://github.com/chung445/Manguonmo_nhom9)
Truy cập project qua đường dẫn:
👉 https://github.com/chung445/Manguonmo_nhom9/index.php
```
### 4.3. Setup database
Mở XAMPP Control Panel, Start Apache và MySQL

Truy cập MySQL WorkBench
Tạo database:
```bash
CREATE DATABASE english_center;
USE english_center;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    dob DATE,
    email VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
...
);
```

### 4.4. Setup tham số kết nối
Mở file config.php (hoặc .env) trong project, chỉnh thông tin DB:
```bash

<?php
    function getDbConnection() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "english_center";
        $port = 3306;
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);
        if (!$conn) {
            die("Kết nối database thất bại: " . mysqli_connect_error());
        }
        mysqli_set_charset($conn, "utf8");
        return $conn;
    }
?>
```
### 4.5. Chạy hệ thống
Mở XAMPP Control Panel → Start Apache và MySQL

Truy cập hệ thống:
👉 http://localhost/index.php

### 4.6. Đăng nhập lần đầu
Hệ thống có thể cấp tài khoản admin 

Sau khi đăng nhập Admin có thể:

Thêm sửa xóa sinh viên, giảng viên, khóa học, lịch học

Hiển thị Dashboard cho quản trị viên

Quản lý phân quyền theo cấp
