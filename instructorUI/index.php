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
    <script src="../main.js"></script>
    <style>
        .logout-message {
            display: none;
            color: green;
            font-weight: bold;
        }
        .selected-subject {
            background-color: #FF0000;
            border-color: #badbcc;
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
                <div>
                    <form action="../logout.php" method="post">
                        <button type="submit">Logout</button>
                    </form>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div><h4>Subjects:</h4></div>
                        <?php foreach ($subjects as $subject): ?>
                            <div class="btnSubjects">
                                <button onclick="selectSubject('<?php echo htmlspecialchars($subject['subject_code']); ?>', '<?php echo htmlspecialchars($subject['subject_name']); ?>', this)">
                                    <?php echo htmlspecialchars($subject['subject_name']); ?>
                                </button>
                            </div>
                        <?php endforeach; ?>
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
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">Plans</button>
                            <button class="tablinks" onclick="openTab(event, 'Topics')">Competencies</button>
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
                                    <a href="#"><p>Syllabus</p></a>
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
                          
                        <div id="Topics" class="tabcontent">
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
                                <div class="commentCard">
                                    <div>
                                        <h6>ADGEC 1</h6>
                                    </div>
                                    <div>
                                        <p class="content">/*comments*/</p>
                                    </div>
                                    <div>
                                        <p class="footerTopic">Topic No. 3</p>
                                    </div>
                                </div>
                                <div class="commentCard">
                                    <div>
                                        <h6>ADGEC 1</h6>
                                    </div>
                                    <div>
                                        <p class="content">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ducimus dolores id debitis cum accusamus inventore praesentium sit voluptatum, distinctio dignissimos odio laboriosam, omnis assumenda eos iusto officia aut itaque. Molestias!</p>
                                    </div>
                                    <div>
                                        <p class="footerTopic">Topic No. 2</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>
</html>
