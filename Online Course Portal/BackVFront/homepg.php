<?php
session_start();
// Database connection
include 'db_connect.php'; // Ensure you have a database connection
if (!isset($_SESSION['user_id'])) {
    header("Location: indexpg.html"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch notifications
$notifications = [];
$unread_count = 0;

$query = "SELECT message, created_date, notification_status FROM notifications WHERE user_id = ? ORDER BY created_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($notification = $result->fetch_assoc()) {
    $notifications[] = $notification;
    // Check if notification_status exists before accessing it
    if (isset($notification['notification_status']) && $notification['notification_status'] == 'unread') {
        $unread_count++;
    }
}
// Get username and set default to 'Guest' if not logged in
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest'; 

// Fetch user role from the database
$user_type = 'guest'; // Default role
if (isset($_SESSION['user_id'])) { // Assuming user_id is stored in session
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT user_type FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_type = $row['user_type'];
    }
}

// Fetch popular courses based on enrollments
$popular_courses = [];
$sql = "
    SELECT c.course_name, c.description, c.price, c.duration, c.image, COUNT(e.course_id) AS enrollment_count
    FROM courses c
    LEFT JOIN enrollments e ON c.course_id = e.course_id
    GROUP BY c.course_id
    ORDER BY enrollment_count DESC
    LIMIT 3"; // Adjust the limit as needed

$result = $conn->query($sql);

