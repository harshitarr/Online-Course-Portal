<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Fetch forums from the database
$forums = [];
$sql = "SELECT forum_id, forum_name, description FROM forums";
$stmt = $conn->prepare($sql);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($forum = $result->fetch_assoc()) {
        $forums[] = [
            'id' => htmlspecialchars($forum['forum_id']),
            'name' => htmlspecialchars($forum['forum_name']),
            'description' => htmlspecialchars($forum['description']),
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
    <title>Forums</title>
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
        .forum-list {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }
        .forum-item {
            background: #1c2140;
            padding: 20px;
            border-radius: 10px;
            width: 300px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .forum-item h3 {
            color: #f4a51c;
        }
        .forum-item p {
            color: white;
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
            text-decoration: none; /* Ensure it's styled as a button */
        }
        .enroll-button:hover {
            background-color: #d4931a;
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
                <li><a href="forums.php">Forums</a></li> <!-- Added Forums link -->
            </ul>
        </nav>
        <div class="profile-container">
            <a href="homepg.php">Back</a>
        </div>
    </header>

    <section class="hero">
 <div class="hero-content">
            <h1>Welcome to the Forums</h1>
            <p>Join the discussion and connect with fellow learners.</p>
        </div>
    </section>

    <h2>Available Forums</h2>
    <div class="forum-list">
        <?php if (count($forums) > 0): ?>
            <?php foreach ($forums as $forum): ?>
                <div class="forum-item">
                    <h3><?php echo $forum['name']; ?></h3>
                    <p><?php echo $forum['description']; ?></p>
                    <a href="forum_posts.php?forum_id=<?php echo $forum['id']; ?>" class="enroll-button">View Posts</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No forums available at the moment.</p>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2023 Pro-Skills. All rights reserved.</p>
    </footer>
</body>
</html>