<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'instructor') {
    header("Location: login.php"); // Redirect to login if not logged in or not an instructor
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];
    $max_marks = $_POST['max_marks'];

    // Insert the new assignment into the database
    $sql = "INSERT INTO assignments (course_id, title, description, due_date, max_marks) VALUES (?, ?, ?, ?, ?)";
    
    if ($stmt = $conn->prepare($sql)) {  // Check if preparation is successful
        $stmt->bind_param("isssi", $course_id, $title, $description, $due_date, $max_marks);

        if ($stmt->execute()) {
            echo "Assignment added successfully!";
            // Redirect back to the course details page
            header("Location: course_details.php?course=" . urlencode($course_id));
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close(); // Close statement only if it was successfully prepared
    } else {
        echo "Error: " . $conn->error; // Handle statement preparation error
    }
}

$conn->close(); // Ensure connection is closed at the end
?>
