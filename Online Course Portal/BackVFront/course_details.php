<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the course name is set in the URL
if (!isset($_GET['course'])) {
    echo "Course not specified!";
    exit();
}

$course_name = $_GET['course'];

// Fetch course details
$course_details = [];
$sql = "SELECT course_id, course_name, description, price, duration, image FROM courses WHERE course_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_name);
if ($stmt->execute()) {
    $course_result = $stmt->get_result();
    if ($course_result->num_rows > 0) {
        $course_details = $course_result->fetch_assoc();
    } else {
        echo "Course not found!";
        exit();
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Check if the user is enrolled in the course
$user_id = $_SESSION['user_id']; // Get the logged-in user's ID
$course_id = $course_details['course_id']; // Get the current course ID

// Check if the user is enrolled in the course
$enrollment_check_sql = "SELECT COUNT(*) FROM enrollments WHERE user_id = ? AND course_id = ?";
$enrollment_stmt = $conn->prepare($enrollment_check_sql);
$enrollment_stmt->bind_param("ii", $user_id, $course_id);
$enrollment_stmt->execute();
$enrollment_stmt->bind_result($is_enrolled);
$enrollment_stmt->fetch();
$enrollment_stmt->close();

// Fetch subjects for the selected course
$subjects = [];
$sql = "SELECT s.subject_id, s.subject_name, s.image, s.description 
        FROM course_subjects cs 
        JOIN subjects s ON cs.subject_id = s.subject_id 
        JOIN courses c ON cs.course_id = c.course_id 
        WHERE c.course_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_name);

if ($stmt->execute()) {
    $subjects_result = $stmt->get_result();
    while ($subject = $subjects_result->fetch_assoc()) {
        $subjects[] = [
            'id' => htmlspecialchars($subject['subject_id']),
            'name' => htmlspecialchars($subject['subject_name']),
            'image' => htmlspecialchars($subject['image']),
            'description' => htmlspecialchars($subject['description']),
        ];
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Fetch assignments for the selected course
$assignments = [];
$sql = "SELECT assignment_id, title, description, due_date, max_marks FROM assignments WHERE course_id = (SELECT course_id FROM courses WHERE course_name = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_name);

if ($stmt->execute()) {
    $assignments_result = $stmt->get_result();
    while ($assignment = $assignments_result->fetch_assoc()) {
        $assignments[] = [
            'assignment_id' => htmlspecialchars($assignment['assignment_id']),
            'title' => htmlspecialchars($assignment['title']),
            'description' => htmlspecialchars($assignment['description']),
            'due_date' => htmlspecialchars($assignment['due_date']),
            'max_marks' => htmlspecialchars($assignment['max_marks']),
        ];
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Fetch reviews for the selected course
$reviews = [];
$sql = "SELECT r.rating, r.review_text, r.review_date, u.username 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        WHERE r.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);

if ($stmt->execute()) {
    $reviews_result = $stmt->get_result();
    while ($review = $reviews_result->fetch_assoc()) {
        $reviews[] = [
            'rating' => htmlspecialchars($review['rating']),
            'review_text' => htmlspecialchars($review['review_text']),
            'review_date' => htmlspecialchars($review['review_date']),
            'username' => htmlspecialchars($review['username']),
        ];
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Fetch submissions for the selected course
$submissions = [];
$sql = "SELECT s.submission_id, s.submission_date, s.file_path, s.marks_awarded, a.title 
        FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.assignment_id 
        WHERE a.course_id = (SELECT course_id FROM courses WHERE course_name = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_name);

if ($stmt->execute()) {
    $submissions_result = $stmt->get_result();
    while ($submission = $submissions_result->fetch_assoc()) {
        $submissions[] = [
            'submission_id' => htmlspecialchars($submission['submission_id']),
            'submission_date' => htmlspecialchars($submission['submission_date']),
            'file_path' => htmlspecialchars($submission['file_path']),
            'marks_awarded' => htmlspecialchars($submission['marks_awarded']),
            'assignment_title' => htmlspecialchars($submission['title']),
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
    <meta name="viewport" content = "width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course_details['course_name']); ?> Details</title>
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
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.1)), url('images/banner.png') no-repeat center center/cover;
            color: white;
        }
        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
        }
        .hero h1 {
            font-size: 48px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .cta-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f4a51c;
            color: #11152b;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .cta-button:hover {
            background-color: #d4931a;
        }
        .course-details {
            padding: 20px;
        }
        .subjects-list {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .subject-item {
            background: #1c2140;
            padding: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 400px;
            height: 350px;
            border-radius: 15px;
            border: 2px solid #f4a51c;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin: 10px;
        }
        .subject-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
        }
        .subject-item h3 {
            margin: 10px 0;
        }
        .subject-item p {
            flex-grow: 1;
            margin: 10px 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .enroll-button {
            background-color: #f4a51c;
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
        .assignments-list, .reviews-list {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .assignment-item, .review-item {
            background: #1c2140;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 300px;
            border-radius: 15px;
            border: 2px solid #f4a51c;
            text-align: left;
            margin: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .assignment-item:hover, .review-item:hover {
            transform: scale(1.05);
            box-shadow:  0 6px 25px rgba(0, 0, 0, 0.4);
        }
        .assignment-item h3, .review-item h3 {
            margin: 10px 0;
        }
        .assignment-item p, .review-item p {
            margin: 5px 0;
        }
        .submissions-list {
            list-style-type: none;
            padding: 0;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .submission-item {
            background: #1c2140;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            width: 300px;
            border-radius: 15px;
            border: 2px solid #f4a51c;
            text-align: left;
            margin: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .submission-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
        }
        .submission-item h3 {
            margin: 10px 0;
        }
        .submission-item p {
            margin: 5px 0;
        }
        a {
            color: inherit;
            text-decoration: none;
        }

        .image-container {
            display: flex;
            justify-content: center; /* Center the image horizontally */
            align-items: center; /* Center the image vertically */
            width: 300px; /* Set a fixed width for the container */
            height: 200px; /* Set a fixed height for the container */
            overflow: hidden; /* Hide any overflow */
            border-radius: 8px; /* Optional: add rounded corners */
        }

        img {
            width: 100%; /* Make the image take the full width of the container */
            height: auto; /* Maintain aspect ratio */
            object-fit: cover; /* Cover the container while maintaining aspect ratio */
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

        .assignments-heading {
            text-align: center;
            color: white;
        }

        .assignments-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }

        .assignment-item {
            width: 250px;
            padding: 15px;
            border-radius: 10px;
            color: white;
            position: relative;
        }

        .assignment-item:nth-child(4n+1) { background: #ef4444; }
        .assignment-item:nth-child(4n+2) { background: #f4a51c; }
        .assignment-item:nth-child(4n+3) { background: #10b981; }
        .assignment-item:nth-child(4n+4) { background: #3b82f6; }

        .toggle-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: white;
        }

        .assignment-dropdown {
            display: none;
            background: rgba(0, 0, 0, 0.2);
            padding: 10px;
            border-radius: 5px;
        }

        .submit-assignment {
            display: block;
            text-align: center;
            background: #f4a51c;
            padding: 5px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
        }

        .add-assignment {
            width: 150px;
            padding: 15px;
            border-radius: 30px;
            margin-top: 15px;
            color: white;
            text-align: center;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 10px rgba(255, 255, 255, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 2px solid #f4a51c;
        }

        .add-assignment:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(255, 255, 255, 0.3);
        }

        .plus-sign {
            font-size: 50px;
            font-weight: bold;
            color: white;
        }

        
        .add-assignment:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
        }

        .container {
            display: flex;
            justify-content: center;
            width: 100%; /* Ensures full width */
        }

        /* Modal Overlay - Blurred Background */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Dark translucent */
            backdrop-filter: blur(8px); /* Blur effect */
            z-index: 999;
        }

        /* Modal Box - Glassmorphism */
        .modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 350px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1); /* Transparent glass effect */
            backdrop-filter: blur(15px); /* Blur effect */
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
            display: none;
            z-index: 1000;
        }

        /* Modal Input Fields */
        .modal input,
        .modal textarea {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            outline: none;
        }

        /* Buttons */
        .modal button {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }

        .submit-btn {
            background-color: #f4a51c;
            color: white;
            font-weight: bold;
        }

        .submit-btn:hover {
            background-color: #e69500;
        }

        .cancel-btn {
            background-color: red;
            color: white;
        }

        .cancel-btn:hover {
            background-color: darkred;
        }

        .reviews-slider-container {
            overflow: hidden;
            width: 100%;
            position: relative;
        }

        .reviews-slider {
            display: flex;
            gap: 15px;
            animation: slide 45s linear infinite;
        }

        /* Review Card Design */
        .review-card {
            background-color: #6a5acd;
            color: white;
            padding: 20px;
            border-radius: 10px;
            min-width: 300px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
            text-align: center;
            position: relative;
        }

        /* Initials Circle */
        .review-initials {
            width: 50px;
            height: 50px;
            background-color: #ffffff;
            color: #6a5acd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            margin: 0 auto 10px;
        }

        /* Star Rating */
        .star-rating {
            margin: 10px 0;
            font-size: 20px;
        }

        /* Slider Animation */
        @keyframes slide {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .emoji-rating {
            display: flex;
            gap: 10px;
            justify-content: center;
            align-items: center;
        }

        .emoji-rating label {
            font-size: 24px;
            cursor: pointer;
        }

        .emoji-button {
            font-size: 30px;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .emoji-button:hover {
            transform: scale(1.2);
        }

        .emoji-button:focus {
            outline: 2px solid #6a5acd;
            border-radius: 50%;
        }

        .submissions-section {
            text-align: center;
            padding: 20px;
        }

        .submissions-heading {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            color:  #ffffff;
        }

        .submissions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .submission-card {
            width: 300px;
            padding: 20px;
            border-radius: 12px;
            color: white;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .submission-card h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .submission-card a {
            color: white;
            text-decoration: underline;
        }

        .submission-card button {
            margin-top: 10px;
            background-color: white;
            color: black;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }

        .submission-card button:hover {
            background-color: #ddd;
        }

    </style>
    <script>
        document.querySelectorAll('.submit-assignment').forEach(item => {
            item.addEventListener('click', event => {
                event.preventDefault(); // Prevent the default link behavior
                const assignmentId = item.getAttribute('data-assignment-id');
                document.getElementById('assignment_id').value = assignmentId; // Set the assignment ID in the hidden input
                document.getElementById('submissionModal').style.display = 'block'; // Show the modal
            });
        });

        function closeModal() {
            document.getElementById('submissionModal').style.display = 'none'; // Hide the modal
        }
       // Open Add Assignment Modal
        function openAddAssignmentModal() {
            document.getElementById("modalOverlay").style.display = "block";
            document.getElementById("addAssignmentModal").style.display = "block";
        }

        // Open Grade Modal
        function openGradeModal(submissionId) {
            document.getElementById("grade_submission_id").value = submissionId;
            document.getElementById("modalOverlay").style.display = "block";
            document.getElementById("gradeModal").style.display = "block";
        }

        // Close Modals
        function closeModals() {
            document.getElementById("modalOverlay").style.display = "none";
            document.getElementById("addAssignmentModal").style.display = "none";
            document.getElementById("gradeModal").style.display = "none";
        }

        // Submit Grade via AJAX
        function submitGrade() {
            let submissionId = document.getElementById("grade_submission_id").value;
            let marksAwarded = document.getElementById("marks_awarded").value;

            fetch("grade_submission.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `submission_id=${submissionId}&marks_awarded=${marksAwarded}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    alert(data.message); // Show success message
                    document.getElementById("marks_display_" + submissionId).innerText = marksAwarded; // Update UI
                    closeModals(); // Close modal after grading
                } else {
                    alert("Error: " + data.message); // Show error message
                }
            })
            .catch(error => console.error("Error:", error));
        }

        function toggleDropdown(id) {
            let element = document.getElementById(id);
            element.style.display = element.style.display === 'block' ? 'none' : 'block';
        }

    </script>
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
            <h1><?php echo htmlspecialchars($course_details['course_name']); ?></h1>
            <img src="<?php echo htmlspecialchars($course_details['image']); ?>" alt="<?php echo htmlspecialchars($course_details['course_name']); ?>" style="max-width: 100%; height: auto; border-radius: 10px;">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($course_details['description']); ?></p>
            <p><strong>Price:</strong> $<?php echo htmlspecialchars($course_details['price']); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($course_details['duration']); ?></p>
            <a href="enroll.php?course=<?php echo urlencode($course_details['course_name']); ?>" class="enroll-button">Enroll Now</a>
        </div>
    </section>

    <div class="course-details">
        <h2>Subjects</h2>
        <ul class="subjects-list">
            <?php if (count($subjects) > 0): ?>
                <?php foreach ($subjects as $subject): ?>
                    <li class="subject-item">
                        <a href="get_lessons.php?subject_id=<?php echo $subject['id']; ?>&course=<?php echo urlencode($course_details['course_name']); ?>">
                            <h3><?php echo $subject['name']; ?></h3>
                            <p><?php echo $subject['description']; ?></p>
                            <div class="image-container">
                                <img src="<?php echo htmlspecialchars($subject['image']); ?>" alt="<?php echo htmlspecialchars($subject['name']); ?>">
                            </div>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No subjects found for this course.</p>
            <?php endif; ?>
        </ul>
    </div>

    <h2 class="assignments-heading">Assignments</h2>
    <div class="assignments-container">
        <?php if (count($assignments) > 0): ?>
            <?php foreach ($assignments as $assignment): ?>
                <div class="assignment-item">
                    <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                    <p><strong>Due Date:</strong> <?php echo htmlspecialchars($assignment['due_date']); ?></p>
                    <p><strong>Max Marks:</strong> <?php echo htmlspecialchars($assignment['max_marks']); ?></p>
                    <button class="toggle-btn" onclick="toggleDropdown('desc-<?php echo $assignment['assignment_id']; ?>')">▼</button>
                    <div class="assignment-dropdown" id="desc-<?php echo $assignment['assignment_id']; ?>">
                        <p><?php echo htmlspecialchars($assignment['description']); ?></p>
                        <?php if ($_SESSION['user_type'] === 'student'): ?>
                            <a href="submit_assignment.php?assignment_id=<?php echo htmlspecialchars($assignment['assignment_id']); ?>&course=<?php echo urlencode($course_name); ?>" class="submit-assignment">Submit Assignment</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No assignments found for this course.</p>
        <?php endif; ?>
    </div>

    <?php if ($_SESSION['user_type'] === 'instructor'): ?>
        <div class="container">
            <div class="add-assignment" onclick="openAddAssignmentModal()">
                <div class="plus-sign">+</div>
                <p>Add Assignment</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Background Overlay -->
    <div id="modalOverlay" class="modal-overlay" onclick="closeModals()"></div>

    <!-- Add Assignment Modal -->
    <div id="addAssignmentModal" class="modal">
        <h2>Add Assignment</h2>
        <form method="POST" action="add_assignment.php">
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
            <input type="text" name="title" placeholder="Assignment Title" required>
            <textarea name="description" placeholder="Description" required></textarea>
            <input type="date" name="due_date" required>
            <input type="number" name="max_marks" placeholder="Max Marks" required>
            <button type="submit" class="submit-btn">Add Assignment</button>
            <button type="button" class="cancel-btn" onclick="closeModals()">Cancel</button>
        </form>
    </div>

    <!-- Grade Modal -->
    <div id="gradeModal" class="modal">
        <h2>Grade Submission</h2>
        <form id="gradeForm">
            <input type="hidden" name="submission_id" id="grade_submission_id">
            <input type="number" name="marks_awarded" id="marks_awarded" placeholder="Marks Awarded" required>
            <button type="button" class="submit-btn" onclick="submitGrade()">Submit</button>
            <button type="button" class="cancel-btn" onclick="closeModals()">Cancel</button>
        </form>
    </div>

    

    <h2>What Our Customers Are Saying</h2>
    <div class="reviews-slider-container">
        <?php if (count($reviews) > 0): ?>
            <div class="reviews-slider">
                <?php foreach ($reviews as $review): ?>
                    <div class="review-card">
                        <!-- Initials Circle -->
                        <div class="review-initials">
                            <?php echo strtoupper(substr($review['username'], 0, 1)); ?>
                        </div>
                        <!-- Reviewer Name -->
                        <h3><?php echo $review['username']; ?></h3>
                        <!-- Star Rating -->
                        <div class="star-rating">
                            <?php
                            $filledStars = floor($review['rating']);
                            $halfStar = $review['rating'] - $filledStars >= 0.5 ? true : false;
                            $emptyStars = 5 - $filledStars - ($halfStar ? 1 : 0);
                            
                            // Display filled stars
                            for ($i = 0; $i < $filledStars; $i++) {
                                echo "⭐";
                            }
                            // Display half star
                            if ($halfStar) {
                                echo "⭐️";
                            }
                            // Display empty stars
                            for ($i = 0; $i < $emptyStars; $i++) {
                                echo "☆";
                            }
                            ?>
                        </div>
                        <!-- Review Text -->
                        <p><?php echo $review['review_text']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No reviews found for this course.</p>
        <?php endif; ?>
    </div>

    <?php if ($is_enrolled > 0): ?>
        <h2>Rate us!</h2>
        <form action="add_review.php" method="POST" id="rating-form">
            <div class="emoji-rating">
                <button type="submit" name="rating" value="1" class="emoji-button">😡</button>
                <button type="submit" name="rating" value="2" class="emoji-button">😟</button>
                <button type="submit" name="rating" value="3" class="emoji-button">😐</button>
                <button type="submit" name="rating" value="4" class="emoji-button">🙂</button>
                <button type="submit" name="rating" value="5" class="emoji-button">😍</button>
            </div>
            <textarea name="review_text" id="review_text" placeholder="Add a comment..." required></textarea>
            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>"> 
            <input type="hidden" name="course_name" value="<?php echo $course_name; ?>"> 
        </form>
    <?php else: ?>
        <p>You must be enrolled in this course to leave a review.</p>
    <?php endif; ?>

    <div class="submissions-section">
        <h2 class="submissions-heading">Submissions</h2>
        <div class="submissions">
            <?php if (count($submissions) > 0): ?>
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-card" style="background-color: 
                        <?php 
                            switch ($submission['marks_awarded']) {
                                case ($submission['marks_awarded'] === 0): echo '#ef4444'; break; // Red for ungraded
                                case ($submission['marks_awarded'] <= 50): echo '#f4a51c'; break; // Orange for below-average
                                case ($submission['marks_awarded'] <= 75): echo '#10b981'; break; // Green for good
                                default: echo '#3b82f6'; // Blue for excellent
                            }
                        ?>">
                        <h3><?php echo $submission['assignment_title']; ?></h3>
                        <p><strong>Submission Date:</strong> <?php echo $submission['submission_date']; ?></p>
                        <p><strong>File Path:</strong> <a href="<?php echo $submission['file_path']; ?>" target="_blank">View Submission</a></p>
                        <p><strong>Marks Awarded:</strong> <?php echo $submission['marks_awarded']; ?></p>
                        <?php if ($_SESSION['user_type'] === 'instructor'): ?>
                            <button onclick="openGradeModal(<?php echo $submission['submission_id']; ?>)">Grade</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No submissions found for this course.</p>
            <?php endif; ?>
        </div>
    </div>


    </ul>
</body>
</html>