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

$chairFullName = '';
$instructors = [];
$selectedInstructorId = null;
$selectedSubjectCode = null;

// Check if the program chair is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'program_chair') {
    $chairId = $_SESSION['user_ID'];

    // Fetch program chair's full name based on the chair_ID
    $sql = "SELECT chair_fname, chair_mname, chair_lname FROM program_chair WHERE chair_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $chairId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $chairFullName = $row['chair_fname'] . ' ' . $row['chair_mname'] . ' ' . $row['chair_lname'];
    } else {
        $chairFullName = 'Unknown Program Chair';
    }
    $stmt->close();

    // Fetch the list of instructors
    $sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDIS Chair UI</title>
    <link rel="stylesheet" href="style.css">
    <script src="chair.js"></script>
    <style>
        /* Highlight selected subject */
        .selected-subject {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }

        /* Default button styling */
        .btnSubjects button,
        .subjectButton {
            margin: 5px;
            padding: 10px;
            width: 100%;
            border: none;
            background-color: #f1f1f1;
            cursor: pointer;
        }

        /* On hover, change button background */
        .btnSubjects button:hover,
        .subjectButton:hover {
            background-color: lightgray;
        }

        /* On selected, highlight the button */
        .subjectButton.selected-subject {
            background-color: red;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="../logo.png" alt="sample logo">
                </div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($chairFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($chairId); ?></ul>
                </div>
                <br><br>
                <h4 style="text-align: center;">Select Instructor</h4>
                <div class="selectIns">
                    <form method="get" action="index.php" id="instructorForm">
                        <select name="instructor_ID" id="showSelect" onchange="fetchSubjects(this.value)">
                            <option value="">Select Instructor:</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_ID']; ?>">
                                    <?php echo htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <br><br>
                <h4 style="text-align: center;">PENDING SUBJECTS</h4>
                <div class="subsContainer">
                    <div id="subjectsList" class="subjects">    
                        <!-- Subjects will be loaded here based on the selected instructor -->
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
                    <nav class="navtab">
                        <button class="tablinks" onclick="openTab(event, 'ILOs')">Plans</button>
                    </nav>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Implement</h6>
                            <div id="containerPlan">
                                <div class="planCard" id="syllabusCard" style="display: none;">
                                    <a href="#" id="syllabusLink">
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
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Function to fetch subjects based on selected instructor
        function fetchSubjects(instructorId) {
            if (!instructorId) {
                document.getElementById('subjectsList').innerHTML = '<p>Please select an instructor.</p>';
                return;
            }

            fetch('fetch_subjects.php?instructor_id=' + instructorId)
                .then(response => response.json())
                .then(data => {
                    let subjectsList = document.getElementById('subjectsList');
                    subjectsList.innerHTML = ''; // Clear previous subjects

                    if (data.error) {
                        // Display the error message if there's an error
                        subjectsList.innerHTML = `<p>${data.error}</p>`;
                    } else if (data.length > 0) {
                        // Loop through the subjects and display them as buttons
                        data.forEach(subject => {
                            let button = document.createElement('button');
                            button.textContent = subject.subject_name;
                            button.className = "subjectButton"; // Adding a common class to dynamically created buttons
                            button.onclick = function() {
                                selectSubject(subject.subject_code, subject.subject_name, button);
                            };
                            subjectsList.appendChild(button);
                        });
                    } else {
                        subjectsList.innerHTML = '<p>No PENDING subjects assigned to this instructor.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching subjects:', error);
                    document.getElementById('subjectsList').innerHTML = '<p>Error fetching subjects.</p>';
                });
        }

        // Function to highlight selected subject and show plan cards
        function selectSubject(subjectCode, subjectName, buttonElement) {
            // Clear the selection from all subject buttons inside subjectsList
            document.querySelectorAll('#subjectsList .subjectButton').forEach(function(button) {
                button.classList.remove('selected-subject'); // Remove the class from previously selected buttons
            });

            // Add the selection to the clicked subject button
            buttonElement.classList.add('selected-subject');

            // Show the Syllabus and Competencies plan cards
            document.getElementById('syllabusCard').style.display = 'block';
            document.getElementById('competenciesCard').style.display = 'block';

            // Set the subject code and name dynamically in the Competencies and Syllabus links
            document.getElementById('competenciesLink').href = `competencies.php?subject_code=${subjectCode}&subject_name=${subjectName}`;
            document.getElementById('syllabusLink').href = `print_syllabus.php?subject_code=${subjectCode}&subject_name=${subjectName}`;
        }
    </script>

</body>

</html>
