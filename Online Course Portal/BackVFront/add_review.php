<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the review data from the form
    $rating = intval($_POST['rating']);
    $review_text = trim($_POST['review_text']);
    $course_id = intval($_POST['course_id']); // Ensure this is being retrieved correctly
    $user_id = $_SESSION['user_id']; // Assuming you have the user ID stored in the session
    $course_name = $_POST['course_name'];

    // Validate input
    if ($rating < 1 || $rating > 5 || empty($review_text) || $course_id <= 0) {
        echo "Invalid input!";
        exit();
    }

    // Insert the new review into the reviews table
    $sql = "INSERT INTO reviews (user_id, course_id, rating, review_text, review_date) VALUES (?, ?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiis", $user_id, $course_id, $rating, $review_text);

    if ($stmt->execute()) {
        // Redirect back to the course details page after successful insertion
        header("Location: course_details.php?course=" . urlencode($course_name)); // Ensure $course_name is defined
        exit();
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>