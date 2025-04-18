<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Fetch reviews from the database
$reviews = [];
$sql = "SELECT r.review_id, r.rating, r.review_text, r.review_date, u.username, c.course_name 
        FROM reviews r 
        JOIN users u ON r.user_id = u.user_id 
        JOIN courses c ON r.course_id = c.course_id 
        ORDER BY r.review_date DESC";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($review = $result->fetch_assoc()) {
        $reviews[] = [
            'id' => htmlspecialchars($review['review_id']),
            'rating' => htmlspecialchars($review['rating']),
            'text' => htmlspecialchars($review['review_text']),
            'date' => htmlspecialchars($review['review_date']),
            'username' => htmlspecialchars($review['username']),
            'course_name' => htmlspecialchars($review['course_name']),
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
    <title>Reviews</title>
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
        .review-list {
            padding: 20px;
        }
        .review-item {
            background: #1c2140;
            padding: 20px;
            border-radius: 10px;
            margin: 10px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .review-item h4 {
            color: #f4a51c;
        }
        .review-item p {
            color: white;
        }
        a {
            color: inherit; /* Keeps text color normal */
            text-decoration: none; /* Removes underline */
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">Pr <span style="color: #f4a51c;">Pro-Skills</span></div>
        <nav>
            <ul>
                <li><a href="indexpg.html">Home</a></li>
                <li><a href="about.php">About Us</a></li>
                <li><a href="reviews.php">Reviews</a></li> <!-- Updated to point to reviews.php -->
            </ul>
        </nav>
        <div class="profile-container">
            <a href="indexpg.html">Back</a>
        </div>
    </header>

    <h1>User Reviews</h1>
    <div class="review-list">
        <?php if (count($reviews) > 0): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <h4><?php echo $review['username']; ?> - Rating: <?php echo $review['rating']; ?>/5</h4>
                    <p><?php echo nl2br($review['text']); ?></p>
                    <p><em><?php echo $review['date']; ?> - Course: <?php echo $review['course_name']; ?></em></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews available at the moment.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2023 Pro-Skills. All rights reserved.</p>
    </footer>
</body>
</html>