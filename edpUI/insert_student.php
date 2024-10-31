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

    // If the form is submitted to create a student
    if (isset($_POST['create_student'])) {
        $student_ID = $_POST['student_ID'];  // Capture student_ID from the form
        $student_fname = $_POST['student_fname'];
        $student_mname = $_POST['student_mname'];
        $student_lname = $_POST['student_lname'];
        $password = $_POST['password'];  // Store password as plain text
        $course_id = $_POST['course'];  // Capture course_id from the form
        $section = $_POST['section'];
        $year_level = $_POST['year_level'];

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

        // Check for validation
        if (empty($student_ID) || empty($student_fname) || empty($student_lname) || empty($password) || empty($course_id) || empty($section) || empty($year_level)) {
            $_SESSION['error_message'] = "All fields are required!";
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
                // Insert the student into the database, including the course name from the courses table
                $stmt = $conn->prepare("INSERT INTO student (student_ID, student_fname, student_mname, student_lname, password, course, section, year_level) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssssi", $student_ID, $student_fname, $student_mname, $student_lname, $password, $courseName, $section, $year_level);

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
                <input type="number" name="student_ID" value="<?php echo $_SESSION['form_data']['student_ID'] ?? ''; ?>" required>

                <!-- Student Name Fields -->
                <label for="student_fname">First Name:</label>
                <input type="text" name="student_fname" value="<?php echo $_SESSION['form_data']['student_fname'] ?? ''; ?>" required>

                <label for="student_mname">Middle Name:</label>
                <input type="text" name="student_mname" value="<?php echo $_SESSION['form_data']['student_mname'] ?? ''; ?>">

                <label for="student_lname">Last Name:</label>
                <input type="text" name="student_lname" value="<?php echo $_SESSION['form_data']['student_lname'] ?? ''; ?>" required>

                <!-- Password Field -->
                <label for="password">Password:</label>
                <input type="password" name="password" required>

                <!-- Course Selection -->
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
                <button type="submit" name="create_student" style="margin: 0 auto; display: block;">Create Student</button>
            </form>
        </div>
    </body>

    <script>
        // Notification handling logic (similar to the original script)
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