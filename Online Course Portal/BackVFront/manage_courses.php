<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

$query = "SELECT course_id, course_name, description FROM courses";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #0c0f1a;
            color: black;
            text-align: center;
        }
        header {
            background: #11152b;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #f4a51c;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin: 0 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav ul li a:hover {
            color: #f4a51c;
        }
        .course-container {
            max-width: 900px;
            margin: 40px auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .course {
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(255, 255, 255, 0.1);
            text-align: left;
            font-size: 18px;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .course:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }
        .course:nth-child(4n+1) { background: #f46f6f; }
        .course:nth-child(4n+2) { background: #fcd94d; }
        .course:nth-child(4n+3) { background: rgb(186, 237, 110); }
        .course:nth-child(4n+4) { background: #6fa1f3; }
        
        .course h2 {
            margin-bottom: 15px;
        }
        .course p {
            margin-bottom: 20px;
        }
        .actions {
            display: flex;
            gap: 15px;
        }
        .btn {
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.2s, background 0.3s;
        }
        .btn-edit {
            background: #ffcc00;
            color: black;
            border: 2px black solid;
        }
        .btn-delete {
            background: #ff4444;
            color: black;
            border: 2px black solid;
        }
        .btn:hover {
            transform: translateY(-3px);
            opacity: 0.9;
        }
        .btn-edit:hover {
            background: #e6b800;
        }
        .btn-delete:hover {
            background: #cc0000;
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
                <li><a href="manage_courses.php">Manage Courses</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <h1>Manage Courses</h1>
    
    <div class="course-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($course = $result->fetch_assoc()): ?>
                <div class="course">
                    <h2><?php echo htmlspecialchars($course['course_name']); ?></h2>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="actions">
                        <a href="edit_course.php?id=<?php echo $course['course_id']; ?>">
                            <button class="btn btn-edit">Edit</button>
                        </a>
                        <a href="delete_course.php?id=<?php echo $course['course_id']; ?>" onclick="return confirm ('Are you sure you want to delete this course?');">
                            <button class="btn btn-delete">Delete</button>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No courses found.</p>
        <?php endif; ?>
    </div>

    <?php
    $conn->close();
    ?>
</body>
</html>