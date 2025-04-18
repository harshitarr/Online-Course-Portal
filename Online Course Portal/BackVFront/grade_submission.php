<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'instructor') {
    header("Location: login.php"); // Redirect to login if not logged in or not an instructor
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_id = $_POST['submission_id'];
    $marks_awarded = $_POST['marks_awarded'];

    // Update the marks awarded for the submission
    $sql = "UPDATE submissions SET marks_awarded = ? WHERE submission_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $marks_awarded, $submission_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Marks awarded successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>