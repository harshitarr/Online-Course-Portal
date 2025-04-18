<?php
session_start();
include 'db_connect.php'; // Ensure you have a database connection

// Check if the course is set in the URL
$course_name = isset($_GET['course']) ? $_GET['course'] : null;

// Fetch course details based on the course name
$course_details = [];
if ($course_name) {
    $sql = "SELECT course_id, course_name, description, price, duration FROM courses WHERE course_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $course_name);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $course_details = $result->fetch_assoc();
        }
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Now - Pro-Skills</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #0c0f1a, #1c2140);
            color: white;
            text-align: center;
        }
        header {
            background: #11152b;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #f4a51c;
        }
        nav ul {
            list-style: none;
            padding: 0;
            display: flex;
        }
        nav ul li {
            margin: 0 20px;
        }
        nav ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            transition: background-color 0.3s ease;
            border-radius: 5px;
        }
        nav ul li a:hover {
            background-color: rgba(244, 165, 28, 0.3);
        }
        .form-container {
            background: #1c2140;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            margin: 50px auto;
            position: relative;
            overflow: hidden;
        }
        h2 {
            color: #f4a51c;
            margin-bottom: 20px;
            font-size: 32px;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        input, select, textarea {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: #2a2e4b;
            color: white;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        input:focus, select:focus, textarea:focus {
            background: #3a3e5b;
            outline: none;
        }
        .button {
            background-color: #f4a51c;
            color: #11152b;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin: 10px 5px;
            font-size: 16px;
        }
        .button:hover {
            background-color: #d4931a;
            transform: scale(1.05);
        }
        .error {
            color: red;
            margin: 10px 0;
            display: none;
        }
        /* Overlay Styles */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        .overlay.show {
            display: block;
            opacity: 1;
        }
        /* Payment Overlay Styles */
        .payment-overlay {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.5);
            background: #1c2140;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
            z-index: 1001;
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.5s ease;
            width: 90%;
            max-width: 400px;
        }
        .payment-overlay.show {
            display: block;
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        /* Receipt Overlay Styles */
        .receipt-overlay {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.5);
            background: rgba(28, 33, 64, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
            z-index: 1001;
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.5s ease;
            width: 90%;
            max-width: 600px; /* Increased width for better visibility */
            max-height: 80%; /* Limit height for scrolling */
            overflow-y: auto; /* Enable vertical scrolling */
        }
        .receipt-overlay.show {
            display: block;
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        .receipt-overlay h3 {
            color: #f4a51c;
            margin-bottom: 10px;
            font-size: 24px; /* Increased font size */
        }
        #receiptDetails {
            text-align: left; /* Align text to the left for better readability */
            font-size: 16px; /* Set a readable font size */
            white-space: pre-wrap; /* Preserve whitespace for formatting */
        }
        #closeReceipt {
            background-color: #f4a51c;
            color: #11152b;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        #closeReceipt:hover {
            background-color: #d4931a;
        }
        /* Enrollment Successful Overlay Styles */
        .success-overlay {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.5);
            background: rgba(28, 33, 64, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.7);
            z-index: 1002;
            opacity: 0;
            transition: transform 0.5s ease, opacity 0.5s ease;
            width: 90%;
            max-width: 400px;
        }
        .success-overlay.show {
            display: block;
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        .success-overlay h3 {
            color: #f4a51c;
            margin-bottom: 10px;
        }
        .success-overlay .tick {
            font-size: 80px;
            color: green; /* Change tick color to green */
            animation: tickAnimation 1s forwards;
        }
        @keyframes tickAnimation {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
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
                <<li><a href="forums.php">Forums</a></li>
            </ul>
        </nav>
    </header>

    <div class="form-container" id="formContainer">
        <h2>Enroll Now</h2>
        <form id="enrollmentForm">
            <!-- Step 1: Personal Details -->
            <div class="form-step active">
                <h3>Personal Details</h3>
                <input type="text" id="fullName" placeholder="Full Name" required>
                <input type="email" id="email" placeholder="Email" required>
                <input type="tel" id="phone" placeholder="Phone Number" required>
                <input type="date" id="dob" placeholder="Date of Birth" required>   
                <div id="dobError" class="error" style="display:none;">Invalid Date of Birth.</div>
                <select id="gender" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
                <div class="error" id="error1">Please fill in all fields.</div>
                <button type="button" class="button" id="next1">Next</button>
            </div>

            <!-- Step 2: Course Details -->
            <div class="form-step">
                <h3>Course Details</h3>
                <select id="course" required>
                    <option value="<?php echo htmlspecialchars($course_details['course_name']); ?>" selected>
                        <?php echo htmlspecialchars($course_details['course_name']); ?>
                    </option>
                </select>
                <input type="text" id="courseDuration" placeholder="Course Duration" value="<?php echo htmlspecialchars($course_details['duration']); ?>" readonly>
                <input type="text" id="courseFee" placeholder="Course Fee" value="<?php echo htmlspecialchars($course_details['price']); ?>" readonly>
                <input type="text" id="enrollmentDate" placeholder="Enrollment Date" value="" readonly>
                <div class="error" id="error2">Please select a course.</div>
                <button type="button" class="button" id="prev1">Previous</button>
                <button type="button" class="button" id="next2">Next</button>
            </div>

            <!-- Step 3: Login Credentials -->
            <div class="form-step">
                <h3>Login Credentials (Optional)</h3>
                <input type="text" id="username" placeholder="Username">
                <input type="password" id="password" placeholder="Password">
                <input type="password" id="confirmPassword" placeholder="Confirm Password">
                <div class="error" id="error3">Please fill in all fields.</div>
                <div class="error" id="errorPassword">Passwords do not match.</div>
                <button type="button" class="button" id="prev2">Previous</button>
                <button type="button" class="button" id="next3">Next</button>
            </div>

            <!-- Step 4: Confirmation -->
            <div class="form-step">
                <h3>Confirmation</h3>
                <p>Please review your details before proceeding to payment.</p>
                <button type="button" class="button" id="prev3">Previous</button>
                <button type="submit" class="button">Pay Now</button>
            </div>
        </form>
    </div>
    <!-- Overlay for Payment Details -->
    <div class="overlay" id="overlay"></div>
    <div class="payment-overlay" id="paymentOverlay">
        <h3>Payment Details</h3>
        <div class="payment-details">
            <p><strong>Course:</strong> <span id="paymentCourse"></span></p>
            <p><strong>Duration:</strong> <span id="paymentDuration"></span></p>
            <p><strong>Fee:</strong> <span id="paymentFee"></span></p>
            <p><strong>Enrollment Date:</strong> <span id="paymentDate"></span></p>
            <label for="paymentMethod">Select Payment Method:</label>
            <select id="paymentMethod" required>
                <option value="" disabled selected>Select Payment Method</option>
                <option value="GPay">GPay</option>
                <option value="PayPal">PayPal</option>
                <option value="Credit Card">Credit Card</option>
            </select>
        </div>
        <button class="button" id="confirmPayment">Confirm Payment</button>
        <button class="button" id="cancelPayment">Cancel</button>
    </div>

    <!-- Enrollment Successful Overlay -->
    <div class="success-overlay" id="successOverlay">
        <h3>Enrollment Successful!</h3>
        <div class="tick">✔️</div>
        <p>Your enrollment has been successfully completed.</p>
        <button class="button" id="okayButton">Okay</button>
    </div>

    <!-- Receipt Overlay -->
    <div class="receipt-overlay" id="receiptOverlay">
        <h3>Your Receipt</h3>
        <pre id="receiptDetails" style="color: white;"></pre>
        <button class="button" id="closeReceipt" data-course-id="<?php echo $course_details['course_id']; ?>">Close</button>
    </div>


    <script>
        let receiptData = {}; // Store receipt data

        document.getElementById("enrollmentForm").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent form submission

            // Validate date of birth
            const dob = new Date(document.getElementById("dob").value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            const isFutureDate = dob > today;

            if (isFutureDate || age < 15 || (age === 15 && monthDiff < 0)) {
                document.getElementById("dobError").style.display = "block";
                return;
            } else {
                document.getElementById("dobError").style.display = "none";
            }

            // Auto-fill course details based on selected course
            const course = document.getElementById("course").value;
            let duration = "";
            let fee = "";

            switch (course) {
                case "Full Stack Development":
                    duration = "6 months";
                    fee = "$500";
                    break;
                case "JavaScript Essentials":
                    duration = "3 months";
                    fee = "$300";
                    break;
                case "HTML & CSS Basics":
                    duration = "2 months";
                    fee = "$200";
                    break;
            }

            // Set payment details in the overlay
            document.getElementById("paymentCourse").innerText = course;
            document.getElementById("paymentDuration").innerText = duration;
            document.getElementById("paymentFee").innerText = fee;
            document.getElementById("paymentDate").innerText = new Date().toLocaleDateString();

            // Show the payment overlay
            document.getElementById("overlay").classList.add('show');
            document.getElementById("paymentOverlay").classList.add('show');
        });

        // Course selection change event
        document.getElementById("course").addEventListener("change", function() {
            const course = this.value;
            let duration = "";
            let fee = "";

            switch (course) {
                case "Full Stack Development":
                    duration = "6 months";
                    fee = "$500";
                    break;
                case "JavaScript Essentials":
                    duration = "3 months";
                    fee = "$300";
                    break;
                case "HTML & CSS Basics":
                    duration = "2 months";
                    fee = "$200";
                    break;
            }

            document.getElementById("courseDuration").value = duration;
            document.getElementById("courseFee").value = fee;
            document.getElementById("enrollmentDate").value = new Date().toLocaleDateString();
        });

        // Navigation between steps
        let currentStep = 0;
        const steps = document.querySelectorAll(".form-step");
        steps[currentStep].classList.add("active");

        document.getElementById("next1").addEventListener("click", function() {
            // Validate date of birth before proceeding
            const dob = new Date(document.getElementById("dob").value);
            const today = new Date();
            const age = today.getFullYear() - dob.getFullYear();
            const monthDiff = today.getMonth() - dob.getMonth();
            const isFutureDate = dob > today;

            if (isFutureDate || age < 15 || (age === 15 && monthDiff < 0)) {
                document.getElementById("dobError").style.display = "block";
                return; // Prevent moving to the next step
            } else {
                document.getElementById("dobError").style.display = "none";
            }

            if (validateStep1()) {
                steps[currentStep].classList.remove("active");
                currentStep++;
                steps[currentStep].classList.add("active");
            }
        });

        document.getElementById("prev1").addEventListener("click", function() {
            steps[currentStep].classList.remove("active");
            currentStep--;
            steps[currentStep].classList.add("active");
        });

        document.getElementById("next2").addEventListener("click", function() {
            if (validateStep2()) {
                steps[currentStep].classList.remove("active");
                currentStep++;
                steps[currentStep].classList.add("active");
            }
        });

        document.getElementById("prev2").addEventListener("click", function() {
            steps[currentStep].classList.remove("active");
            currentStep--;
            steps[currentStep].classList.add("active");
        });

        document.getElementById("next3").addEventListener("click", function() {
            if (validateStep3()) {
                steps[currentStep].classList.remove("active");
                currentStep++;
                steps[currentStep].classList.add("active");
            }
        });

        document.getElementById("prev3").addEventListener("click", function() {
            steps[currentStep].classList.remove("active");
            currentStep--;
            steps[currentStep].classList.add("active");
        });

        // Validation functions
        function validateStep1() {
            const fullName = document.getElementById("fullName").value;
            const email = document.getElementById("email").value;
            const phone = document.getElementById("phone").value;
            const dob = document.getElementById("dob").value;
            const gender = document.getElementById("gender").value;

            if (!fullName || !email || !phone || !dob || !gender) {
                document.getElementById("error1").style.display = "block";
                return false;
            } else {
                document.getElementById("error1").style.display = "none";
                return true;
            }
        }

        function validateStep2() {
            const course = document.getElementById("course").value;

            if (!course) {
                document.getElementById("error2").style.display = "block";
                return false;
            } else {
                document.getElementById("error2").style.display = "none";
                return true;
            }
        }

        function validateStep3() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;

            if (password !== confirmPassword) {
                document.getElementById("errorPassword").style.display = "block";
                return false;
            } else {
                document.getElementById("errorPassword").style.display = "none";
                return true;
            }
        }

        // Auto-fill enrollment date on page load
        document.getElementById("enrollmentDate").value = new Date().toLocaleDateString();

        // Confirm Payment
        document.getElementById("confirmPayment").addEventListener("click", function() {
            const paymentMethod = document.getElementById("paymentMethod").value;
            if (!paymentMethod) {
                // Show warning overlay if no payment method is selected
                alert("Please select a payment method before confirming payment.");
            } else {
                // Simulate saving the form data as a receipt
                receiptData = {
                    fullName: document.getElementById("fullName").value,
                    email: document.getElementById("email").value,
                    phone: document.getElementById("phone").value,
                    dob: document.getElementById("dob").value,
                    gender: document.getElementById("gender").value,
                    course: document.getElementById("course").value,
                    duration: document.getElementById("courseDuration").value,
                    fee: document.getElementById("courseFee").value,
                    enrollmentDate: document.getElementById("enrollmentDate").value,
                    username: document.getElementById("username").value,
                    paymentMethod: paymentMethod
                };

                // Hide the enrollment form
                document.getElementById("formContainer").style.display = 'none';

                // Show the success overlay
                document.getElementById("successOverlay").classList.add('show');
            }
        });

        // Okay button in success overlay
        document.getElementById("okayButton").addEventListener("click", function() {
            // Hide the success overlay
            document.getElementById("successOverlay").classList.remove('show');

            // Show the receipt overlay
            const receiptDetails = `
                Full Name: ${receiptData.fullName}
                Email: ${receiptData.email}
                Phone: ${receiptData.phone}
                Date of Birth: ${receiptData.dob}
                Gender: ${receiptData.gender}
                Course: ${receiptData.course}
                Duration: ${receiptData.duration}
                Fee: ${receiptData.fee}
                Enrollment Date: ${receiptData.enrollmentDate}
                Username: ${receiptData.username}
                Payment Method: ${receiptData.paymentMethod}
            `;
            document.getElementById("receiptDetails").innerText = receiptDetails;
            document.getElementById("receiptOverlay").classList.add('show');

            // Prepare data to send
            const dataToSend = {
                fullName: receiptData.fullName,
                email: receiptData.email,
                phone: receiptData.phone,
                dob: receiptData.dob,
                gender: receiptData.gender,
                course: receiptData.course,
                username: receiptData.username,
                password: receiptData.password, // Ensure you have this in receiptData
                paymentMethod: receiptData.paymentMethod,
                fee: receiptData.fee
            };

            // Send data to process.php
            fetch('process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(dataToSend)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("Enrollment and payment successful!");
                    // You can add additional logic here, like redirecting or showing a success message
                } else {
                    console.error("Error:", data.message);
                    // Handle error (e.g., show an error message to the user)
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                // Handle fetch error (e.g., show an error message to the user)
            });
        });

        // Cancel Payment
        document.getElementById("cancelPayment").addEventListener("click", function() {
            document.getElementById("overlay").classList.remove('show');
            document.getElementById("paymentOverlay").classList.remove('show');
        });

        // Close Receipt Overlay
        document.getElementById("closeReceipt").addEventListener("click", function() {
            document.getElementById("receiptOverlay").classList.remove('show');
            location.reload(); // Optionally reload the page to reset the form
        });
        
        document.getElementById('closeReceipt').addEventListener('click', function() {
            // Get the course_id from the button's data attribute
            var courseId = this.getAttribute('data-course-id');
            
            // Redirect to get_course_subjects.php with the course_id as a query parameter
            window.location.href = 'course_details.php?course=' + course_name;
        });

    </script>
</body>
</html>