<?php
session_start();
include 'db_connect.php'; 
header('Content-Type: text/html');

$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
$course_name = isset($_GET['course']) ? $_GET['course'] : '';
if ($subject_id <= 0) {
    echo "Invalid subject ID.";
    exit();
}

// Fetch lessons for the given subject_id
$query = "SELECT lesson_id, lesson_title, content FROM lessons WHERE subject_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $subject_id);
$stmt->execute();
$result = $stmt->get_result();

$lessons = [];
while ($row = $result->fetch_assoc()) {
    $lessons[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lessons</title>
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
        .lesson-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .lesson-item {
            width: 80%;
            padding: 20px;
            margin: 15px 0;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: left;
            position: relative;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }
        .lesson-item:nth-child(4n+1) { background: #ef4444; } /* Red */
        .lesson-item:nth-child(4n+2) { background: #f4a51c; } /* Yellow */
        .lesson-item:nth-child(4n+3) { background: #10b981; } /* Green */
        .lesson-item:nth-child(4n+4) { background: #3b82f6; } /* Blue */
        
        .lesson-item:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.4);
        }
        .lesson-item h2 {
            margin: 10px 0;
        }
        .lesson-item p {
            margin: 5px 0;
        }

        /* Down Arrow Button */
        .arrow {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        /* Rotate arrow when active */
        .lesson-item.active .arrow {
            transform: translateY(-50%) rotate(180deg);
        }

        /* Hidden Info Section */
        .lesson-details {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease-in-out;
            background: rgba(0, 0, 0, 0.2);
            margin-top: 10px;
            padding: 0 15px;
            border-radius: 10px;
        }
        .lesson-details p {
            margin: 10px 0;
        }

        .profile-container a {
            display: inline-block;
            background: #f4a51c;
            color: #11152b;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: background 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(255, 165, 0, 0.4);
        }

        .profile-container a:hover {
            background: #ffb933;
            transform: scale(1.05);
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
                <li><a href="forums.php">Forums</a></li>
            </ul>
        </nav>
        <div class="profile-container">
            <a href="course_details.php?course=<?php echo urlencode($course_name); ?>">Back</a>
        </div>
    </header>

    <section class="lesson-container">
        <h1 style ="font-size: 35px;">Lessons</h1>
        <?php if (count($lessons) > 0): ?>
            <?php foreach ($lessons as $lesson): ?>
                <div class="lesson-item">
                    <h2><?php echo htmlspecialchars($lesson['lesson_title']); ?></h2>
                    <span class="arrow">▼</span>
                    <div class="lesson-details">
                        <p><?php echo nl2br(htmlspecialchars($lesson['content'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No lessons found for this subject.</p>
        <?php endif; ?>
    </section>

    <script>
        // Select all lesson items
        const lessons = document.querySelectorAll('.lesson-item');

        lessons.forEach(lesson => {
            lesson.addEventListener('click', () => {
                // Toggle active class for arrow rotation
                lesson.classList.toggle('active');

                // Toggle lesson details visibility
                const details = lesson.querySelector('.lesson-details');
                if (details.style.maxHeight) {
                    details.style.maxHeight = null;
                } else {
                    details.style.maxHeight = details.scrollHeight + "px";
                }
            });
        });
    </script>
</body>
</html>