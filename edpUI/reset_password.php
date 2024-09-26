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

// Check if form has been submitted
if (isset($_POST['reset_password'])) {
    $type = $_POST['account_type'];
    $account_id = $_POST['account_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ensure passwords match
    if ($new_password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Check whether it's for a student or instructor
        if ($type == "student") {
            // Check old password
            $sql = "SELECT password FROM student WHERE student_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $stmt->bind_result($current_password);
            $stmt->fetch();
            $stmt->close();

            if ($current_password != $old_password) {
                $message = "Old password is incorrect.";
            } else {
                // Update the new password for the student
                $sql = "UPDATE student SET password = ? WHERE student_ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_password, $account_id);
                if ($stmt->execute()) {
                    $message = "Student password reset.";
                } else {
                    $message = "Error resetting student password.";
                }
                $stmt->close();
            }
        } elseif ($type == "instructor") {
            // Check old password
            $sql = "SELECT password FROM instructor WHERE instructor_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
            $stmt->bind_result($current_password);
            $stmt->fetch();
            $stmt->close();

            if ($current_password != $old_password) {
                $message = "Old password is incorrect.";
            } else {
                // Update the new password for the instructor
                $sql = "UPDATE instructor SET password = ? WHERE instructor_ID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $new_password, $account_id);
                if ($stmt->execute()) {
                    $message = "Instructor password reset successfully.";
                } else {
                    $message = "Error resetting instructor password.";
                }
                $stmt->close();
            }
        } else {
            $message = "Please select a valid account type.";
        }
    }

    // Store the message in the session and redirect back to form page
    $_SESSION['message'] = $message;
    header("Location: reset_password.php");
    exit();
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
            margin: 20px auto;
            padding: 20px;
            max-width: 800px;
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
            width: 25%;
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
        <select id="user_type" onchange="toggleForm()">
            <option value="">--Select--</option>
            <option value="student">Student</option>
            <option value="instructor">Instructor</option>
        </select>

        <!-- Reset Password for Student -->
        <form id="student_form" class="form-section" method="post" action="reset_password.php">
            <h4>Reset Student Password</h4>
            <input type="hidden" name="account_type" value="student">

            <label for="account_id">Student ID:</label>
            <input type="number" name="account_id" required>

            <label for="old_password">Old Password:</label>
            <input type="password" name="old_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="reset_password">Reset Student Password</button>
        </form>

        <!-- Reset Password for Instructor -->
        <form id="instructor_form" class="form-section" method="post" action="reset_password.php">
            <h4>Reset Instructor Password</h4>
            <input type="hidden" name="account_type" value="instructor">

            <label for="account_id">Instructor ID:</label>
            <input type="number" name="account_id" required>

            <label for="old_password">Old Password:</label>
            <input type="password" name="old_password" required>

            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>

            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit" name="reset_password">Reset Instructor Password</button>
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
        document.addEventListener('DOMContentLoaded', function () {
            const notification = document.querySelector('.notification');
            const clearAllButton = document.getElementById('clearAllButton');

            if (clearAllButton) {
                clearAllButton.addEventListener('click', function () {
                    notification.remove();
                    clearAllButton.style.display = 'none';
                });
            }

            if (notification) {
                setTimeout(function () {
                    notification.classList.add('fade-out');
                    setTimeout(function () {
                        notification.remove();
                    }, 500);
                }, 4000);
            }
        });
    </script>

</body>

</html>
