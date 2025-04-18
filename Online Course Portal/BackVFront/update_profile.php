<?php
session_start();
include 'db_connect.php'; // Database connection file

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$userType = trim($_POST['userType']);

// Validate inputs
if (empty($name) || empty($email) || empty($userType)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}

// Update user details in the database
$sql = "UPDATE users SET username = ?, email = ?, user_type = ? WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $email, $userType, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Database update failed"]);
}

$stmt->close();
$conn->close();
?>
