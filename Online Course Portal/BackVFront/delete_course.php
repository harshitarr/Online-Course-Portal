<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

$course_id = $_GET['id'];

// Delete the course
$query = "DELETE FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);

if ($stmt->execute()) {
    // Add notification
    $notification_query = "INSERT INTO notifications (user_id, message, notification_status) VALUES (?, ?, ?)";
    $notification_stmt = $conn->prepare($notification_query);
    $message = "Course with ID '$course_id' deleted successfully.";
    $status = "unread"; // or "read" based on your logic
    $notification_stmt->bind_param("iss", $_SESSION['user_id'], $message, $status);
    $notification_stmt->execute();
    $notification_stmt->close();

    echo "<script>alert('Course deleted successfully!'); window.location.href = 'manage_courses.php';</script>";
} else {
    echo "<p>Error deleting course: " . $stmt->error . "</p>";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Course</title>
    <script>
        // Show the alert message when the page loads
        window.onload = function() {
            alert("<?php echo addslashes($message); ?>");
            window.location.href = "manage_courses.php"; // Redirect to manage courses after the alert
        };
    </script>
</head>
<body>
    <!-- Optionally, you can include a message here for users with JavaScript disabled -->
    <p>If you see this message, your course has been deleted.</p>
</body>
</html>