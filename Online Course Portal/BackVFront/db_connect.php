<?php
// Database connection parameters
$servername = "localhost";  // Since you're using XAMPP, localhost is default
$username = "root";         // Default MySQL username in XAMPP
$password = "root1234";             // Leave empty if no password is set
$database = "ocp_ocp";  // Replace with your actual database name

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    //echo "Connected successfully"; // Uncomment for testing
}
?>
