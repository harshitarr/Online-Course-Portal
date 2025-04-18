<?php
require 'db_connect.php'; // Ensure you have a database connection file

header("Content-Type: application/json");

// Get the JSON input
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$fullName = $data['fullName'];
$email = $data['email'];
$phone = $data['phone'];
$dob = $data['dob'];
$gender = $data['gender'];
$course = $data['course'];
$enrollmentDate = date("Y-m-d");
$username = $data['username'];
$password = password_hash($data['password'], PASSWORD_BCRYPT); // Secure password
$paymentMethod = $data['paymentMethod'];
$amount = $data['fee'];
$paymentDate = date("Y-m-d");

// Check if the user already exists
$sql = "SELECT user_id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    // Insert new user
    $sql = "INSERT INTO users (full_name, email, phone, dob, gender, username, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $fullName, $email, $phone, $dob, $gender, $username, $password);
    if (!$stmt->execute()) {
        echo json_encode(["success" => false, "message" => "User creation failed."]);
        exit;
    }
    $user_id = $stmt->insert_id;
} else {
    $row = $result->fetch_assoc();
    $user_id = $row['user_id'];
}

// Get course ID
$sql = "SELECT course_id FROM courses WHERE course_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success" => false, "message" => "Course not found."]);
    exit;
}

$row = $result->fetch_assoc();
$course_id = $row['course_id'];

// Insert into enrollments table
$sql = "INSERT INTO enrollments (user_id, course_id, enrollment_date, progress, status) 
        VALUES (?, ?, ?, 0, 'active')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $course_id, $enrollmentDate);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Enrollment failed."]);
    exit;
}
$enrollment_id = $stmt->insert_id;

// Insert into payments table
$sql = "INSERT INTO payments (user_id, course_id, amount, payment_date, payment_status) 
        VALUES (?, ?, ?, ?, 'completed')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $user_id, $course_id, $amount, $paymentDate);
if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Payment processing failed."]);
    exit;
}

// Success response
echo json_encode(["success" => true, "message" => "Enrollment and payment successful!"]);
?>
