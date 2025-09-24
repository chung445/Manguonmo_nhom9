<?php
// ...existing code...
?>
<div class="sidebar">
    <div class="sidebar-title-logo">
        <img src="/BTLSC/images/logo3.png" alt="Logo" class="sidebar-logo">
        <h2>Carrington <br>English Center</h2>
    </div>
    <div class="sidebar-search">
        <input type="text" id="menuSearch" placeholder="Tìm chức năng..." onkeyup="filterMenu()">
    </div>
    <ul>
        <li><a href="/BTLSC/index.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="/BTLSC/views/students/list_students.php"><i class="fas fa-user-graduate"></i> Học viên</a></li>
        <li><a href="/BTLSC/views/teachers/list_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Giảng viên</a></li>
        <li><a href="/BTLSC/views/courses/list_courses.php"><i class="fas fa-book"></i> Khóa học</a></li>
        <li><a href="/BTLSC/views/schedules/list_schedules.php"><i class="fas fa-calendar-alt"></i> Lịch học</a></li>
        <li><a href="/BTLSC/views/enrollments/list_enrollments.php"><i class="fas fa-user-plus"></i> Đăng ký khóa học</a></li>
        <li><a href="/BTLSC/views/grades/list_grades.php"><i class="fas fa-star"></i> Điểm số</a></li>
        <li><a href="/BTLSC/handle/logout_process.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
    </ul>
    <script>
    function filterMenu() {
        var input = document.getElementById('menuSearch');
        var filter = input.value.toLowerCase();
        var ul = input.parentElement.nextElementSibling;
        var li = ul.getElementsByTagName('li');
        for (var i = 0; i < li.length; i++) {
            var a = li[i].getElementsByTagName('a')[0];
            var txt = a.textContent || a.innerText;
            li[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
        }
    }
    </script>
</div>  
<?php
// ...existing code...
?>
