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

$instructorFullName = '';

// Check if the instructor is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'instructor') {
    $instructorId = $_SESSION['user_ID'];

    // Fetch instructor's full name based on the instructor ID
    $sql = "SELECT instructor_fname, instructor_mname, instructor_lname FROM instructor WHERE instructor_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instructorFullName = $row['instructor_fname'] . ' ' . $row['instructor_mname'] . ' ' . $row['instructor_lname'];
    } else {
        $instructorFullName = 'Unknown Instructor';
    }

    $stmt->close();
}

// Fetch subjects for the logged-in instructor
$subjects = [];
$sql = "SELECT subject_code, subject_name FROM subject WHERE instructor_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDIS</title>
    <link rel="stylesheet" href="style.css">
    <script src="instructor.js"></script>
    <style>
        /* Logout Message Styling */
        .logout-message {
            display: none;
            color: #28a745;
            /* Use a consistent green color */
            font-weight: bold;
        }

        /* Selected Subject Button Styling */
        .selected-subject {
            background-color: #FF0000;
            /* Add some thickness to the border */
            color: white;
            /* Make text white for better contrast */
            padding: 10px;
            /* Add padding to make it look more like a button */
            border-radius: 5px;
            /* Add border-radius for smooth edges */
        }

        /* Comment Card Styling */
        .commentCard {
            border: 2px solid #ccc;
            /* Light grey border */
            border-radius: 8px;
            /* Slight rounding of corners */
            margin-bottom: 15px;
            /* Increase spacing between cards */
            padding: 15px;
            /* Increase padding inside the card */
            background-color: #f9f9f9;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            /* Add subtle shadow for depth */
            width: 96.5%;
            text-align: left;
            justify-content: left;
            line-height: 1.8;
        }

        /* Comment Card Title Styling */
        .commentCard h6 {
            font-size: 1.2em;
            margin-bottom: 8px;
            /* Increase margin for better separation */
            font-weight: bold;
            /* Make the text bolder */
            color: #333;
            /* Darker color for better readability */
        }

        /* Comment Card Content Styling */
        .commentCard .content {
            font-size: 1em;
            margin-bottom: 15px;
            /* Add more space after content */
            color: #555;
            /* Slightly lighter text color */
        }

        /* Comment Card Footer Topic Styling */
        .commentCard .footerTopic {
            font-size: 0.9em;
            color: #666;
            /* Lighter grey for footers */
            font-style: italic;
            /* Italicized for differentiation */
        }


        #containerComment ,
        #competenciesTable {
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
                <div>
                    <ul>Name: <?php echo htmlspecialchars($instructorFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($instructorId); ?></ul>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div>
                            <h4>Subjects:</h4>
                        </div>
                        <?php foreach ($subjects as $subject): ?>
                            <div class="btnSubjects">
                                <button onclick="selectSubject('<?php echo htmlspecialchars($subject['subject_code']); ?>', '<?php echo htmlspecialchars($subject['subject_name']); ?>', this)">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
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
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">Plans</button>
                            <button class="tablinks" onclick="openTab(event, 'Competencies')">Competencies</button>
                            <button class="tablinks" onclick="openTab(event, 'Comments')">Comments</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Implement</h6>
                            <div id="containerPlan">
                                <!-- Syllabus Plan Card -->
                                <div class="planCard" data-subject-code="">
                                    <form action="edit_insert_syllabus.php" method="post" style="display: block;">
                                        <input type="hidden" name="syllabus_subject_code" id="syllabus_subject_code">
                                        <input type="hidden" name="syllabus_subject_name" id="syllabus_subject_name">
                                        <button type="submit" style="all: unset; cursor: pointer; display: block; width: 100%; height: 100%;">
                                            <p style="text-align: center; margin: 0;">Syllabus</p>
                                        </button>
                                    </form>
                                </div>

                                <!-- Competencies Plan Card -->
                                <div class="planCard" data-subject-code="">
                                    <form action="insert_competencies.php" method="post" style="display: block;">
                                        <input type="hidden" name="subject_code" id="selected_subject_code">
                                        <input type="hidden" name="subject_name" id="selected_subject_name">
                                        <button type="submit" style="all: unset; cursor: pointer; display: block; width: 100%; height: 100%;">
                                            <p style="text-align: center; margin: 0;">Competencies</p>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div id="Competencies" class="tabcontent">
                            <h6><br>Remark check if the competency is implemented.</h6>
                            <div id="container">
                                <table class="remarksTable" id="competenciesTable" data-subject-code="">
                                    <tr>
                                        <th>Competencies</th>
                                        <th>Remarks</th>
                                    </tr>
                                    <tr id="noCompetencies">
                                        <td colspan="2">No competencies found for this subject.</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div id="Comments" class="tabcontent">
                            <h6><br>Pop up Comments / Suggestions</h6>
                            <div id="containerComment" data-subject-code="">
                                <!-- The comments will be dynamically appended here by JavaScript -->
                            </div>
</body>

</html>