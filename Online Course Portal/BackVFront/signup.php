<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Debugging: Check the received POST data
    var_dump($_POST); // This will show you all the data received from the form

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $user_type = trim($_POST['user_type']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);

    // Debugging: Check the values before inserting into the database
    echo "Username: " . htmlspecialchars($username) . "<br>";
    echo "Email: " . htmlspecialchars($email) . "<br>";
    echo "Phone Number: " . htmlspecialchars($phone_number) . "<br>";
    echo "User  Type: " . htmlspecialchars($user_type) . "<br>";

    // Prepare SQL statement to insert data
    $sql = "INSERT INTO users (username, email, phone_number, user_type, password, registration_date) 
            VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("sssss", $username, $email, $phone_number, $user_type, $password);

    if ($stmt->execute()) {
        $_SESSION['username'] = $username; // Store the username in the session
        header("Location: homepg.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>