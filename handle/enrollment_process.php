<?php
include '../functions/db_connection.php';
$conn = getDbConnection();

/* ==============================
   THÊM GHI DANH
============================== */
if(isset($_POST['add_enrollment'])){
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];
    $enrollment_date = $_POST['enrollment_date'];
    $status = $_POST['status'];

    $sql = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status)
            VALUES ($student_id, $course_id, '$enrollment_date', '$status')";
    $conn->query($sql);

    header("Location: ../views/enrollments/list_enrollments.php");
    exit();
}

/* ==============================
   CẬP NHẬT GHI DANH
============================== */
if(isset($_POST['update_enrollment'])){
    $id = $_POST['id'];
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];
    $enrollment_date = $_POST['enrollment_date'];
    $status = $_POST['status'];

    $sql = "UPDATE enrollments 
            SET student_id=$student_id, course_id=$course_id, enrollment_date='$enrollment_date', status='$status'
            WHERE id=$id";
    $conn->query($sql);

    header("Location: ../views/enrollments/list_enrollments.php");
    exit();
}

/* ==============================
   XÓA GHI DANH
============================== */
if(isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM enrollments WHERE id=$id");

    header("Location: ../views/enrollments/list_enrollments.php");
    exit();
}
?>
