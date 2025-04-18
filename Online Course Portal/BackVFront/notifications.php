<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: indexpg.html"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT message, created_date, notification_status FROM notifications WHERE user_id = ? ORDER BY created_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Notifications</title>
    <style>
        /* Include your CSS styles here */
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
        /* Add other styles as needed */
        .notification {
            background: #1c2140;
            padding: 10px;
            margin: 10px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.1);
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
                <li><a href="homepg.php">My Courses</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <h1>Your Notifications</h1>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($notification = $result->fetch_assoc()): ?>
            <div class="notification">
                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                <small><?php echo htmlspecialchars($notification['created_date']); ?></small>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No notifications found.</p>
    <?php endif; ?>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>