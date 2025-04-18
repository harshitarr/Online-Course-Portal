<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the category_name is set in the URL
if (!isset($_GET['category'])) {
    echo "Category not specified!";
    exit();
}

$category_name = $_GET['category'];

// Fetch courses for the selected category
$courses = [];
$sql = "SELECT course_name, description, price, duration, image FROM courses WHERE category = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    $courses_result = $stmt->get_result();
    while ($course = $courses_result->fetch_assoc()) {
        $courses[] = [
            'name' => htmlspecialchars($course['course_name']),
            'description' => htmlspecialchars($course['description']),
            'price' => htmlspecialchars($course['price']),
            'duration' => htmlspecialchars($course['duration']),
            'image' => htmlspecialchars($course['image']),
        ];
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_name); ?> Courses</title>
    <style>
        /* Add your CSS styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #0c0f1a;
            color: white;
            text-align: center;
        }
        h1 {
            color: #f4a51c;
            margin: 20px 0;
        }
        .course-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .course-item {
            background: #1c2140;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .course-item h3 {
            color: #f4a51c;
        }
        .course-item img {
            max-width: 100%; /* Ensures the image does not exceed the width of the container */
            height: auto; /* Maintains the aspect ratio */
            border-radius: 10px; /* Optional: for rounded corners */
        }
        .enroll-button {
            background-color: #f4a51c;
            color: #11152b;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .enroll-button:hover {
            background-color: #d4931a;
        }
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
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
            width: 350px; /* Increased width */
            height: 350px; /* Increased height */
            border-radius: 10px;
            text-align: left;
            outline: 5px solid #f4a51c; /* Yellow outline */
            margin: 10px; /* Margin for spacing */
            transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease; /* Added transition */
        }
        .course:hover {
            transform: scale(1.05); /* Scale effect */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5); /* Enhanced shadow on hover */
            background: #2a2e4b; /* Slightly lighter background on hover */
        }
        .course img {
            width: 100%;
            height: auto;
            border-radius: 10px;
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

        .course-item {
            background: #1c2140;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 400px; /* Increased width */
            height: 450px; /* Increased height */
            border-radius: 15px;
            border: 2px solid #f4a51c; /* Yellow border */
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .course-item img {
            width: 100%;  /* Make the image take full width */
            height: 150px; /* Fixed height */
            object-fit: cover; /* Crop/fit the image inside */
            border-radius: 10px;
        }

        .course-item h3 {
            margin: 10px 0;
        }

        .course-item p {
            flex-grow: 1; /* Ensures the description takes remaining space */
            /*overflow: hidden;
            text-overflow: ellipsis;*/
            font-size: 18px; /* Increased font size for course description */
            color: white; /* Ensure text is visible */
            margin-top: 10px;
        }

        .enroll-button {
            background: #f4a51c;
            color: black;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.3s ease;
        }

        .enroll-button:hover {
            background: #d48b19;
        }


        .course-list {
            margin-top: 30px; /* Space above the entire list */
        }



        /* Add hover effect */
        .course-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
        }

        .course-item img {
            width: 370px; /* Set a fixed width */
            height: 300px; /* Set a fixed height */
            border-radius: 10px;
            margin-bottom: 10px;
            object-fit: fill; /* Ensures the image covers the area without distortion */
        }

        /* Style for course images 
        .course-item img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            margin-bottom: 10px;
        }*/
    </style>
</head>
<body>
    <header>
        <div class="logo">Pr <span style="color: #f4a51c;">Pro-Skills</span></div>
        <nav>
            <ul>
                <li><a href="homepg.php">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="forums.php">Forums</a></li>
            </ul>
        </nav>
    </header>
    <section class="hero">
        <div class="hero-content">
            <h1>
                <?php echo htmlspecialchars($category_name); ?> Courses
            </h1>
        </div>
    </section>
    <div class="course-list">
        <?php if (count($courses) > 0): ?>
            <?php foreach ($courses as $course): ?>
                <div class="course-item">
                    <h3><?php echo htmlspecialchars($course['name']); ?></h3>
                    <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['name']); ?>">
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <a href="course_details.php?course=<?php echo urlencode($course['name']); ?>&category=<?php echo urlencode($category_name); ?>" class="enroll-button">Explore Course</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No courses found in this category.</p>
        <?php endif; ?>
    </div>
</body>

</html>