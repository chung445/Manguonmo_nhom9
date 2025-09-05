<?php
include '../functions/db_connection.php';
$conn = getDbConnection();

/* ==============================
   THÊM KHÓA HỌC
============================== */
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $fee = $_POST['fee'];
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : "NULL";

    $sql = "INSERT INTO courses (course_name, description, fee, teacher_id)
            VALUES ('$course_name', '$description', '$fee', $teacher_id)";
    $conn->query($sql);

    header("Location: ../views/courses/list_courses.php");
    exit();
}

/* ==============================
   CẬP NHẬT KHÓA HỌC
============================== */
if (isset($_POST['update_course'])) {
    $id = $_POST['id'];
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $fee = $_POST['fee'];
    $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : "NULL";

    $sql = "UPDATE courses 
            SET course_name='$course_name', description='$description', fee='$fee', teacher_id=$teacher_id
            WHERE id=$id";
    $conn->query($sql);

    header("Location: ../views/courses/list_courses.php");
    exit();
}

/* ==============================
   XÓA KHÓA HỌC
============================== */
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];

    $conn->query("DELETE FROM courses WHERE id=$id");

    header("Location: ../views/courses/list_courses.php");
    exit();
}
?>
