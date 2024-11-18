<?php
session_start();

// Check if the user is logged in as EDP
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'edp') {
    header("Location: ../login.php");
    exit();
}

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

// Fetch all available courses from the courses table for selection
$courses = [];
$sqlCourses = "SELECT course_id, course FROM courses"; // Fetch courses from 'courses' table
$resultCourses = $conn->query($sqlCourses);
if ($resultCourses->num_rows > 0) {
    while ($row = $resultCourses->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Fetch departments from the department table
$departments = [];
$sqlDepartments = "SELECT department_id, department_name FROM department"; // Fetch department names from the department table
$resultDepartments = $conn->query($sqlDepartments);
if ($resultDepartments->num_rows > 0) {
    while ($row = $resultDepartments->fetch_assoc()) {
        $departments[] = $row;
    }
}

// If the form is submitted to create a student
if (isset($_POST['create_student'])) {
    $student_ID = $_POST['student_ID'];  // Capture student_ID from the form
    $student_fname = $_POST['student_fname'];
    $student_mname = $_POST['student_mname'];
    $student_lname = $_POST['student_lname'];
    $password = $_POST['password'];  // Store password as plain text
    $confirm_password = $_POST['confirm_password'];  // Get the confirm password
    $course_id = $_POST['course'];  // Capture course_id from the form
    $section = $_POST['section'];
    $year_level = $_POST['year_level'];

    // Capture the selected department(s) (as an array of department names)
    $departments_selected = isset($_POST['department']) ? $_POST['department'] : [];

    // If no department is selected, set an error message
    if (empty($departments_selected)) {
        $_SESSION['error_message'] = "At least one department must be selected!";
    } else {
        // Fetch the course name based on the selected course_id
        $sqlCourseName = "SELECT course FROM courses WHERE course_id = ?";
        $stmtCourse = $conn->prepare($sqlCourseName);
        $stmtCourse->bind_param("i", $course_id);
        $stmtCourse->execute();
        $resultCourse = $stmtCourse->get_result();
        $courseName = '';
        if ($resultCourse->num_rows > 0) {
            $courseRow = $resultCourse->fetch_assoc();
            $courseName = $courseRow['course'];
        }
        $stmtCourse->close();

        // Capture the selected department (as a string)
        $department = isset($_POST['department']) ? $_POST['department'] : null;

        // Check for validation
        if (empty($student_ID) || empty($student_fname) || empty($student_lname) || empty($password) || empty($confirm_password) || empty($course_id) || empty($section) || empty($year_level) || empty($department)) {
            $_SESSION['error_message'] = "All fields are required!";
        } elseif ($password !== $confirm_password) {
            $_SESSION['error_message'] = "Passwords do not match!";
        } elseif (strlen($password) < 4 || strlen($password) > 8) {
            $_SESSION['error_message'] = "Password must be between 4 and 8 characters!";
        } else {
            // Check if the student ID already exists
            $stmtCheck = $conn->prepare("SELECT student_ID FROM student WHERE student_ID = ?");
            $stmtCheck->bind_param("i", $student_ID);
            $stmtCheck->execute();
            $stmtCheck->store_result();

            if ($stmtCheck->num_rows > 0) {
                // Student ID already exists
                $_SESSION['error_message'] = "Student ID already exists!";
            } else {
                // Insert the student into the database, including the department
                $stmt = $conn->prepare("INSERT INTO student (student_ID, student_fname, student_mname, student_lname, password, course, section, year_level, department) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssis", $student_ID, $student_fname, $student_mname, $student_lname, $password, $courseName, $section, $year_level, $department);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = "Student successfully created!";
                } else {
                    $_SESSION['error_message'] = "Failed to create student.";
                }
                $stmt->close();
            }
            $stmtCheck->close();
        }
    }
}

// Clear the form data after submission
unset($_SESSION['form_data']);

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="insert_student.css">
    <link rel="stylesheet" href="../style2.css">
    <title>Create Student</title>
    <style>
        .containerOfAll {
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
        }

        h3,
        h4 {
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

        /* Dropdown styling */
        select {
            display: block;
            width: 100%;
            height: 40px;
            padding: 6px 12px;
            font-size: 16px;
            line-height: 1.5;
            color: #495057;
            background-color: #f9f9f9;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        select:hover {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 5px rgba(128, 189, 255, 0.5);
        }

        select:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 8px rgba(128, 189, 255, 0.5);
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        button[disabled] {
            background-color: #ddd;
            cursor: not-allowed;
        }

        .error-message {
            color: red;
            /* Set text color to red */
            font-size: 14px;
            /* Set font size for the error message */
            display: inline-block;
            /* Ensure the span behaves like an inline-block element */
            margin-top: 5px;
            /* Add some space above the message */
        }
    </style>

</head>

<body>
    <div class="containerOfAll">
        <!-- Success and Error Messages -->
        <div class="notification-container" id="success-container">
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="notification success">
                    <span><?php echo $_SESSION['success_message']; ?></span>
                    <span class="notification-close">&times;</span>
                </div>
                <button class="clear-all-button" id="clearAllSuccessButton">[ clear all ]</button>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        </div>

        <div class="notification-container" id="error-container">
            <?php if (isset($_SESSION['error_message'])): ?>
                <div class="notification error">
                    <span><?php echo $_SESSION['error_message']; ?></span>
                    <span class="notification-close">&times;</span>
                </div>
                <button class="clear-all-button" id="clearAllErrorButton">[ clear all ]</button>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>

        <!-- Back Button at the top of the page -->
        <button class="back-button" onclick="window.location.href='index.php';">Back</button>
        <h3>Create Student</h3>

        <form method="post" action="">
            <!-- Student ID Field -->
            <label for="student_ID">Student ID:</label>
            <input type="number" name="student_ID" id="student_ID" value="<?php echo $_SESSION['form_data']['student_ID'] ?? ''; ?>" required>
            <span id="student_id_error" class="error-message"></span> <!-- Error message will be shown here -->
            <br>
            <br>
            <!-- Student Name Fields -->
            <label for="student_fname">First Name:</label>
            <input type="text" name="student_fname" value="<?php echo $_SESSION['form_data']['student_fname'] ?? ''; ?>" required>

            <label for="student_mname">Middle Name:</label>
            <input type="text" name="student_mname" value="<?php echo $_SESSION['form_data']['student_mname'] ?? ''; ?>">

            <label for="student_lname">Last Name:</label>
            <input type="text" name="student_lname" value="<?php echo $_SESSION['form_data']['student_lname'] ?? ''; ?>" required>

            <!-- Password Field -->
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required minlength="4" maxlength="8" onkeyup="validatePassword();" />

            <!-- Confirm Password Field -->
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" id="confirm_password" maxlength="8" required onkeyup="validatePassword();" />

            <p>
                <span id="password_error_student" class="error-message"></span>
            </p>

            <!-- Course Selection -->
            <br><br>
            <h4>Select Course</h4>
            <select name="course" required>
                <option value="">Select a Course</option>
                <?php foreach ($courses as $course): ?>
                    <option value="<?php echo htmlspecialchars($course['course_id']); ?>" <?php echo (isset($_SESSION['form_data']['course']) && $_SESSION['form_data']['course'] == $course['course_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>

            <!-- Section Field -->
            <label for="section">Section:</label>
            <input type="text" name="section" value="<?php echo $_SESSION['form_data']['section'] ?? ''; ?>" required>

            <!-- Year Level Selection -->
            <h4>Select Year Level</h4>
            <select name="year_level" required>
                <option value="1" <?php echo (isset($_SESSION['form_data']['year_level']) && $_SESSION['form_data']['year_level'] == '1') ? 'selected' : ''; ?>>1st Year</option>
                <option value="2" <?php echo (isset($_SESSION['form_data']['year_level']) && $_SESSION['form_data']['year_level'] == '2') ? 'selected' : ''; ?>>2nd Year</option>
                <option value="3" <?php echo (isset($_SESSION['form_data']['year_level']) && $_SESSION['form_data']['year_level'] == '3') ? 'selected' : ''; ?>>3rd Year</option>
                <option value="4" <?php echo (isset($_SESSION['form_data']['year_level']) && $_SESSION['form_data']['year_level'] == '4') ? 'selected' : ''; ?>>4th Year</option>
            </select>
            <br>
            <!-- Department Selection -->
            <h4>Select Department</h4>
            <select name="department" required>
                <option value="">Select a Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo $dept['department_name']; ?>"
                        <?php echo (isset($_SESSION['form_data']['department']) && $_SESSION['form_data']['department'] == $dept['department_name']) ? 'selected' : ''; ?>>
                        <?php echo $dept['department_name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>



            <!-- Submit Button -->
            <button type="submit" name="create_student" id="create_student" class="btn">Create Student</button>
        </form>
    </div>


    <script>
        // Add event listener to dynamically check the student ID
        document.getElementById("student_ID").addEventListener("input", function() {
            var studentID = this.value;
            var errorMessage = document.getElementById("student_id_error");
            var createStudentBtn = document.getElementById("create_student"); // Get the button element

            if (studentID) {
                // Make AJAX request to check if the student ID exists in the database
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "check_student_id.php?student_ID=" + studentID, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText.trim();

                        // Check the response and display an error if the ID exists
                        if (response === "exists") {
                            errorMessage.textContent = "Student ID already exists!";
                            createStudentBtn.disabled = true; // Disable submit button
                        } else {
                            errorMessage.textContent = "";
                            createStudentBtn.disabled = false; // Enable submit button if ID doesn't exist
                        }
                    }
                };
                xhr.send();
            } else {
                errorMessage.textContent = ""; // Clear error message if input is empty
                createStudentBtn.disabled = false; // Enable submit button if input is empty
            }
        });

        // Password validation function for student creation page
        function validatePassword() {
            var passwordField = document.getElementById("password");
            var confirmPasswordField = document.getElementById("confirm_password");
            var errorMessage = document.getElementById("password_error_student");
            var createStudentBtn = document.getElementById("create_student");

            var password = passwordField.value;
            var confirmPassword = confirmPasswordField.value;

            // Regex for password validation: 4-8 characters, 1 uppercase, 1 special character
            const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{4,8}$/;

            // Check if password matches the required pattern
            if (!passwordRegex.test(password)) {
                errorMessage.textContent = "Password must be 4-8 characters, contain 1 uppercase letter and 1 special character.";
                createStudentBtn.disabled = true; // Disable submit button
            } else if (password !== confirmPassword) {
                errorMessage.textContent = "Passwords do not match.";
                createStudentBtn.disabled = true; // Disable submit button
            } else {
                errorMessage.textContent = ""; // Clear error message
                createStudentBtn.disabled = false; // Enable submit button if valid
            }
        }

        // Validate passwords when user types
        document.getElementById("password").addEventListener('input', validatePassword);
        document.getElementById("confirm_password").addEventListener('input', validatePassword);

        document.addEventListener('DOMContentLoaded', function() {
            const successContainer = document.getElementById('success-container');
            const errorContainer = document.getElementById('error-container');
            const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
            const clearAllErrorButton = document.getElementById('clearAllErrorButton');

            // Function to remove notification
            function removeNotification(notification) {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }

            // Show notifications and set timeout to remove them after 5 seconds
            function showNotifications() {
                document.querySelectorAll('.notification').forEach(notification => {
                    setTimeout(() => {
                        removeNotification(notification);
                    }, 5000); // Notifications disappear after 5 seconds

                    notification.querySelector('.notification-close').addEventListener('click', () => {
                        removeNotification(notification);
                    });
                });
            }

            // Clear all notifications in the success container
            if (clearAllSuccessButton) {
                clearAllSuccessButton.addEventListener('click', function() {
                    successContainer.querySelectorAll('.notification').forEach(notification => {
                        removeNotification(notification);
                    });
                    clearAllSuccessButton.style.display = 'none'; // Hide the clear button after clearing
                });
            }

            // Clear all notifications in the error container
            if (clearAllErrorButton) {
                clearAllErrorButton.addEventListener('click', function() {
                    errorContainer.querySelectorAll('.notification').forEach(notification => {
                        removeNotification(notification);
                    });
                    clearAllErrorButton.style.display = 'none'; // Hide the clear button after clearing
                });
            }

            showNotifications();
        });
    </script>


</html>