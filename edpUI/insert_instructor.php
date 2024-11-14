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

// If the form is submitted to create an instructor
if (isset($_POST['create_instructor'])) {
    $instructor_ID = $_POST['instructor_ID'];  // Capture instructor_ID from the form
    $instructor_fname = $_POST['instructor_fname'];
    $instructor_mname = $_POST['instructor_mname'];
    $instructor_lname = $_POST['instructor_lname'];
    $password = $_POST['password'];  // Store password as plain text
    $confirm_password = $_POST['confirm_password'];  // Store confirm password

    // Check for validation
    if (empty($instructor_ID) || empty($instructor_fname) || empty($instructor_lname) || empty($password) || empty($confirm_password)) {
        $_SESSION['error_message'] = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match!";
    } elseif (strlen($password) < 4 || strlen($password) > 8) {
        $_SESSION['error_message'] = "Password must be between 4 and 8 characters.";
    } else {
        // Check if the instructor ID already exists
        $stmtCheck = $conn->prepare("SELECT instructor_ID FROM instructor WHERE instructor_ID = ?");
        $stmtCheck->bind_param("i", $instructor_ID);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            // Instructor ID already exists
            $_SESSION['error_message'] = "Instructor ID already exists!";
        } else {
            // Insert the instructor into the database
            $stmt = $conn->prepare("INSERT INTO instructor (instructor_ID, instructor_fname, instructor_mname, instructor_lname, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $instructor_ID, $instructor_fname, $instructor_mname, $instructor_lname, $password);

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
    </style>
</head>
<body>
    <div class="containerOfAll">
        <!-- Success and Error Messages -->
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
            <!-- Instructor ID Field -->
            <label for="instructor_ID">Instructor ID:</label>
            <input type="number" name="instructor_ID" required>

            <!-- Instructor Name Fields -->
            <label for="instructor_fname">First Name:</label>
            <input type="text" name="instructor_fname" required>

            <label for="instructor_mname">Middle Name:</label>
            <input type="text" name="instructor_mname">

            <label for="instructor_lname">Last Name:</label>
            <input type="text" name="instructor_lname" required>

            <!-- Password Fields -->
            <label for="password">Password:</label>
            <input type="password" name="password" required id="password">

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" name="confirm_password" required id="confirm_password">

            <!-- Error Message for Password Validation -->
            <span id="password_error" style="color: red; display: none;">Passwords do not match or are not within the required length (4-8 characters).</span>

            <!-- Submit Button -->
            <button type="submit" name="create_instructor" id="submitButton" style="margin: 0 auto; display: block;" disabled>Create Instructor</button>
        </form>
    </div>

</body>
<script>
    // Notification handling logic (similar to the student insert page)
    document.addEventListener('DOMContentLoaded', function() {
        const successContainer = document.getElementById('success-container');
        const errorContainer = document.getElementById('error-container');
        const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
        const clearAllErrorButton = document.getElementById('clearAllErrorButton');
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');
        const createInstructorBtn = document.getElementById('submitButton');
        const passwordError = document.getElementById('password_error');  // Password error message

        function removeNotification(notification) {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();
            }, 500);
        }

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

        if (clearAllSuccessButton) {
            clearAllSuccessButton.addEventListener('click', function() {
                successContainer.querySelectorAll('.notification').forEach(notification => {
                    removeNotification(notification);
                });
                clearAllSuccessButton.style.display = 'none';
            });
        }

        if (clearAllErrorButton) {
            clearAllErrorButton.addEventListener('click', function() {
                errorContainer.querySelectorAll('.notification').forEach(notification => {
                    removeNotification(notification);
                });
                clearAllErrorButton.style.display = 'none';
            });
        }

        // Confirm password validation with error message
        function validatePasswords() {
            if (passwordField.value !== confirmPasswordField.value || passwordField.value.length < 4 || passwordField.value.length > 8) {
                passwordError.style.display = 'inline';  // Show error message
                createInstructorBtn.disabled = true;  // Disable submit button
                return false;
            } else {
                passwordError.style.display = 'none';  // Hide error message
                createInstructorBtn.disabled = false;  // Enable submit button
                return true;
            }
        }

        // Validate on input
        passwordField.addEventListener('input', validatePasswords);
        confirmPasswordField.addEventListener('input', validatePasswords);

        showNotifications();
    });
</script>

</html>
