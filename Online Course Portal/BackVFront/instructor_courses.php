<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT course_id, course_name, description 
          FROM courses 
          WHERE instructor_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Courses</title>
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
        }

        .courses-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .course {
            padding: 10px 20px;
            margin: 10px auto;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            width: 70%; /* Increased width */
            color: white;
            font-weight: bold;
            text-align: center;
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
        }

        /* Color pattern */
        .course:nth-child(4n+1) { background: #ef4444; } /* Red */
        .course:nth-child(4n+2) { background: #f4a51c; } /* Yellow */
        .course:nth-child(4n+3) { background: #10b981; } /* Green */
        .course:nth-child(4n+4) { background: #3b82f6; } /* Blue */

        /* Hover Effect: Zoom Out */
        .course:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        h1 {
            color: #f4a51c;
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

    <h1>Your Courses</h1>
    <div class="courses-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($course = $result->fetch_assoc()): ?>
                <div class="course">
                    <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>You are not teaching any courses.</p>
        <?php endif; ?>
    </div>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>