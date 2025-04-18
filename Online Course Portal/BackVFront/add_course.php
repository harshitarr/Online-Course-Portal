<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'instructor') {
    header("Location: indexpg.html"); // Redirect to login if not logged in or not an instructor
    exit();
}

// Fetch existing categories
$category_query = "SELECT category_name FROM categories";
$category_result = $conn->query($category_query);
$categories = [];

if ($category_result) {
    while ($row = $category_result->fetch_assoc()) {
        $categories[] = $row['category_name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $category = $_POST['category']; // Get the selected category
    $new_category = $_POST['new_category']; // Get the new category input
    $instructor_id = $_SESSION['user_id'];

    // If the user selected "Other", use the new category input
    if ($category === 'other' && !empty($new_category)) {
        $category = $new_category; // Use the new category if provided
    }

    // Check if the category exists in the database
    if (!in_array($category, $categories)) {
        // If not, insert the new category into the categories table
        $insert_category_query = "INSERT INTO categories (category_name) VALUES (?)";
        $insert_stmt = $conn->prepare($insert_category_query);
        $insert_stmt->bind_param("s", $category);
        $insert_stmt->execute();
        $insert_stmt->close();
    }

    // Insert the new course
    $query = "INSERT INTO courses (course_name, description, price, duration, category, instructor_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdsdi", $course_name, $description, $price, $duration, $category, $instructor_id);

    if ($stmt->execute()) {
        // Add notification
        $notification_query = "INSERT INTO notifications (user_id, message, notification_status) VALUES (?, ?, ?)";
        $notification_stmt = $conn->prepare($notification_query);
        $message = "Course '$course_name' added successfully.";
        $status = "unread"; // or "read" based on your logic
        $notification_stmt->bind_param("iss", $instructor_id, $message, $status);
        $notification_stmt->execute();
        $notification_stmt->close();
    
        echo "<p>Course added successfully!</p>";
    } else {
        echo "<p>Error adding course: " . $stmt->error . "</p>";
    }
    
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course</title>
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

    <h1>Add New Course</h1>
    <form method="POST" action="">
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" required>
        
        <label for="description">Description:</label>
        <textarea name="description" rows="4" required></textarea>
        
        <label for="price">Price:</label>
        <input type="number" name="price" step="0.01" required>
        
        <label for="duration">Duration:</label>
        <input type="text" name="duration" required>
        
        <label for="category">Category:</label>
        <select name="category" required>
            <option value="">Select a category</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
            <option value="other">Other (type below)</option>
        </select>
        
        <input type="text" name="new_category" placeholder="Type new category here if not listed" id="new-category">
        
        <input type="submit" value="Add Course">
    </form>

    <script>
        // Show the input field for new category if "Other" is selected
        const categorySelect = document.querySelector('select[name="category"]');
        const newCategoryInput = document.getElementById('new-category');

        categorySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                newCategoryInput.style.display = 'block';
                newCategoryInput.required = true; // Make it required
            } else {
                newCategoryInput.style.display = 'none';
                newCategoryInput.required = false; // Not required
            }
        });
    </script>
</body>
</html>