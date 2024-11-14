<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
$account_exists = true; // Flag to track if the account exists

// Check if form has been submitted
if (isset($_POST['reset_password'])) {
    $type = $_POST['account_type'];
    $account_id = $_POST['account_id'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ensure passwords match
    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (strlen($new_password) < 4 || strlen($new_password) > 8) {
        $message = "Password must be between 4 and 8 characters long.";
    } else {
        // Check whether it's for a student or instructor
        if ($type == "student") {
            // Check if student exists
            $sql = "SELECT * FROM student WHERE student_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Fetch student data and update the password
                $row = $result->fetch_assoc();
                $student_name = $row['student_fname'] . " " . $row['student_lname'];

                // Update the new password for the student
                $sql = "UPDATE student SET password = ? WHERE student_ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_password, $account_id);
                if ($stmt->execute()) {
                    $message = "Student password reset successfully.";
                } else {
                    $message = "Error resetting student password.";
                }

                // Set the student name in the field
                $_SESSION['student_name'] = $student_name;
            } else {
                // Account not found, display this in the name field
                $_SESSION['student_name'] = "Account not found";
                $message = "Student account not found.";
                $account_exists = false;
            }
            $stmt->close();
        } elseif ($type == "instructor") {
            // Check if instructor exists
            $sql = "SELECT * FROM instructor WHERE instructor_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Fetch instructor data and update the password
                $row = $result->fetch_assoc();
                $instructor_name = $row['instructor_fname'] . " " . $row['instructor_lname'];

                // Update the new password for the instructor
                $sql = "UPDATE instructor SET password = ? WHERE instructor_ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_password, $account_id);
                if ($stmt->execute()) {
                    $message = "Instructor password reset successfully.";
                } else {
                    $message = "Error resetting instructor password.";
                }

                // Set the instructor name in the field
                $_SESSION['instructor_name'] = $instructor_name;
            } else {
                // Account not found, display this in the name field
                $_SESSION['instructor_name'] = "Account not found";
                $message = "Instructor account not found.";
                $account_exists = false;
            }
            $stmt->close();
        }
    }

    // Store the message in the session
    $_SESSION['message'] = $message;
    if ($account_exists) {
        header("Location: reset_password.php");
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="insert_student.css">
    <link rel="stylesheet" href="../style2.css">
    <title>Reset Password</title>
    <style>
        .containerOfAll {
            justify-content: center;
            padding: 20px;
            height: 670px auto;
            width: 520px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }

        h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.25rem;
        }

        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            max-width: 300px;
        }

        .notification {
            display: flex;
            justify-content: space-between;
            background-color: #444;
            color: white;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        .notification.fade-out {
            opacity: 0;
        }

        .notification-close {
            cursor: pointer;
            margin-left: 15px;
            font-weight: bold;
        }

        .clear-all-button {
            display: block;
            background-color: #444;
            color: white;
            padding: 10px;
            margin-top: 10px;
            text-align: center;
            border-radius: 5px;
            cursor: pointer;
        }

        .clear-all-button:hover {
            background-color: #333;
        }

        .back-button {
            background-color: #f44336;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            top: 10px;
            left: 20px;
            width: 80px;
        }

        .back-button:hover {
            background-color: #d32f2f;
        }

        /* Hide forms initially */
        .form-section {
            display: none;
        }

        label {
            margin-top: 10px;
        }

        .containerOfAll select {
            width: 100%;
            height: 40px;
            margin-top: 10px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
            border: 1px solid #ccc;

        }

        .name-display {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            box-sizing: border-box;
            font-size: 14px;
            color: #666;
            background-color: #f1f1f1;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: not-allowed;
        }

        #instructor_name_display,
        #student_name_display {
            font-weight: bold;
        }

        /* Styling for error message */
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }

        /* Styling for invalid ID input */
        .id-input.error {
            border: 2px solid red;
            box-shadow: 0 0 5px red;
        }

        /* Styling for disabled reset button */
        button[disabled] {
            background-color: #ddd;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <div class="containerOfAll">
        <!-- Success and Error Messages -->
        <div class="notification-container" id="success-container">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="notification <?= strpos($_SESSION['message'], 'success') !== false ? 'success' : '' ?>">
                    <span><?php echo $_SESSION['message']; ?></span>
                    <span class="notification-close">&times;</span>
                </div>
                <button class="clear-all-button" id="clearAllButton">[ clear all ]</button>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>
        </div>

        <!-- Back Button -->
        <button class="back-button" onclick="window.location.href='index.php';">Back</button>
        <h3>Reset Password</h3>

        <!-- User Type Selection -->
        <label for="user_type">Select User Type:</label>
        <br>
        <select id="user_type" onchange="toggleForm()">
            <option value="">--Select--</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
        </select>

        <!-- Reset Password for Student -->
        <form id="student_form" class="form-section" method="post" action="reset_password.php">
            <h4>Reset Student Password</h4>
            <input type="hidden" name="account_type" value="student">

            <label for="student_name_display">Student Name:</label>
            <input type="text" id="student_name_display" readonly class="name-display" value="<?php echo isset($_SESSION['student_name']) ? $_SESSION['student_name'] : ''; ?>">

            <label for="student_id">Student ID:</label>
            <input type="number" name="account_id" id="student_id" required oninput="fetchName('student')" class="id-input">

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="reset_password" id="student_reset_button" disabled>Reset Student Password</button>
        </form>

        <!-- Reset Password for Instructor -->
        <form id="instructor_form" class="form-section" method="post" action="reset_password.php">
            <h4>Reset Instructor Password</h4>
            <input type="hidden" name="account_type" value="instructor">

            <label for="instructor_name_display">Instructor Name:</label>
            <input type="text" id="instructor_name_display" readonly class="name-display" value="<?php echo isset($_SESSION['instructor_name']) ? $_SESSION['instructor_name'] : ''; ?>">

            <label for="instructor_id">Instructor ID:</label>
            <input type="number" name="account_id" id="instructor_id" required oninput="fetchName('instructor')" class="id-input">

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="reset_password" id="instructor_reset_button" disabled>Reset Instructor Password</button>
        </form>

    </div>

    <script>
        // Toggle between student and instructor forms based on selection
        function toggleForm() {
            const userType = document.getElementById("user_type").value;
            const studentForm = document.getElementById("student_form");
            const instructorForm = document.getElementById("instructor_form");

            // Hide both forms initially
            studentForm.style.display = "none";
            instructorForm.style.display = "none";

            // Show the appropriate form based on the selected user type
            if (userType === "student") {
                studentForm.style.display = "block";
            } else if (userType === "instructor") {
                instructorForm.style.display = "block";
            }
        }

        // Notification handling logic
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.notification');
            const clearAllButton = document.getElementById('clearAllButton');

            if (clearAllButton) {
                clearAllButton.addEventListener('click', function() {
                    notification.remove();
                    clearAllButton.style.display = 'none';
                });
            }

            if (notification) {
                setTimeout(function() {
                    notification.classList.add('fade-out');
                    setTimeout(function() {
                        notification.remove();
                    }, 500);
                }, 4000);
            }
        });

        function fetchName(accountType) {
            let accountIdField = document.getElementById(accountType === 'student' ? 'student_id' : 'instructor_id');
            let nameDisplayField = document.getElementById(accountType === 'student' ? 'student_name_display' : 'instructor_name_display');
            let resetButton = document.getElementById(accountType === 'student' ? 'student_reset_button' : 'instructor_reset_button');

            // Clear previous error styles
            nameDisplayField.style.color = '';  // Reset to default color
            accountIdField.classList.remove('error');

            if (accountIdField.value) {
                fetch(`get_name.php?account_type=${accountType}&account_id=${accountIdField.value}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data === "Account not found") {
                            // Show error inside the name field and change color to red
                            nameDisplayField.value = accountType === 'student' ? 'Student not found!' : 'Instructor not found!';
                            nameDisplayField.style.color = 'red';  // Make the text red
                            resetButton.disabled = true; // Disable reset button
                        } else {
                            // Display name if found
                            nameDisplayField.value = data;
                            nameDisplayField.style.color = ''; // Reset the color to default
                            resetButton.disabled = false; // Enable reset button
                        }
                    });
            } else {
                nameDisplayField.value = '';
                nameDisplayField.style.color = ''; // Reset the color
                resetButton.disabled = true; // Disable reset button if input is empty
            }
        }
    </script>
</body>
