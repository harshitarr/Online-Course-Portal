<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the forum_id is set in the URL
if (!isset($_GET['forum_id'])) {
    echo "Forum not specified!";
    exit();
}

$forum_id = intval($_GET['forum_id']);

// Fetch forum details
$forum = [];
$sql = "SELECT forum_name, description FROM forums WHERE forum_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $forum_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $forum = $result->fetch_assoc();
    } else {
        echo "Forum not found!";
        exit();
    }
} else {
    echo "Error executing query: " . $stmt->error;
}

// Fetch posts for the selected forum
$posts = [];
$sql = "SELECT p.post_id, p.post_content, p.created_date, u.username 
        FROM forum_posts p 
        JOIN users u ON p.user_id = u.user_id 
        WHERE p.forum_id = ? 
        ORDER BY p.created_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $forum_id);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($post = $result->fetch_assoc()) {
        $posts[] = [
            'id' => htmlspecialchars($post['post_id']),
            'content' => htmlspecialchars($post['post_content']),
            'created_date' => htmlspecialchars($post['created_date']),
            'username' => htmlspecialchars($post['username']),
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
    <title><?php echo htmlspecialchars($forum['forum_name']); ?> - Posts</title>
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
        .forum-list {
            padding: 20px;
        }
        .post-item {
            background: #1c2140;
            padding: 20px;
            border-radius: 10px;
            margin: 10px 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }
        .post-item h4 {
            color: #f4a51c;
        }
        .post-item p {
            color: white;
        }
        .post-form {
            margin: 20px 0;
        }
        .post-form textarea {
            width: 80%;
            height: 100px;
            border-radius: 5px;
            border: none;
            padding: 10px;
            margin-bottom: 10px;
        }
        .post-form button {
            background-color: #f4a51c;
            color: #11152b;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .post-form button:hover {
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
                <li><a href="forums.php">Forums</a></li>
            </ul>
        </nav>
        <div class="profile-container">
            <a href="forums.php">Back</a>
        </div>
    </header>

    <h1><?php echo htmlspecialchars($forum['forum_name']); ?></h1>
    <p><?php echo htmlspecialchars($forum['description']); ?></p>

    <div class="forum-list">
        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post-item">
                    <h4><?php echo $post['username']; ?> (<?php echo $post['created_date']; ?>)</h4>
                    <p><?php echo nl2br($post['content']); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts available in this forum.</p>
        <?php endif; ?>
    </div>

    <div class="post-form">
        <h2>Add a New Post</h2>
        <form action="add_post.php" method="POST">
            <textarea name="post_content" placeholder="Write your post here..." required></textarea>
            <input type="hidden" name="forum_id" value="<?php echo $forum_id; ?>">
            <button type="submit">Submit Post</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2025 Pro-Skills. All rights reserved.</p>
    </footer>
</body>
</html>