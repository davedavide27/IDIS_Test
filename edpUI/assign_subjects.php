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

// Fetch all instructors
$instructors = [];
$sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}

// Fetch all available subjects
$subjects = [];
$sql = "SELECT subject_code, subject_name FROM subject";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// If the form is submitted for assigning subjects
if (isset($_POST['assign_subjects'])) {
    $selectedInstructorID = $_POST['instructor_id'];
    $selectedSubjects = isset($_POST['subjects']) ? $_POST['subjects'] : [];
    $errorMessages = [];
    $successMessages = [];
    $errorsOccured = false;
    $unassignedSubjects = []; // Keep track of unassigned subjects

    // Check if instructor_id is set
    if (empty($selectedInstructorID)) {
        $errorMessages[] = "Please select an instructor.";
        $errorsOccured = true;
    }

    // Check that subjects are selected
    if (empty($selectedSubjects)) {
        $errorMessages[] = "Please select at least one subject.";
        $errorsOccured = true;
    }

    // If no errors have occurred so far, check for subject assignment
    if (!$errorsOccured) {
        foreach ($selectedSubjects as $subject_code) {
            // Check if the subject is already assigned to an instructor
            $stmt = $conn->prepare("SELECT instructor_ID FROM subject WHERE subject_code = ?");
            $stmt->bind_param("s", $subject_code);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if (!is_null($row['instructor_ID'])) {
                // Subject is already assigned to an instructor
                $errorMessages[] = "Subject " . htmlspecialchars($subject_code) . " is already assigned to an instructor.";
                $errorsOccured = true;
            } else {
                // Subject is unassigned, keep it selected
                $unassignedSubjects[] = $subject_code;
            }

            $stmt->close();
        }

        // Proceed with assignment if no errors were found
        if (!$errorsOccured) {
            foreach ($selectedSubjects as $subject_code) {
                $assignSubjects = $conn->prepare("UPDATE subject SET instructor_ID = ? WHERE subject_code = ?");
                $assignSubjects->bind_param("is", $selectedInstructorID, $subject_code);

                if ($assignSubjects->execute()) {
                    $successMessages[] = "Subject " . htmlspecialchars($subject_code) . " successfully assigned.";
                } else {
                    $errorMessages[] = "Failed to assign instructor to subject " . htmlspecialchars($subject_code) . ".";
                    $errorsOccured = true;
                }

                $assignSubjects->close();
            }
        }
    }

    // Display success and error messages
    if (!$errorsOccured) {
        $_SESSION['success_message'] = implode("<br>", $successMessages);
        unset($_SESSION['form_data']);  // Clear form data after successful submission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = implode("<br>", $errorMessages);
        // Save form data with only unassigned subjects remaining selected
        $_SESSION['form_data'] = $_POST;
        $_SESSION['form_data']['subjects'] = $unassignedSubjects; // Keep only unassigned subjects selected
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="select.css">
    <title>Assign Subjects</title>
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
        }

        .search-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search-container select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .subject-list div {
            margin-bottom: 10px;
        }

        .subject-list label {
            margin-left: 10px;
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
        <!-- Success Messages Container -->
        <div class="notification-container" id="success-container">
            <?php if (isset($_SESSION['success_message'])):
                $successes = explode("<br>", $_SESSION['success_message']); // Handle multiple success messages
                foreach ($successes as $success): ?>
                    <div class="notification success">
                        <span><?php echo $success; ?></span>
                        <span class="notification-close">&times;</span>
                    </div>
                <?php endforeach; ?>
                <button class="clear-all-button" id="clearAllSuccessButton">[ clear all ]</button>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
        </div>

        <!-- Error Messages Container -->
        <div class="notification-container" id="error-container">
            <?php if (isset($_SESSION['error_message'])): ?>
                <?php 
                $errors = explode("<br>", $_SESSION['error_message']);
                foreach ($errors as $error): ?>
                    <div class="notification error">
                        <span><?php echo $error; ?></span>
                        <span class="notification-close">&times;</span>
                    </div>
                <?php endforeach; ?>
                <button class="clear-all-button" id="clearAllErrorButton">[ clear all ]</button>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>

        <!-- Back Button at the top of the page -->
        <button class="back-button" onclick="window.location.href='index.php';">Back</button>

        <h3>Assign Subjects to Instructor</h3>

        <form method="post" action="">
            <input type="hidden" name="assign_subjects" value="1">

            <!-- Search Bar for Instructor Selection -->
            <div class="search-container">
                <h4>Select Instructor</h4>
                <input type="text" id="searchInstructor" onkeyup="filterInstructors()" placeholder="Search for instructor..">
                <select id="instructorSelectAssign" name="instructor_id" onchange="updateSearchInstructor()" required>
                    <option value="">Select an Instructor</option>
                    <?php foreach ($instructors as $instructor):
                        $fullName = trim($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']);
                        $selected = (isset($_SESSION['form_data']['instructor_id']) && $_SESSION['form_data']['instructor_id'] == $instructor['instructor_ID']) ? 'selected' : '';  // Preserve instructor selection
                    ?>
                        <option value="<?php echo htmlspecialchars($instructor['instructor_ID']); ?>" data-fullname="<?php echo htmlspecialchars($fullName); ?>" <?php echo $selected; ?>>
                            <?php echo htmlspecialchars($fullName); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h4>Select Subjects</h4>
            <div class="check-all">
                <input type="checkbox" id="checkAll" onclick="toggleCheckAll(this)">
                <label for="checkAll">Select/Deselect All Subjects</label>
            </div>

            <div class="subject-list">
                <?php foreach ($subjects as $subject):
                    $checked = (isset($_SESSION['form_data']['subjects']) && in_array($subject['subject_code'], $_SESSION['form_data']['subjects'])) ? 'checked' : '';  // Preserve subject selection
                ?>
                    <div>
                        <input type="checkbox" name="subjects[]" value="<?php echo htmlspecialchars($subject['subject_code']); ?>" id="subject_<?php echo htmlspecialchars($subject['subject_code']); ?>" <?php echo $checked; ?>>
                        <label for="subject_<?php echo htmlspecialchars($subject['subject_code']); ?>">
                            <?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="submit">Assign Subjects</button>
        </form>
    </div>

</body>
<script>
    // Function to toggle select/deselect all checkboxes for subjects
    function toggleCheckAll(source) {
        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = source.checked;
        });
    }

    // Function to filter instructors based on input
    function filterInstructors() {
        const input = document.getElementById('searchInstructor');
        const filter = input.value.toLowerCase();
        const select = document.getElementById('instructorSelectAssign');

        for (let i = 0; i < select.options.length; i++) {
            const txtValue = select.options[i].textContent || select.options[i].innerText;
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                select.options[i].style.display = "";
            } else {
                select.options[i].style.display = "none";
            }
        }
    }

    // Function to update the search input with the selected instructor's full name
    function updateSearchInstructor() {
        const select = document.getElementById('instructorSelectAssign');
        const selectedOption = select.options[select.selectedIndex];

        // Get the full name from the selected option's data attribute
        const fullName = selectedOption.getAttribute('data-fullname');

        // Update the search input with the selected instructor's full name
        document.getElementById('searchInstructor').value = fullName;
    }

    // Function to remove notifications, with optional fade-out effect
    function removeNotification(notification, immediate = false) {
        if (immediate) {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();

                const remainingNotifications = document.querySelectorAll('.notification');
                if (remainingNotifications.length === 0) {
                    const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
                    const clearAllErrorButton = document.getElementById('clearAllErrorButton');
                    if (clearAllSuccessButton) clearAllSuccessButton.style.display = 'none';
                    if (clearAllErrorButton) clearAllErrorButton.style.display = 'none';
                }
            }, 500); 
        } else {
            notification.classList.add('fade-out');
            setTimeout(() => {
                notification.remove();

                const remainingNotifications = document.querySelectorAll('.notification');
                if (remainingNotifications.length === 0) {
                    const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
                    const clearAllErrorButton = document.getElementById('clearAllErrorButton');
                    if (clearAllSuccessButton) clearAllSuccessButton.style.display = 'none';
                    if (clearAllErrorButton) clearAllErrorButton.style.display = 'none';
                }
            }, 500); 
        }
    }

    // Function to show notifications with automatic closure after 4 seconds
    function showNotifications() {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach((notification) => {
            setTimeout(() => {
                removeNotification(notification);
            }, 4000);

            notification.querySelector('.notification-close').addEventListener('click', () => {
                removeNotification(notification);
            });
        });
    }

    // Clear all notifications at once with fade-out animation
    const clearAllSuccessButton = document.getElementById('clearAllSuccessButton');
    const clearAllErrorButton = document.getElementById('clearAllErrorButton');

    if (clearAllSuccessButton) { 
        clearAllSuccessButton.addEventListener('click', function() {
            const notifications = document.querySelectorAll('.notification.success');
            notifications.forEach((notification, index) => {
                setTimeout(() => {
                    removeNotification(notification, true);
                }, index * 100); 
            });
            clearAllSuccessButton.style.display = 'none';
        });
    }

    if (clearAllErrorButton) { 
        clearAllErrorButton.addEventListener('click', function() {
            const notifications = document.querySelectorAll('.notification.error');
            notifications.forEach((notification, index) => {
                setTimeout(() => {
                    removeNotification(notification, true);
                }, index * 100); 
            });
            clearAllErrorButton.style.display = 'none';
        });
    }

    document.addEventListener('DOMContentLoaded', showNotifications);
</script>

</html>
