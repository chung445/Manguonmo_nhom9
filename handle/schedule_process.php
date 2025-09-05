<?php
include '../functions/db_connection.php';
$conn = getDbConnection();

/* ==============================
   THÊM LỊCH HỌC
============================== */
if (isset($_POST['add_schedule'])) {
    $course_id = $_POST['course_id'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];

    $sql = "INSERT INTO schedules (course_id, schedule_date, start_time, end_time, location)
            VALUES ($course_id, '$schedule_date', '$start_time', '$end_time', '$location')";
    $conn->query($sql);

    header("Location: ../views/schedules/list_schedules.php");
    exit();
}

/* ==============================
   CẬP NHẬT LỊCH HỌC
============================== */
if (isset($_POST['update_schedule'])) {
    $id = $_POST['id'];
    $course_id = $_POST['course_id'];
    $schedule_date = $_POST['schedule_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = $_POST['location'];

    $sql = "UPDATE schedules 
            SET course_id=$course_id, schedule_date='$schedule_date', start_time='$start_time', end_time='$end_time', location='$location' 
            WHERE id=$id";
    $conn->query($sql);

    header("Location: ../views/schedules/list_schedules.php");
    exit();
}

/* ==============================
   XÓA LỊCH HỌC
============================== */
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $conn->query("DELETE FROM schedules WHERE id=$id");

    header("Location: ../views/schedules/list_schedules.php");
    exit();
}
?>
