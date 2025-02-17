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
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        * {
            margin: 0%;
            font-family: 'Montserrat', sans-serif;

        }

        ul {
            font-weight: 800;
        }

        h4 {
            font-weight: 800;
            margin-bottom: 20px;
        }


        /* Highlight selected subject */
        .selected-subject {
            background-color: #1e90ff;
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
            border-radius: 10px;
        }

        /* On hover, change button background */
        .btnSubjects button:hover,
        .subjectButton:hover {
            background-color: lightgray;
        }

        /* On selected, highlight the button */
        .subjectButton.selected-subject {
            background-color: #1e90ff;
            color: white;
            font-weight: bold;
            border-radius: 10px;
        }

        .logout_btn {
            display: block;
            margin: auto;
            margin-top: 140px;
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

        .main {
            background-color: burlywood;
            width: 100%;
            height: 50%;
            border-bottom-right-radius: 10pt;
            box-shadow: 10px 20px 20px;
        }

        h6 {
            font-size: 1rem;
        }

        .planCard p {
            padding: 5px;
            background-color: #f2bb30;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .selectIns {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
        }

        .search-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 90%;
            max-width: 400px;
            margin-bottom: 10px;
        }

        .search-container h4 {
            margin-bottom: 8px;
            font-size: 1.2em;
            color: #333;
        }

        #searchInstructor {
            width: 89%;
            padding: 8px 12px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
            margin-left: 35px;
        }

        #instructorSelectAssign {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
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
                <br>
                <h4 style="text-align: center;">Select Instructor</h4>
                <div class="selectIns">
                    <form method="get" action="index.php" id="instructorForm">
                        <!-- Search input above the select dropdown -->
                        <input type="text" id="searchInstructor" placeholder="Search Instructor..." onkeyup="filterInstructors()" style="width: 65%; padding: 8px; margin-bottom: 10px;">

                        <!-- Instructor dropdown -->
                        <select name="instructor_ID" id="showSelect" onchange="fetchSubjects(this.value)">
                            <option value="">Select Instructor:</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo $instructor['instructor_ID']; ?>" class="instructor-option">
                                    <?php echo htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>

                <script>
                    // Function to filter instructors based on search input
                    function filterInstructors() {
                        var input, filter, select, options, i, txtValue;
                        input = document.getElementById('searchInstructor');
                        filter = input.value.toUpperCase(); // Convert search input to uppercase for case-insensitive search
                        select = document.getElementById('showSelect');
                        options = select.getElementsByClassName('instructor-option');

                        // Loop through all options and hide those that don't match the search query
                        for (i = 0; i < options.length; i++) {
                            txtValue = options[i].textContent || options[i].innerText;
                            if (txtValue.toUpperCase().indexOf(filter) > -1) {
                                options[i].style.display = ""; // Show matching options
                            } else {
                                options[i].style.display = "none"; // Hide non-matching options
                            }
                        }
                    }
                </script>


                <br>
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
                                <!-- Make the entire card clickable by wrapping the card in the anchor tag -->
                                <a href="#" id="syllabusLink" style="text-decoration: none; color: inherit;">
                                    <div class="planCard" id="syllabusCard" style="display: none;">
                                        <button type="submit" style="all: unset; display: block; width: 100%; height: 10%;">
                                            <input type="hidden" name="syllabus_subject_code" id="syllabus_subject_code">
                                            <input type="hidden" name="syllabus_subject_name" id="syllabus_subject_name">
                                            <p style="text-align: center; margin: 0;">Syllabus</p>
                                            <div style="text-align: justify; font-size: 16px; color: #555; margin: 20px 20px 0; line-height: 1.6;">
                                                <strong style="text-align: center; display: block;">Notes:</strong><br>
                                                <div style="margin-top: 8px;">
                                                    - To avoid miscalculation, ILOs, Course Outlines, & Competencies must be equal.
                                                </div>
                                                <div style="margin-top: 8px;">
                                                    - All inputs must be included when submitting a syllabus.
                                                </div>
                                                <div style="margin-top: 8px;">
                                                    - ILOs, Course Outlines, & Competencies must be unique to each other and not repeated.
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </a>

                                <a href="competencies.php" id="competenciesLink" style="text-decoration: none; color: inherit;">
                                    <div class="planCard" id="competenciesCard" style="display: none;">
                                        <button type="submit" style="all: unset; display: block; width: 100%; height: 10%;">
                                            <input type="hidden" name="subject_code" id="selected_subject_code">
                                            <input type="hidden" name="subject_name" id="selected_subject_name">
                                            <p style="text-align: center; margin: 0;">Competencies</p>
                                            <div style="text-align: justify; font-size: 16px; color: #555; margin: 20px 20px 0; line-height: 1.6;">
                                                <strong style="text-align: center; display: block;">Notes:</strong><br>
                                                <div style="margin-top: 8px;">
                                                    - To avoid miscalculation, ILOs, Course Outlines, & Competencies must be equal.
                                                </div>
                                                <div style="margin-top: 8px;">
                                                    - All inputs must be included when submitting a syllabus.
                                                </div>
                                                <div style="margin-top: 8px;">
                                                    - ILOs, Course Outlines, & Competencies must be unique to each other and not repeated.
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
    <script>
        // JavaScript to control card visibility and linking logic
        document.addEventListener("DOMContentLoaded", function() {
            const syllabusCard = document.getElementById("syllabusCard");
            const competenciesCard = document.getElementById("competenciesCard");
            const syllabusLink = document.getElementById("syllabusLink");

            // Set the href for syllabusLink dynamically if needed
            syllabusLink.href = "print_syllabus.php";

            // Make cards visible
            syllabusCard.style.display = "block";
            competenciesCard.style.display = "block";
        });
    </script>


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