if ($result) {
    while ($course = $result->fetch_assoc()) {
        $popular_courses[] = [
            'name' => htmlspecialchars($course['course_name']),
            'description' => htmlspecialchars($course['description']),
            'price' => htmlspecialchars($course['price']),
            'duration' => htmlspecialchars($course['duration']),
            'image' => htmlspecialchars($course['image']),
            'enrollment_count' => $course['enrollment_count'], // Optional: include enrollment count if needed
        ];
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pro-Skills</title>
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
        .hero {
            padding: 80px 20px;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.1)), url('images/banner.png') no-repeat center center/cover; /* Add a background image */
            color: white;
        }
        .hero-content {
            max-width: 800px; /* Limit the width of the content */
            margin: 0 auto; /* Center the content */
            text-align: center; /* Center the text */
        }
        .hero h1 {
            font-size: 48px; /* Increased font size */
            font-weight: bold;
            margin-bottom: 20px; /* Space below the heading */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Text shadow for depth */
        }
        .hero input {
            padding: 10px;
            width: 60%; /* Increased width */
            font-size: 16px;
            border-radius: 5px;
            border: none;
            outline: none;
            margin-bottom: 20px; /* Space below the input */
        }
        .cta-button {
            display: inline-block; /* Make it a block element */
            padding: 10px 20px; /* Padding for the button */
            background-color: #f4a51c; /* Button color */
            color: #11152b; /* Text color */
            text-decoration: none; /* Remove underline */
            font-weight: bold; /* Bold text */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s ease; /* Transition for hover effect */
        }
        .cta-button:hover {
            background-color: #d4931a; /* Darker shade on hover */
        }
        .courses {
            padding: 50px;
        }
        .course-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .course {         
            background: #1c2140;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 400px; /* Increased width */
            height: 400px; /* Increased height */
            border-radius: 15px;
            border: 2px solid #f4a51c; /* Yellow border */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;/* Added transition */
        }
        .course:hover {
            transform: scale(1.05); /* Scale effect */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); /* Enhanced shadow on hover */
            background: #2a2e4b; /* Slightly lighter background on hover */
        }
        .course img {
            border-radius: 15px;
            width: 100%; /* Ensure image fits the box */
            height: auto; /* Maintain aspect ratio */
            transition: none;
        }
        .course h3 {
            color: #f4a51c;
            margin: 10px 0; /* Added margin for spacing */
        }
        .course p {
            margin: 0; /* Reset margin for paragraph */
        }
        .other-courses {
            padding: 50px;
            background: #11152b;
        }
        .other-course-list {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 30px;
        }
        .other-course {
            background: #1c2140;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 400px; /* Increased width */
            height: 400px; /* Increased height */
            border-radius: 15px;
            border: 2px solid #f4a51c; /* Yellow border */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease; /* Added transition */
        }
        .other-course:hover {
            transform: scale(1.05); /* Zoom effect */
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5); /* Enhanced shadow on hover */
        }
        .other-course img {
            border-radius: 15px;
            width: 100%; /* Ensure image fits the box */
            height: auto; /* Maintain aspect ratio */
            transition: none; /* No transition on image hover */
        }
        .other-course h3 {
            color: #f4a51c;
            font-size: 24px; /* Increased font size for course title */
            margin: 10px 0; /* Margin for spacing */
        }
        .other-course p {
            font-size: 18px; /* Increased font size for course description */
            color: white; /* Ensure text is visible */
            margin-top: 10px; /* Added margin for spacing */
        }
        footer {
            background: #11152b;
            color: white;
            padding: 10px;
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
        .course-box {
            transition: transform 0.3s ease-in-out;
        }
        .course-box:hover {
            transform: scale(1.05);
        }
        /* New styles for headings */
        h2 {
            font-size: 36px; /* Increased font size */
            color: #f4a51c; /* Yellow color */
            margin: 20px 0; /* Margin for spacing */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Text shadow for depth */
            font-weight: bold; /* Bold text */
            padding: 25px;
        }
        h3 {
            font-size: 25px;
            color: #f4a51c;
            padding-top: 16px;
        }
        p {
            font-size: 17px;
            color: white;
            padding: 6px;
        }
        .profile-container {
            position: relative;
            display: inline-block;
        }

        .profile-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f4a51c;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #11152b;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .profile-icon:hover {
            background: #d4931a;
        }

        .profile-dropdown {
            position: absolute;
            top: 20px;
            right: 0;
            background: #1c2140;
            border: 2px solid #f4a51c;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: none;
            flex-direction: column;
            min-width: 150px;
            text-align: left;
            z-index: 1000;
        }

        .profile-dropdown a {
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            display: block;
            transition: background 0.3s ease;
        }

        .profile-dropdown a:hover {
            background: #2a2e4b;
            color: #f4a51c;
            border-radius: 10px;
        }

        /* Show Dropdown on Click */
        .profile-container:hover .profile-dropdown {
            display: flex;
        }

        a {
            color: inherit; /* Keeps text color normal */
            text-decoration: none; /* Removes underline */
        }
        .notification-icon {
            position: relative;
            cursor: pointer;
        }

        .notification-dropdown {
            display: none; /* Hidden by default */
            position: absolute;
            background: #1c2140;
            border: 1px solid #f4a51c;
            border-radius: 5px;
            z-index: 1000;
            right: 0;
            width: 250px; /* Adjust width as needed */
        }

        .notification-dropdown .notification-item {
            padding: 10px;
            border-bottom: 1px solid #f4a51c;
        }

        .notification-dropdown .notification-item:last-child {
            border-bottom: none; /* Remove border for last item */
        }

        .notification-dropdown p {
            margin: 0;
            color: white;
        }

        .notification-dropdown small {
            color: #ccc; /* Lighter color for date */
        }

    </style>
