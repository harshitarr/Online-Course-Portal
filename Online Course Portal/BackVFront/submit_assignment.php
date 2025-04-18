<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Check if assignment_id and course are passed in the URL
if (!isset($_GET['assignment_id']) || !isset($_GET['course'])) {
    echo "Assignment ID or Course not specified.";
    exit();
}

$assignment_id = $_GET['assignment_id']; // Get the assignment ID from the URL
$course_name = $_GET['course']; // Get the course name from the URL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id']; // Get the logged-in user's ID

    // Check if file_path is set in the POST request
    if (isset($_POST['file_path']) && !empty($_POST['file_path'])) {
        $file_path = $_POST['file_path']; // Get the file path from the input

        // Insert into submissions table
        $sql = "INSERT INTO submissions (assignment_id, user_id, submission_date, file_path) VALUES (?, ?, CURDATE(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $assignment_id, $user_id, $file_path);
        
        if ($stmt->execute()) {
            echo "Submission successful!";
            // Redirect back to the course page with the course name
            header("Location: course_details.php?course=" . urlencode($course_name));
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "File path cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Assignment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #0c0f1a;
            color: white;
            text-align: center;
        }
        header {
            background: #11152b;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #f4a51c;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin: 0 15px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #f4a51c;
        }

        h1 {
            margin-top: 30px;
        }

        form {
            margin: 40px auto;
            padding: 40px;
            background: #1c2140;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.1);
            max-width: 500px;
            display: flex;
            flex-direction: column;
            gap: 15px; /* Ensures spacing between fields */
        }

        label {
            display: block;
            text-align: left;
            font-weight: bold;
            margin-top: 10px;
        }

        input, textarea, select {
            width: calc(100% - 20px);
            padding: 12px;
            margin: 5px 0;
            border-radius: 5px;
            border: none;
            display: block;
            background: #29304d;
            color: white;
            outline: none;
            font-size: 16px;
        }

        input:focus, textarea:focus, select:focus {
            border: 2px solid #f4a51c;
        }

        input[type="submit"] {
            background-color: #f4a51c;
            color: #11152b;
            cursor: pointer;
            font-weight: bold;
            padding: 15px;
            margin-top: 20px;
            transition: background 0.3s, transform 0.2s;
        }

        input[type="submit"]:hover {
            background-color: #d4931a;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Pro-Skills</div>
        <nav>
            <ul>
                <li><a href="homepg.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="instructor_courses.php">My Courses</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <h1>Submit Assignment</h1>
    <form method="POST" action="">
        <label for="file_path">Enter File Path:</label>
        <input type="text" name="file_path" placeholder="Enter the path to your file" required>

        <input type="submit" name="submit" value="Submit Assignment">
    </form>
</body>
</html>

<?php
$conn->close();
?>