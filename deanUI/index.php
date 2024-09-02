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
$reports = []; // Array to store reports data

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

    // Fetch the list of subjects
    $sql = "SELECT subject_code, subject_name FROM subject";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    // Fetch the list of instructors
    $sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }

    // Fetch reports related to subjects and competencies
    $sql = "SELECT * FROM reports WHERE department = ?";
    $stmt = $conn->prepare($sql);
    $department = "COLLEGE OF ARTS AND SCIENCES"; // Example department
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    $stmt->close();
} else {
    // Redirect to login if session is not set
    header("Location: login.php");
    exit();
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
                    <ul>Name: <?php echo htmlspecialchars($deanFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($deanId); ?></ul>
                </div>
                <div>

                </div>
                <h4 style="text-align: center;">Select Role</h4>
                <div class="selectIns">
                    <form action="select_role.php" method="post">
                        <select name="role" id="showSelect" onchange="this.form.submit()">
                            <option value="">Select Role</option>
                            <option value="Program Chair">Program Chair</option>
                            <option value="Subject Coordinator">Subject Coordinator</option>
                        </select>
                    </form>
                </div>
                <br><br>
                <h4 style="text-align: center;">Select Instructor</h4>
                <div class="selectIns">
                    <form action="select_instructor.php" method="post">
                        <select name="instructor" id="showSelect" onchange="this.form.submit()">
                            <option value="">Select Instructor</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_ID']; ?>">
                                    <?php echo htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                
                <br><br>
                <h4 style="text-align: center;">Subjects</h4>
                <div class="subsContainer">
                    <div class="subjects">
                        <?php foreach ($subjects as $subject): ?>
                            <div class="btnSubjects">
                                <form action="view_subject.php" method="post">
                                    <button name="subject" value="<?php echo htmlspecialchars($subject['subject_code']); ?>">
                                        <?php echo htmlspecialchars($subject['subject_name']); ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                
                    <form action="../logout.php" method="post">
                        <button class="logout_btn" type="submit">Logout</button>
                    </form>
                </div>
                </div>
            </nav>
            <div class="implementContainer">
                <header><h5>Instructional Delivery Implementation System (IDIS)</h5><p>Saint Michael College of Caraga (SMCC)</p>
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
                                <div class="planCard">
                                    <a href=""><p>Syllabus</p></a>
                                </div>
                                <div class="planCard">
                                    <a href=""><p>Competencies</p></a>
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
                                    <?php foreach ($reports as $report): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($report['competency']) ?></td>
                                            <td><?= htmlspecialchars($report['remarks']) ?></td>
                                            <td><?= htmlspecialchars($report['percentage_implemented']) ?>%</td>
                                            <td><?= htmlspecialchars($report['remarks']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                            </div>
                        </div>
                        <script>
                            function showLogoutMessage(message) {
                                var logoutMessage = document.getElementById('logoutMessage');
                                logoutMessage.textContent = message;
                                logoutMessage.style.display = 'block';
                                setTimeout(function() {
                                    logoutMessage.style.display = 'none';
                                }, 3000);
                            }
                        </script>
                    </div>
                </main>               
            </div>
        </div>
    </div>

    
</body>
</html>
