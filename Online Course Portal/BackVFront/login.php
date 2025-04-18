<?php
session_start(); // Start the session to use session variables
include 'db_connect.php'; // Ensure you have a database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input values and sanitize them
    $email_or_username = trim($_POST['email_or_username']);
    $password = $_POST['password'];

    // Prepare SQL statement to select the user
    $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("ss", $email_or_username, $email_or_username);
    $stmt->execute();
    
    $result = $stmt->get_result();
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Store user information in session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            header("Location: homepg.php"); // Redirect to homepage
            exit();
        } else {
            echo "Incorrect password!";
        }
    } else {
        echo "User  not found!";
    }

    $stmt->close(); // Close the statement
    $conn->close(); // Close the database connection
}
?>