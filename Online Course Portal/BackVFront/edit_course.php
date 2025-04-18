<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$course_id = $_GET['id'];

// Fetch the existing course details
$query = "SELECT * FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Course not found.";
    exit();
}

$course = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Update course details
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $category = $_POST['category']; // Get the selected or typed category
    $new_category = $_POST['new_category']; // Get the new category input

    // If the user selected "Other", use the new category input
    if ($category === 'other' && !empty($new_category)) {
        $category = $new_category; // Use the new category if provided
    }

    $update_query = "UPDATE courses SET course_name = ?, description = ?, price = ?, duration = ?, category = ? WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("ssdssi", $course_name, $description, $price, $duration, $category, $course_id);
    
    if ($update_stmt->execute()) {
        // Add notification
        $notification_query = "INSERT INTO notifications (user_id, message, notification_status) VALUES (?, ?, ?)";
        $notification_stmt = $conn->prepare($notification_query);
        $message = "Course '$course_name' updated successfully.";
        $status = "unread"; // or "read" based on your logic
        $notification_stmt->bind_param("iss", $_SESSION['user_id'], $message, $status);
        $notification_stmt->execute();
        $notification_stmt->close();
    
        echo "<p>Course updated successfully!</p>";
    } else {
        echo "<p>Error updating course: " . $update_stmt->error . "</p>";
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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

        select {
            width: 100%; /* Ensure the dropdown matches input fields */
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

        #new-category {
            display: none;
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

    <h1>Edit Course</h1>
    <form method="POST" action="">
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
        
        <label for="description">Description:</label>
        <textarea name="description" required><?php echo htmlspecialchars($course['description']); ?></textarea>
        
        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($course['price']); ?>" required>
        
        <label for="duration">Duration:</label>
        <input type="text" name="duration" value="<?php echo htmlspecialchars($course['duration']); ?>" required>
        
        <label for="category">Category:</label>
        <select name="category" required>
            <option value="<?php echo htmlspecialchars($course['category']); ?>"><?php echo htmlspecialchars($course['category']); ?></option>
            <option value="Programming">Programming</option>
            <option value="Design">Design</option>
            <option value="Marketing">Marketing</option>
            <option value="other">Other (type below)</option>
        </select>
        
        <input type="text" name="new_category" placeholder="Type new category here if not listed" id="new-category">
        
        <input type="submit" value="Update Course">
    </form>

    <script>
        const categorySelect = document.querySelector('select[name="category"]');
        const newCategoryInput = document.getElementById('new-category');

        categorySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.required = true;
            } else {
                newCategoryInput.style.display = 'none';
                newCategoryInput.required = false;
            }
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>