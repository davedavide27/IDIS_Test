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
    <link rel="stylesheet" href="modal.css">
    <script src="student.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        * {
            margin: 0%;
            font-family: 'Montserrat', sans-serif;

        }

        .logout-message {
            display: none;
            color: green;
            font-weight: bold;
        }

        /* Highlighted subject button */
        .selected-subject {
            background-color: #1e90ff;
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
            overflow: hidden; 
        }

        .logout_btn {
            display: block;
            margin: auto;
            margin-top: 240px;
            padding: 7px 20px;
            border-radius: 5px;
            border-style: none;
            background: #1e90ff;
            color: fff;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            -webkit-transform-duration: 0.3s;
            transition-duration: 0.3s;
        }

        .logout_btn:hover,
        .logout_btn:focus,
        .logout_btn:active {
            box-shadow: 0 0 20px rgba (0, 0, 0, 0.5);
            -webkit-transform: scale(1.1);
            transform: scale(1.1);
        }

        .subjects button {
            text-align: center;
            margin-bottom: 15px;
            width: 100pt;
            border-radius: 5px;
            padding: 8px;
            border-style: none;
        }

        ul {
            font-weight: 900;
            text-align: left;
        }

        main {
            background-color: burlywood;
            width: 100%;
            border-bottom-right-radius: 10pt;
            box-shadow: 10px 20px 20px;
        }

        h6 {
            font-size: 1rem;
        }

        h4 {
            margin-bottom: 10px;
        }

        .navtab button {
            background-color: goldenrod;
            border: none;
            margin-right: 1pt;
            margin-top: 30pt;
            height: 20pt;
            box-shadow: -1pt -1pt 15pt 1pt;
            font-weight: 800;
            font-size: 1rem;
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

                    <!-- Instructor Assigned will initially be empty -->
                    <ul id="assignedInstructor">Instructor Assigned: <span id="instructorName">N/A</span></ul>
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
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                        <span style="display: none;">
                                            (<?php echo htmlspecialchars($subject['subject_code']); ?>)
                                        </span>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No subjects assigned yet.</p>
                        <?php endif; ?>

                    </div>
                </div>
                <form action="../logout.php" method="post">
                    <button class="logout_btn" type="submit">Logout</button>
                </form>
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
                        <br>
                        <div id="Topics" class="tabcontent">
                            <h6>Rate if the topics are being discussed clearly from 1 to 5.</h6>
                            <button class="legend-button" onclick="openModal()">Legend</button>
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
                        </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal structure -->
    <div id="legendModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div class="legend-container">
                <p class="legend-title">Legend:</p>

                <div class="rating-level rating-1">
                    1 - Very Poor
                    <p class="rating-description">
                        The delivery did not align with the course outline; major topics were missing or poorly covered.
                    </p>
                </div>

                <div class="rating-level rating-2">
                    2 - Poor
                    <p class="rating-description">
                        Some aspects of the outline were covered, but many topics or details were unclear or inadequately addressed.
                    </p>
                </div>

                <div class="rating-level rating-3">
                    3 - Average
                    <p class="rating-description">
                        The course followed the outline, but some areas were not fully explained or well-developed.
                    </p>
                </div>

                <div class="rating-level rating-4">
                    4 - Good
                    <p class="rating-description">
                        The course largely followed the outline, clearly covering key topics and expectations.
                    </p>
                </div>

                <div class="rating-level rating-5">
                    5 - Excellent
                    <p class="rating-description">
                        The course fully followed the outline, providing clear explanations and covering all key topics as expected.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    // Function to open the modal
    function openModal() {
        const modal = document.getElementById("legendModal");
        modal.style.display = "block";
        modal.classList.remove("fade-out"); // Remove fade-out effect if present
    }

    // Function to close the modal with fade-out animation
    function closeModal() {
        const modal = document.getElementById("legendModal");
        modal.classList.add("fade-out"); // Apply fade-out effect
        setTimeout(() => {
            modal.style.display = "none";
        }, 300); // Match with fadeOut animation duration
    }

</script>

</html>