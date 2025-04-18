<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the required data is sent via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $course_id = $_POST['course_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $payment_status = 'completed'; // Assuming payment is successful

    // Insert into payments table
    $sql_payment = "INSERT INTO payments (user_id, course_id, amount, payment_date, payment_status) VALUES (?, ?, ?, CURDATE(), ?)";
    $stmt_payment = $conn->prepare($sql_payment);
    $stmt_payment->bind_param("iids", $user_id, $course_id, $amount, $payment_status);

    if ($stmt_payment->execute()) {
        // Get the last inserted payment ID
        $payment_id = $stmt_payment->insert_id;

        // Insert into enrollments table
        $sql_enrollment = "INSERT INTO enrollments (user_id, course_id, enrollment_date, progress, status) VALUES (?, ?, CURDATE(), 0, 'active')";
        $stmt_enrollment = $conn->prepare($sql_enrollment);
        $stmt_enrollment->bind_param("ii", $user_id, $course_id);

        if ($stmt_enrollment->execute()) {
            // Enrollment successful
            echo json_encode(['status' => 'success', 'message' => 'Enrollment successful!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to enroll in the course.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Payment processing failed.']);
    }

    $stmt_payment->close();
    $stmt_enrollment->close();
    $conn->close();
}
?>