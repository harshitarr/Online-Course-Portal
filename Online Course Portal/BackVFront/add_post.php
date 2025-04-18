<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the post content and forum ID from the form
    $post_content = isset($_POST['post_content']) ? trim($_POST['post_content']) : '';
    $forum_id = isset($_POST['forum_id']) ? intval($_POST['forum_id']) : 0;
    $user_id = $_SESSION['user_id']; // Assuming you have the user ID stored in the session

    // Validate input
    if (empty($post_content) || $forum_id <= 0) {
        echo "Invalid input!";
        exit();
    }

    // Insert the new post into the forum_posts table
    $sql = "INSERT INTO forum_posts (forum_id, user_id, post_content, created_date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $forum_id, $user_id, $post_content);

    if ($stmt->execute()) {
        // Redirect back to the forum posts page after successful insertion
        header("Location: forum_posts.php?forum_id=" . $forum_id);
        exit();
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>