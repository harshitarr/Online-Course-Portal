<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Modify the query to include course status
$query = "SELECT c.course_id, c.course_name, c.description, e.status 
          FROM courses c 
          JOIN enrollments e ON c.course_id = e.course_id 
          WHERE e.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize counters and arrays for course statuses
$dropped_count = 0;
$active_count = 0;
$completed_count = 0;

$active_courses = [];
$completed_courses = [];
$dropped_courses = [];

// Count the statuses and store course details
while ($course = $result->fetch_assoc()) {
    switch ($course['status']) {
        case 'dropped':
            $dropped_count++;
            $dropped_courses[] = $course; // Store dropped course details
            break;
        case 'active':
            $active_count++;
            $active_courses[] = $course; // Store active course details
            break;
        case 'completed':
            $completed_count++;
            $completed_courses[] = $course; // Store completed course details
            break;
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Enrolled Courses</title>
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
        .status-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            margin-top: 50px;
            padding: 20px;
        }
        .status-box {
            width: 300px;
            height: 300px;
            border-radius: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.2);
            /*position: relative;*/
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }
        .status-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(255, 255, 255, 0.3);
        }
        .dropped {
            background: rgba(239, 68, 68, 0.9);
        }
        .active {
            background: rgba(244, 165, 28, 0.9);
            color: black;
        }
        .completed {
            background: rgba(34, 197, 94, 0.9);
        }
        .course-details {
            background: #1c2140;
            color: white;
            padding: 10px;
            border-radius: 5px;
            width: 250px; /* Adjust width as needed */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            display: none; /* Initially hidden */
            margin-top: 10px; /* Space between status box and course details */
            /* Remove position: absolute or relative */
        }
    </style>
    <script>
    function showCourses(status) {
        const activeCourses = document.getElementById("active-courses");
        const droppedCourses = document.getElementById("dropped-courses");
        const completedCourses = document.getElementById("completed-courses");

        // Hide all course details
        activeCourses.style.display = "none";
        droppedCourses.style.display = "none";
        completedCourses.style.display = "none";

        // Show the selected course details based on status
        if (status === 'active') {
            activeCourses.style.display = "block";
        } else if (status === 'completed') {
            completedCourses.style.display = "block";
        } else if (status === 'dropped') {
            droppedCourses.style.display = "block";
        }
    }

    function hideCourses() {
        const activeCourses = document.getElementById("active-courses");
        const droppedCourses = document.getElementById("dropped-courses");
        const completedCourses = document.getElementById("completed-courses");

        activeCourses.style.display = "none";
        droppedCourses.style.display = "none";
        completedCourses.style.display = "none";
    }
</script>
</head>
<body>
    <header>
        <div class="logo">Pro-Skills</div>
        <nav>
            <ul>
                <li><a href="homepg.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="student_courses.php">My Courses</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <h1>Your Enrolled Courses</h1>

    <!-- STATUS BOXES -->
    <div class="status-container">
        <div class="status-box active" onclick="showCourses('active')" onmouseout="hideCourses()">
            <p>Active</p>
            <p class="count"><?php echo $active_count; ?></p>
        </div>
        <div class="status-box completed" onclick="showCourses('completed')" onmouseout="hideCourses()">
            <p>Completed</p>
            <p class="count"><?php echo $completed_count; ?></p>
        </div>
        <div class="status-box dropped" onclick="showCourses('dropped')" onmouseout="hideCourses()">
            <p>Dropped</p>
            <p class="count"><?php echo $dropped_count; ?></p>
        </div>

        <div class="course-details" id="active-courses" style="display: none;">
            <ul>
                <?php foreach ($active_courses as $course): ?>
                    <li>
                        <strong>
                            <a href="course_details.php?course_id=<?php echo $course['course_id']; ?>" style="color: white; text-decoration: none;">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </a>
                        </strong><br>
                        <strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="course-details" id="completed-courses" style="display: none;">
            <ul>
                <?php foreach ($completed_courses as $course): ?>
                    <li>
                        <strong>
                            <a href="course_details.php?course_id=<?php echo $course['course_id']; ?>" style="color: white; text-decoration: none;">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </a>
                        </strong><br>
                        <strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="course-details" id="dropped-courses" style="display: none;">
            <ul>
                <?php foreach ($dropped_courses as $course): ?>
                    <li>
                        <strong>
                            <a href="course_details.php?course_id=<?php echo $course['course_id']; ?>" style="color: white; text-decoration: none;">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </a>
                        </strong><br>
                        <strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php if ($active_count + $completed_count + $dropped_count > 0): ?>
        <div class="course-list">
            <?php
            // No need to execute the statement again
            // The course details are already fetched and stored in arrays
            ?>
        </div>
    <?php else: ?>
        <p>You are not enrolled in any courses.</p>
    <?php endif; ?>
</body>
</html>