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

    // Check for validation
    if (empty($instructor_ID) || empty($instructor_fname) || empty($instructor_lname) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required!";
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
            width: 15%;
        }

        .back-button:hover {
            background-color: #d32f2f;
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

        <form method="post" action="insert_instructor.php">
            <!-- Instructor ID Field -->
            <label for="instructor_ID">Instructor ID:</label>
            <input type="number" name="instructor_ID" value="<?php echo $_SESSION['form_data']['instructor_ID'] ?? ''; ?>" required>

            <!-- Instructor Name Fields -->
            <label for="instructor_fname">First Name:</label>
            <input type="text" name="instructor_fname" value="<?php echo $_SESSION['form_data']['instructor_fname'] ?? ''; ?>" required>

            <label for="instructor_mname">Middle Name:</label>
            <input type="text" name="instructor_mname" value="<?php echo $_SESSION['form_data']['instructor_mname'] ?? ''; ?>">

            <label for="instructor_lname">Last Name:</label>
            <input type="text" name="instructor_lname" value="<?php echo $_SESSION['form_data']['instructor_lname'] ?? ''; ?>" required>

            <!-- Password Field -->
            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit" style="margin: 0 auto; display: block;"name="create_instructor">Create Instructor</button>
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

        showNotifications();
    });
</script>

</html>