</head>
<body>
    <header>
        <div class="logo">Pr <span style="color: #f4a51c;">Pro-Skills</span></div>
        <nav>
            <ul>
                <li><a href="homepg.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <?php if ($user_type == 'student'): ?>
                    <li><a href="student_courses.php">My Courses</a></li> <!-- Student's courses -->
                <?php elseif ($user_type == 'instructor'): ?>
                    <li><a href="instructor_courses.php">My Courses</a></li> <!-- Instructor's courses -->
                    <li><a href="add_course.php">Add Course</a></li> <!-- Option to add courses -->
                <?php elseif ($user_type == 'admin'): ?>
                    <li><a href="manage_courses.php">Manage Courses</a></li> <!-- Admin management -->
                <?php endif; ?>
                <li><a href="forums.php">Forums</a></li>
                <li>
                    <div class="notification-icon" onclick="toggleNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="badge"><?php echo $unread_count; ?></span> <!-- Display unread count -->
                    </div>
                    <div class="notification-dropdown" id="notificationDropdown">
                        <?php if ($notifications): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-item">
                                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                    <small><?php echo htmlspecialchars($notification['created_date']); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No notifications found.</p>
                        <?php endif; ?>
                    </div>
                </li>
            </ul>
        </nav>

        <div class="profile-container">
            <span class="username-display"><?php echo htmlspecialchars($username); ?></span>
        
            <!-- Dropdown Menu -->
            <div class="profile-dropdown" id="profile-menu">
                <a href="profile.php" id="view-profile">View Profile</a>
                <a href="logout.php">Logout</a>
            </div>
    </header>
    
    <section class="hero">
        <div class="hero-content">
            <h1>
                Learn a <span style="color: #f4a51c; text-decoration: underline;">New Skill</span> 
                <br> 
                Everyday, Anytime and Anywhere.
            </h1>
            <input type="text" id="searchBox" placeholder="Search for courses...">
            <a href="#" class="cta-button">Explore Courses</a>
        </div>
    </section>

    
    <section class="courses">
    <h2>Popular Courses</h2>
        <div class="course-list">
            <?php if (count($popular_courses) > 0): ?>
                <?php foreach ($popular_courses as $course): ?>
                    <div class="course" data-name="<?php echo $course['name']; ?>">
                        <a href="course_details.php?course=<?php echo urlencode($course['name']); ?>">
                            <img src="<?php echo $course['image']; ?>" alt="<?php echo $course['name']; ?>">
                            <h3><?php echo $course['name']; ?></h3>
                            <p><?php echo $course['description']; ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No popular courses found.</p>
            <?php endif; ?>
        </div>
    </section>

    <section class="other-courses">
        <h2>Explore Other Courses</h2>
        <div class="other-course-list">
    <div class="other-course" data-name="Computer Science">
        <a href="course_categories.php?category=Computer%20Science">
            <img src="images/1.png" alt="Computer Science">
            <h3>Computer Science</h3>
            <p>Learn the fundamentals of computer science.</p>
        </a>
    </div>
    <div class="other-course" data-name="Data Science & AI">
        <a href="course_categories.php?category=Data%20Science%20%26%20AI">
            <img src="images/2.jpg" alt="Data Science">
            <h3>Data Science & AI</h3>
            <p>Explore data analysis and machine learning.</p>
        </a>
    </div>
    <div class="other-course" data-name="Web & Mobile Development">
        <a href="course_categories.php?category=Web%20%26%20Mobile%20Development">
            <img src="images/3.png" alt="Web Dev">
            <h3>Web & Mobile Dev</h3>
            <p>Build applications for web and mobile platforms.</p>
        </a>
    </div>
    <div class="other-course" data-name="Cybersecurity & Cloud Computing">
        <a href="course_categories.php?category=Cybersecurity%20%26%20Cloud%20Computing">
            <img src="images/4.jpeg" alt="Cybersecurity">
            <h3>Cybersecurity</h3>
            <p>Protect systems and networks from cyber threats.</p>
        </a>
    </div>
    <div class="other-course" data-name="Marketing & E-Commerce">
        <a href="course_categories.php?category=Marketing%20%26%20E%2DCommerce">
            <img src="images/5.webp" alt="Marketing">
            <h3>Marketing</h3>
            <p>Learn strategies for effective marketing.</p>
        </a>
    </div>
    <div class="other-course" data-name="Design & Art">
        <a href="course_categories.php?category=Design%20%26%20Art">
            <img src="images/6.jpg" alt="Design & Art">
            <h3>Design & Art</h3>
            <p>Explore creativity in design and art.</p>
        </a>
    </div>
    <div class="other-course" data-name="Finance & Business">
        <a href="course_categories.php?category=Finance%20%26%20Business">
            <img src="images/7.jpg" alt="Finance">
            <h3>Finance & Business</h3>
            <p>Understand financial principles and business strategies.</p>
        </a>
    </div>
    <div class="other-course" data-name="Management & Project Planning">
        <a href="course_categories.php?category=Management%20%26%20Project%20Planning">
            <img src="images/8.jpg" alt="Management">
            <h3>Management</h3>
            <p>Learn effective management techniques.</p>
        </a>
    </div>
    <div class="other-course" data-name="Writing & Communication">
        <a href="course_categories.php?category=Writing%20%26%20Communication">
            <img src="images/9.webp" alt="Writing">
            <h3>Writing & Communication</h3>
            <p>Enhance your writing and communication skills.</p>
        </a>
    </div>
    <div class="other-course" data-name="Game & Robotics Development">
        <a href="course_categories.php?category=Game%20%26%20Robotics%20Development">
            <img src="images/10.jpeg" alt="Gaming">
            <h3>Game & Robotics Dev</h3>
            <p>Develop games and robotics applications.</p>
        </a>
    </div>
    <div class="other-course" data-name="Database & SQL">
        <a href="course_categories.php?category=Database%20%26%20SQL">
            <img src="images/11.png" alt="Database">
            <h3>Database & SQL</h3>
            <p>Learn database management and SQL .</p>
        </a>
    </div>
    <div class="other-course" data-name="Blockchain">
        <a href="course_categories.php?category=Blockchain">
            <img src="images/12.webp" alt="Blockchain">
            <h3>Blockchain</h3>
            <p>Understand blockchain technology and its applications.</p>
        </a>
    </div>
    <div class="other-course" data-name="Economics & Political Science">
        <a href="course_categories.php?category=Economics%20%26%20Political%20Science">
            <img src="images/13.jpg" alt="Economics">
            <h3>Economics & Politics</h3>
            <p>Explore the intersection of economics and politics.</p>
        </a>
    </div>
    <div class="other-course" data-name="Science & Environment">
        <a href="course_categories.php?category=Science%20%26%20Environment">
            <img src="images/14.jpeg" alt="Science">
            <h3>Science & Environment</h3>
            <p>Learn about environmental science and sustainability.</p>
        </a>
    </div>
    <div class="other-course" data-name="Social Sciences & Humanities">
        <a href="course_categories.php?category=Social%20Sciences%20%26%20Humanities">
            <img src="images/15.gif" alt="Social Sciences">
            <h3>Social Sciences</h3>
            <p>Study human behavior and societies.</p>
        </a>
    </div>
    <div class="other-course" data-name="Music">
        <a href="course_categories.php?category=Music">
            <img src="images/16.jpeg" alt="Music">
            <h3>Music</h3>
            <p>Explore music theory and composition.</p>
        </a>
    </div>
</div> 
        </div>        
    </section>
    
    <footer>
        <p>&copy; 2025 Pro-Skills. All rights reserved.</p>
    </footer>
    
    <script>
        document.getElementById("searchBox").addEventListener("keyup", function () {
            let filter = this.value.toLowerCase();
            let courses = document.querySelectorAll(".course, .other-course");

            courses.forEach(course => {
                let name = course.dataset.name.toLowerCase();
                course.classList.toggle("hidden", !name.includes(filter));
            });
        });
        document.getElementById("profile-btn").addEventListener("click", function() {
            let menu = document.getElementById("profile-menu");
            menu.style.display = menu.style.display === "block" ? "none" : "block";
        });

        function toggleNotifications() {
            const dropdown = document.getElementById("notificationDropdown");
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
        }

// Close the dropdown if clicked outside
        window.onclick = function(event) {
            if (!event.target.matches('.notification-icon')) {
                const dropdowns = document.getElementsByClassName("notification-dropdown");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }

    </script>
</body>
</html>