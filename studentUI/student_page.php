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

$studentFullName = '';
$assignedSubjects = []; // Array to store assigned subjects

// Check if the student is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'student') {
    $studentId = $_SESSION['user_ID'];

    // Fetch student's full name based on the student ID
    $sql = "SELECT student_fname, student_mname, student_lname FROM student WHERE student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $studentFullName = $row['student_fname'] . ' ' . $row['student_mname'] . ' ' . $row['student_lname'];
        $_SESSION['user_fullname'] = $studentFullName; // Store the full name in session
    } else {
        $studentFullName = 'Unknown Student';
    }
    $stmt->close();

    // Fetch assigned subjects for the logged-in student from the student_subject table
    $sql = "SELECT subject.subject_name, subject.subject_code 
            FROM student_subject 
            JOIN subject ON student_subject.subject_code = subject.subject_code 
            WHERE student_subject.student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $assignedSubjects[] = $row; // Store assigned subjects
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
    <title>IDIS</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js"></script>
    <style>
        .logout-message {
            display: none;
            color: green;
            font-weight: bold;
        }
        /* Highlighted subject button */
        .selected-subject {
            background-color: #FF0000; /* Red background to indicate selection */
            color: white; /* Change text color */
        }
    </style>
</head>
<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="logo.png" alt="sample logo">
                </div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($studentFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($studentId); ?></ul>
                </div>
                <div>
                    <button onclick="location.href='../logout.php';" class="logout-button">Logout</button>
                    <p id="logoutMessage" class="logout-message"></p>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div><h4>Assigned Subjects:</h4></div>
                        
                        <!-- Loop through assigned subjects and display them -->
                        <?php if (!empty($assignedSubjects)): ?>
                            <?php foreach ($assignedSubjects as $subject): ?>
                                <div class="btnSubjects">
                                    <button onclick="selectSubject(this)"><?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No subjects assigned yet.</p>
                        <?php endif; ?>
                        
                    </div>
                </div>
            </nav>
            <div class="implementContainer">
                <header>
                    <h5>Instructional Delivery Implementation System (IDIS)</h5>
                    <p>Saint Michael College of Caraga (SMCC)</p>
                    <div></div>
                    <div>
                        <nav class="navtab">
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">ILOs</button>
                            <button class="tablinks" onclick="openTab(event, 'Topics')">Topics</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Evaluation to Intended Learning Outcomes.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Intended Learning Outcomes (ILOs)</th>
                                        <th>Comments</th>
                                    </tr>
                                    <tr>
                                        <td> To create simple hello world program (PRELIM) </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                    <tr>
                                        <td> To create simple hello world program (PRELIM) </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                          
                        <div id="Topics" class="tabcontent">
                            <h6><br>Rate if the topics are being discussed clearly from 1 to 5.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Course Outlines</th>
                                        <th>Rating</th>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="1range" id="1"><label for="1">1</label>
                                            <input type="radio" name="1range" id="2"><label for="2">2</label>
                                            <input type="radio" name="1range" id="3"><label for="3">3</label>
                                            <input type="radio" name="1range" id="4"><label for="4">4</label>
                                            <input type="radio" name="1range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="2range" id="1"><label for="1">1</label>
                                            <input type="radio" name="2range" id="2"><label for="2">2</label>
                                            <input type="radio" name="2range" id="3"><label for="3">3</label>
                                            <input type="radio" name="2range" id="4"><label for="4">4</label>
                                            <input type="radio" name="2range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="3range" id="1"><label for="1">1</label>
                                            <input type="radio" name="3range" id="2"><label for="2">2</label>
                                            <input type="radio" name="3range" id="3"><label for="3">3</label>
                                            <input type="radio" name="3range" id="4"><label for="4">4</label>
                                            <input type="radio" name="3range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr class="submitRate">
                                        <td></td>
                                        <td><button>Submit</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                          
                    </div>
                </main>               
            </div>
        </div>
    </div>
    <script>
        // Function to show logout message
        function showLogoutMessage(message) {
            var logoutMessage = document.getElementById('logoutMessage');
            logoutMessage.textContent = message;
            logoutMessage.style.display = 'block';
            setTimeout(function() {
                logoutMessage.style.display = 'none';
            }, 3000);
        }

        // Function to handle subject selection and highlighting the selected button
        function selectSubject(buttonElement) {
            // Remove the 'selected-subject' class from all buttons
            const subjectButtons = document.querySelectorAll('.btnSubjects button');
            subjectButtons.forEach(function(btn) {
                btn.classList.remove('selected-subject');
            });

            // Add the 'selected-subject' class to the clicked button
            buttonElement.classList.add('selected-subject');
        }
    </script>
</body>
</html>
