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
$instructorFullName = ''; // To store the full name of the instructor for the selected subject

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

    // Fetch assigned subjects and their corresponding instructors for the logged-in student
    $sql = "SELECT subject.subject_name, subject.subject_code, 
                   instructor.instructor_fname, instructor.instructor_mname, instructor.instructor_lname
            FROM student_subject
            JOIN subject ON student_subject.subject_code = subject.subject_code
            JOIN instructor ON subject.instructor_ID = instructor.instructor_ID
            WHERE student_subject.student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // Store subject and instructor details
        $assignedSubjects[] = [
            'subject_name' => $row['subject_name'],
            'subject_code' => $row['subject_code'],
            'instructor_fullname' => $row['instructor_fname'] . ' ' . $row['instructor_mname'] . ' ' . $row['instructor_lname']
        ];
    }

    // Set the instructor of the first subject by default (if applicable)
    if (!empty($assignedSubjects)) {
        $instructorFullName = $assignedSubjects[0]['instructor_fullname'];
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
    <script src="student.js"></script>
    <style>
        .logout-message {
            display: none;
            color: green;
            font-weight: bold;
        }

        /* Highlighted subject button */
        .selected-subject {
            background-color: #FF0000;
            color: white;
        }

        /* No data message */
        .no-data-message {
            text-align: center;
            color: gray;
            font-size: 16px;
        }

        #containerAll {
            max-height: 420pt;
            /* Adjust height to control scrollable area */
            overflow-y: auto;
            /* Allow scrolling when content overflows */
            padding: 10px;
            margin-bottom: 20px;
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
                <script src="student.js"></script>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($studentFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($studentId); ?></ul>
                    <ul id="assignedInstructor">Instructor Assigned: <?php echo htmlspecialchars($assignedSubjects[0]['instructor_fullname']); ?></ul>

                </div>
                <div>
                    <button onclick="location.href='../logout.php';" class="logout-button">Logout</button>
                    <p id="logoutMessage" class="logout-message"></p>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div>
                            <h4>Assigned Subjects:</h4>
                        </div>

                        <!-- Loop through assigned subjects and display them with assigned instructor -->
                        <?php if (!empty($assignedSubjects)): ?>
                            <?php foreach ($assignedSubjects as $subject): ?>
                                <div class="btnSubjects">
                                    <!-- Pass the instructor's full name to the selectSubject function -->
                                    <button onclick="selectSubject(this, '<?php echo htmlspecialchars($subject['instructor_fullname']); ?>')">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)
                                    </button>
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
                            <h6><br>Evaluation of Intended Learning Outcomes (ILOs).</h6>
                            <div id="containerAll">
                                <table class="remarksTable">
                                    <thead>
                                        <tr>
                                            <th>Intended Learning Outcomes (ILOs)</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="Topics" class="tabcontent">
                            <h6><br>Rate if the topics are being discussed clearly from 1 to 5.</h6>
                            <div id="containerAll">
                                <table class="remarksTable">
                                    <thead>
                                        <tr>
                                            <th>Course Outlines</th>
                                            <th>Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- This tbody will be dynamically updated by the student.js -->
                                    </tbody>
                                </table>
                            </div>
                </main>
            </div>
        </div>
    </div>


</body>

</html>