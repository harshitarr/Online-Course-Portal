<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

if (isset($_GET['user_id']) && isset($_GET['course_id'])) {
    $user_id = $_GET['user_id'];
    $course_id = $_GET['course_id'];

    // Check if the user is enrolled in the course
    $enrollment_check_sql = "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?";
    $enrollment_stmt = $conn->prepare($enrollment_check_sql);
    $enrollment_stmt->bind_param("ii", $user_id, $course_id);
    $enrollment_stmt->execute();
    $enrollment_stmt->bind_result($is_enrolled);
    $enrollment_stmt->fetch();
    $enrollment_stmt->close();

    // Return the result as JSON
    echo json_encode(['is_enrolled' => $is_enrolled > 0]);
} else {
    echo json_encode(['is_enrolled' => false]);
}
?>