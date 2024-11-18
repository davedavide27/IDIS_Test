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
    <link rel="stylesheet" href="reset_password.css">
    <title>Reset Password</title>
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
            <input type="password" name="new_password" id="new_password_student" maxlength="8" required oninput="validatePassword('student')">

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password_student" maxlength="8" required oninput="validatePassword('student')">

            <p>
                <span id="password_error_student" class="error-message"></span>
            </p>

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
            <input type="password" name="new_password" id="new_password_instructor" maxlength="8" required oninput="validatePassword('instructor')">


            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" id="confirm_password_instructor" maxlength="8" required oninput="validatePassword('instructor')">

            <p>
                <span id="password_error_instructor" class="error-message"></span>
            </p>

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

        // Fetch name and handle display for the student or instructor
        function fetchName(accountType) {
            let accountIdField = document.getElementById(accountType === 'student' ? 'student_id' : 'instructor_id');
            let nameDisplayField = document.getElementById(accountType === 'student' ? 'student_name_display' : 'instructor_name_display');
            let resetButton = document.getElementById(accountType === 'student' ? 'student_reset_button' : 'instructor_reset_button');

            // Clear previous error styles
            nameDisplayField.style.color = ''; // Reset to default color
            accountIdField.classList.remove('error');

            if (accountIdField.value) {
                fetch(`get_name.php?account_type=${accountType}&account_id=${accountIdField.value}`)
                    .then(response => response.text())
                    .then(data => {
                        if (data === "Account not found") {
                            // Show error inside the name field and change color to red
                            nameDisplayField.value = accountType === 'student' ? 'Student not found!' : 'Instructor not found!';
                            nameDisplayField.style.color = 'red'; // Make the text red
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

        // Validate password complexity and enable/disable reset button
        function validatePassword(formType) {
            // Identify relevant fields for student or instructor form
            const passwordField = document.getElementById(formType === 'instructor' ? 'new_password_instructor' : 'new_password_student');
            const confirmPasswordField = document.getElementById(formType === 'instructor' ? 'confirm_password_instructor' : 'confirm_password_student');
            const errorSpan = document.getElementById(formType === 'instructor' ? 'password_error_instructor' : 'password_error_student');
            const resetButton = document.getElementById(formType === 'instructor' ? 'instructor_reset_button' : 'student_reset_button');

            const password = passwordField.value;
            const confirmPassword = confirmPasswordField.value;

            // Regex for password validation: 4-8 characters, 1 uppercase, 1 special character
            const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{4,8}$/;

            // Check if password matches the required pattern
            if (!passwordRegex.test(password)) {
                errorSpan.innerText = "Password must be 4-8 characters, contain 1 uppercase letter and 1 special character.";
                resetButton.disabled = true;
            } else if (password !== confirmPassword) {
                errorSpan.innerText = "Passwords do not match.";
                resetButton.disabled = true;
            } else {
                errorSpan.innerText = ""; // Clear error message
                resetButton.disabled = false; // Enable reset button if valid
            }
        }
    </script>

</body>