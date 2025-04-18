<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT username, email, user_type FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();

if ($user_result->num_rows === 1) {
    $user = $user_result->fetch_assoc();
    $username = htmlspecialchars($user['username']);
    $email = htmlspecialchars($user['email']);
    $user_type = htmlspecialchars($user['user_type']);
} else {
    echo "User not found!";
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #0c0f1a;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            animation: fadeIn 1s ease-in-out;
            transition: background 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome {
            font-size: 36px;
            font-weight: bold;
            color: #f4a51c;
            margin-bottom: 5px;
            transition: transform 0.3s;
        }

        .user-id {
            font-size: 18px;
            color: #d3d3d3;
            margin-bottom: 20px;
        }

        .profile-container {
            background: #11152b;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.4);
            animation: fadeIn 1s ease-in-out;
        }

        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #f4a51c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: #11152b;
            font-weight: bold;
            margin: 0 auto 15px;
            transition: transform 0.3s ease-in-out;
        }

        .profile-field {
            margin: 15px 0;
            position: relative;
            text-align: left;
            animation: fadeIn 1s ease-in-out;
        }

        .profile-field label {
            display: block;
            font-size: 16px;
            color: #f4a51c;
            margin-bottom: 5px;
        }

        .profile-field input {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background: #1c2140;
            color: white;
            font-size: 16px;
            text-align: center;
            transition: background 0.3s ease-in-out;
        }

        .profile-field input:focus {
            background: #2a2f55;
            outline: none;
        }

        .edit-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: #f4a51c;
            border: none;
            color: black;
            cursor: pointer;
            font-size: 16px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s, transform 0.3s;
        }

        .edit-btn:hover {
            background: #d48b19;
            transform: scale(1.2);
        }

        .save-btn {
            background: #f4a51c;
            color: black;
            font-size: 16px;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            margin-top: 15px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s, transform 0.2s;
        }

        .save-btn:hover {
            background: #d48b19;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="welcome" id="welcomeText">Welcome, <?php echo $username; ?></div>
    <div class="user-id">User ID: <?php echo $user_id; ?></div>

    <div class="profile-container">
        <div class="profile-img" id="profileInitials">
            <?php
                $nameParts = explode(" ", $username);
                $initials = strtoupper(substr($nameParts[0], 0, 1));
                if (count($nameParts) > 1) {
                    $initials .= strtoupper(substr($nameParts[1], 0, 1));
                }
                echo $initials;
            ?>
        </div>

        <div class="profile-field">
            <label>Full Name</label>
            <input type="text" id="name" value="<?php echo $username; ?>" disabled oninput="updateProfile()">
            <button class="edit-btn" onclick="enableEdit('name')">✎</button>
        </div>

        <div class="profile-field">
            <label>Email</label>
            <input type="email" id="email" value="<?php echo $email; ?>" disabled>
            <button class="edit-btn" onclick="enableEdit('email')">✎</button>
        </div>

        <div class="profile-field">
            <label>User Type</label>
            <input type="text" id="userType" value="<?php echo ucfirst($user_type); ?>" disabled>
            <button class="edit-btn" onclick="enableEdit('userType')">✎</button>
        </div>

        <button class="save-btn" onclick="saveChanges()">Save Changes</button>
    </div>

    <script>
        function enableEdit(id) {
            let input = document.getElementById(id);
            input.removeAttribute("disabled");
            input.focus();
        }

        function saveChanges() {
            let inputs = document.querySelectorAll("input");
            for (let input of inputs) {
                if (!input.value.trim()) {
                    alert("All fields must be filled!");
                    input.removeAttribute("disabled"); // Keep the empty field editable
                    input.focus();
                    return; // Stop execution
                }
            }

            inputs.forEach(input => input.setAttribute("disabled", true));
            alert("Changes saved successfully!");
        }

        function updateProfile() {
            let nameInput = document.getElementById("name").value.trim();
            let welcomeText = document.getElementById("welcomeText");
            let profileInitials = document.getElementById("profileInitials");
            
            welcomeText.textContent = `Welcome, ${nameInput || "User"}`;
            
            let nameParts = nameInput.split(" ");
            let initials = nameParts.length > 1 
                ? nameParts[0][0] + nameParts[1][0] 
                : nameParts[0][0];

            profileInitials.textContent = initials ? initials.toUpperCase() : "U";
        }

        function saveChanges() {
            let name = document.getElementById("name").value.trim();
            let email = document.getElementById("email").value.trim();
            let userType = document.getElementById("userType").value.trim();

            if (!name || !email || !userType) {
                alert("All fields must be filled!");
                return;
            }

            // Prepare data for AJAX
            let formData = new FormData();
            formData.append("name", name);
            formData.append("email", email);
            formData.append("userType", userType);

            // Send AJAX request to update profile
            fetch("update_profile.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Profile updated successfully!");
                    document.querySelectorAll("input").forEach(input => input.setAttribute("disabled", true));
                } else {
                    alert("Error updating profile: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        }

    </script>

</body>
</html>
