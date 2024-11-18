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

// Fetch departments from the department table
$departments = [];
$sqlDepartments = "SELECT department_name FROM department"; // Fetch department names only
$resultDepartments = $conn->query($sqlDepartments);
if ($resultDepartments->num_rows > 0) {
    while ($row = $resultDepartments->fetch_assoc()) {
        $departments[] = $row;
    }
}

// If the form is submitted to create an instructor
if (isset($_POST['create_instructor'])) {
    $instructor_ID = trim($_POST['instructor_ID']); // Capture and trim input
    $instructor_fname = trim($_POST['instructor_fname']);
    $instructor_mname = trim($_POST['instructor_mname']);
    $instructor_lname = trim($_POST['instructor_lname']);
    $password = trim($_POST['password']); // Trimmed password
    $confirm_password = trim($_POST['confirm_password']);
    $department_name = trim($_POST['department']); // Capture department name

    // Validate form fields
    if (empty($instructor_ID) || empty($instructor_fname) || empty($instructor_lname) || empty($password) || empty($confirm_password) || empty($department_name)) {
        $_SESSION['error_message'] = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
    } elseif (strlen($password) < 4 || strlen($password) > 8) {
        $_SESSION['error_message'] = "Password must be between 4 and 8 characters.";
    } else {
        // Check if the instructor ID already exists
        $stmtCheck = $conn->prepare("SELECT instructor_ID FROM instructor WHERE instructor_ID = ?");
        $stmtCheck->bind_param("s", $instructor_ID);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $_SESSION['error_message'] = "Instructor ID already exists!";
        } else {
            // Insert the instructor into the database
            $stmt = $conn->prepare(
                "INSERT INTO instructor (instructor_ID, instructor_fname, instructor_mname, instructor_lname, password, department) VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("isssss", $instructor_ID, $instructor_fname, $instructor_mname, $instructor_lname, $password, $department_name);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Instructor successfully created!";
            } else {
                $_SESSION['error_message'] = "Failed to create instructor.";
            }
            $stmt->close();
        }
        $stmtCheck->close();
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
    <title>Create Instructor</title>
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
            ;
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
        <h3>Create Instructor</h3>

        <form method="post" action="insert_instructor.php" onsubmit="return validateForm()">
            <!-- Student ID Field -->
            <label for="instructor_ID">Instructor ID:</label>
            <input type="number" name="instructor_ID" id="instructor_ID" value="<?php echo $_SESSION['form_data']['instructor_ID'] ?? ''; ?>" required>
            <span id="instructor_id_error" class="error-message"></span> <!-- Error message will be shown here -->
            <br>
            <br>

            <!-- Instructor Name Fields -->
            <label for="instructor_fname">First Name:</label>
            <input type="text" name="instructor_fname" required>

            <label for="instructor_mname">Middle Name:</label>
            <input type="text" name="instructor_mname">

            <label for="instructor_lname">Last Name:</label>
            <input type="text" name="instructor_lname" required>


            <!-- Department Selection -->
            <h4>Select Department</h4>
            <select name="department" required>
                <option value="">Select a Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo htmlspecialchars($dept['department_name']); ?>">
                        <?php echo htmlspecialchars($dept['department_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>


            <!-- Password Fields -->
            <label for="password">Password:</label>
            <input type="password" name="password" required id="password" maxlength="8">

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required id="confirm_password" maxlength="8">

            <p>
                <span id="password_error" class="error-message"></span>
            </p>

            <!-- Submit Button -->
            <button type="submit" name="create_instructor" id="submitButton" style="margin: 0 auto; display: block;" disabled>Create Instructor</button>
        </form>
    </div>

    <script>
        // Add event listener to dynamically check the instructor ID
        document.getElementById("instructor_ID").addEventListener("input", function() {
            var instructorID = this.value;
            var errorMessage = document.getElementById("instructor_id_error");
            var createInstructorBtn = document.getElementById("submitButton"); // Get the button element

            if (instructorID) {
                // Make AJAX request to check if the instructor ID exists in the database
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "check_instructor_id.php?instructor_ID=" + instructorID, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText.trim();

                        // Check the response and display an error if the ID exists
                        if (response === "exists") {
                            errorMessage.textContent = "Instructor ID already exists!";
                            createInstructorBtn.disabled = true; // Disable submit button
                        } else {
                            errorMessage.textContent = "";
                            createInstructorBtn.disabled = false; // Enable submit button if ID doesn't exist
                        }
                    }
                };
                xhr.send();
            } else {
                errorMessage.textContent = ""; // Clear error message if input is empty
                createInstructorBtn.disabled = false; // Enable submit button if input is empty
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const successContainer = document.getElementById('success-container');
            const errorContainer = document.getElementById('error-container');
            const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
            const clearAllErrorButton = document.getElementById('clearAllErrorButton');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const createInstructorBtn = document.getElementById('submitButton');
            const passwordError = document.getElementById('password_error'); // Password error message

            // Function to remove notification with fade-out effect
            function removeNotification(notification) {
                notification.classList.add('fade-out');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }

            // Function to handle notifications
            function showNotifications() {
                document.querySelectorAll('.notification').forEach(notification => {
                    setTimeout(() => {
                        removeNotification(notification);
                    }, 4000);

                    notification.querySelector('.notification-close').addEventListener('click', () => {
                        removeNotification(notification);
                    });
                });
            }

            // Clear all success notifications
            if (clearAllSuccessButton) {
                clearAllSuccessButton.addEventListener('click', function() {
                    successContainer.querySelectorAll('.notification').forEach(notification => {
                        removeNotification(notification);
                    });
                    clearAllSuccessButton.style.display = 'none';
                });
            }

            // Clear all error notifications
            if (clearAllErrorButton) {
                clearAllErrorButton.addEventListener('click', function() {
                    errorContainer.querySelectorAll('.notification').forEach(notification => {
                        removeNotification(notification);
                    });
                    clearAllErrorButton.style.display = 'none';
                });
            }

            // Validate password complexity and enable/disable submit button
            function validatePassword() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;

                // Regex for password validation: 4-8 characters, 1 uppercase, 1 special character
                const passwordRegex = /^(?=.*[A-Z])(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{4,8}$/;

                // Check if password matches the required pattern
                if (!passwordRegex.test(password)) {
                    passwordError.innerText = "Password must be 4-8 characters, contain 1 uppercase letter and 1 special character.";
                    createInstructorBtn.disabled = true; // Disable submit button
                } else if (password !== confirmPassword) {
                    passwordError.innerText = "Passwords do not match.";
                    createInstructorBtn.disabled = true; // Disable submit button
                } else {
                    passwordError.innerText = ""; // Clear error message
                    createInstructorBtn.disabled = false; // Enable submit button if valid
                }
            }

            // Validate passwords when user types
            passwordField.addEventListener('input', validatePassword);
            confirmPasswordField.addEventListener('input', validatePassword);

            showNotifications(); // Show notifications on page load
        });
    </script>