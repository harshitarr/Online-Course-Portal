<?php
session_start();
include 'db_connect.php'; 

// Get course_id from request
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

if ($course_id > 0) {
    // SQL Query: Get subjects for the given course
    $sql = "SELECT subjects.subject_name, subjects.description 
            FROM subjects 
            JOIN course_subjects ON subjects.subject_id = course_subjects.subject_id 
            WHERE course_subjects.course_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];

    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    // Return JSON response
    echo json_encode($subjects);
} else {
    echo json_encode(["error" => "Invalid course ID"]);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Subjects</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #0c0f1a;
            color: white;
            text-align: center;
        }

        h2 {
            font-size: 36px;
            color: #f4a51c;
            margin: 20px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            font-weight: bold;
            padding: 25px;
        }

        h3 {
            font-size: 25px;
            color: #f4a51c;
            padding-top: 16px;
        }

        ul {
            list-style: none;
            padding: 0;
        }

        ul li {
            background: #1c2140;
            padding: 15px;
            border-radius: 8px;
            margin: 10px auto;
            width: 50%;
            text-align: left;
            outline: 2px solid #f4a51c;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        ul li:hover {
            background: #2a2e4b;
            transform: scale(1.05);
        }

        ul li strong {
            color: #f4a51c;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            ul li {
                width: 80%;
            }
        }
    </style>
</head>
<body>

    <h2 id="courseTitle"></h2>
    <h3>Subjects:</h3>
    <ul id="subjectsList"></ul>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const courseId = urlParams.get("course_id"); // Get course_id from URL

            if (!courseId) {
                alert("No course selected! Redirecting to home.");
                window.location.href = "homepg.php"; // Redirect if no data is found
                return;
            }

            // Set course title (Modify this if needed)
            document.getElementById("courseTitle").innerText = `Subjects for Course ID: ${courseId}`;

            // Fetch subjects from database
            fetch(`get_course_subjects.php?course_id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    const subjectsList = document.getElementById("subjectsList");
                    subjectsList.innerHTML = ""; // Clear previous data

                    if (data.error) {
                        subjectsList.innerHTML = `<li>${data.error}</li>`;
                        return;
                    }

                    if (data.length === 0) {
                        subjectsList.innerHTML = `<li>No subjects found for this course.</li>`;
                        return;
                    }

                    data.forEach(subject => {
                        const li = document.createElement ("li");
                        li.innerHTML = `<strong>${subject.subject_name}</strong>: ${subject.description || "No description available"}`;
                        subjectsList.appendChild(li);
                    });
                })
                .catch(error => console.error("Error fetching subjects:", error));
        });
    </script>

</body>
</html>