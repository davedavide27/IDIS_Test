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

$deanFullName = '';
$subjects = [];
$instructors = [];
$competencies = [];
$selectedInstructorId = null;
$selectedSubjectCode = null;

// Check if the Dean is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'dean') {
    $deanId = $_SESSION['user_ID'];

    // Fetch Dean's full name based on the Dean ID
    $sql = "SELECT dean_fname, dean_mname, dean_lname FROM dean WHERE dean_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $deanId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $deanFullName = $row['dean_fname'] . ' ' . $row['dean_mname'] . ' ' . $row['dean_lname'];
    } else {
        $deanFullName = 'Unknown Dean';
    }
    $stmt->close();

    // Fetch the list of instructors
    $sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }

    // Check if an instructor is selected and fetch their subjects
    if (isset($_GET['instructor_ID'])) {
        $selectedInstructorId = $_GET['instructor_ID'];

        $sql = "SELECT subject_code, subject_name FROM subject WHERE instructor_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $selectedInstructorId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $subjects[] = $row;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDIS</title>
    <link rel="stylesheet" href="dean.css">
    <script src="dean.js"></script>
</head>

<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="logo.png" alt="sample logo">
                </div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($deanFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($deanId); ?></ul>
                </div>
                <br><br>
                <h4 style="text-align: center;">Select Instructor</h4>
                <div class="selectIns">
                    <form method="get" action="">
                        <select name="instructor_ID" id="showSelect" onchange="this.form.submit()">
                            <option value="">Select Instructor:</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_ID']; ?>" <?php echo $selectedInstructorId == $instructor['instructor_ID'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <br><br>
                <?php if (!empty($subjects)): ?>
                    <h4 style="text-align: center;">Assigned Subjects</h4>
                    <div class="subsContainer">
                        <div class="subjects">
                            <?php foreach ($subjects as $subject): ?>
                                <div class="btnSubjects">
                                    <button type="button" data-subject-code="<?php echo htmlspecialchars($subject['subject_code']); ?>" onclick="selectSubject('<?php echo htmlspecialchars($subject['subject_code']); ?>', '<?php echo htmlspecialchars($subject['subject_name']); ?>', this)">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <h4 style="text-align: center;">No subjects assigned to this instructor.</h4>
                <?php endif; ?>
                
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
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">Print plans</button>
                            <button class="tablinks" onclick="openTab(event, 'Topics')">Competencies</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Implement</h6>
                            <div id="container_plans">
                                <div class="planCard" id="syllabusCard" style="display: none;">
                                    <a href="#" id="syllabusLink" onclick="printSyllabus()">
                                        <p>Syllabus</p>
                                    </a>
                                </div>

                                <div class="planCard" id="competenciesCard" style="display: none;">
                                    <a href="competencies.php" id="competenciesLink">
                                        <p>Competencies</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div id="Topics" class="tabcontent">
                            <h6><br>The table below concludes all inputs.</h6>
                            <div id="container_ompe">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Competencies</th>
                                        <th>Teacher's remarks</th>
                                        <th>Students' ratings</th>
                                        <th>Interpretation</th>
                                    </tr>
                                    <?php foreach ($competencies as $competency): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($competency['competency_description']); ?></td>
                                            <td><?php echo htmlspecialchars($competency['remarks']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Function to highlight selected subject and show plan cards
        function selectSubject(subjectCode, subjectName, buttonElement) {
            // Highlight the selected subject button
            document.querySelectorAll('.btnSubjects button').forEach(function(button) {
                button.classList.remove('selected-subject');
            });
            buttonElement.classList.add('selected-subject');

            // Show the Syllabus and Competencies plan cards
            document.getElementById('syllabusCard').style.display = 'block';
            document.getElementById('competenciesCard').style.display = 'block';

            // Set the subject code and name dynamically in the Competencies link
            document.getElementById('competenciesLink').href = `competencies.php?subject_code=${subjectCode}&subject_name=${subjectName}`;
                        // Set the subject code and name dynamically in the Competencies link
            document.getElementById('syllabusLink').href = `print_syllabus.php?subject_code=${subjectCode}&subject_name=${subjectName}`;
        }

        // Function to open a tab
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</body>

</html>