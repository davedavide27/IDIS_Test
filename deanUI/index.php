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
    <title>IDIS - Dean</title>
    <link rel="stylesheet" href="dean.css">
    <script src="dean.js"></script>
    <style>
        @media print {
            #printButton {
                display: none;
                /* Hide the print button during printing */
            }
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
            margin-left: 28px;
        }

        #instructorSelectAssign {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .tabcontent button {
            display: block;
            margin: auto;
            margin-bottom: 20px;
            padding: 10px 40px;
            border-radius: 5px;
            border-style: none;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            -webkit-transform-duration: 0.3s;
            transition-duration: 0.3s;
        }

        .tabcontent button:hover {
            box-shadow: 0 0 20px rgba (0, 0, 0, 0.5);
            -webkit-transform: scale(1.1);
            transform: scale(1.1);
            background-color: #1e90ff;
        }

        h4 {
            margin-bottom: 10px;
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
                <br><br>
                <h4 style="text-align: center;">Select Instructor</h4>
                <div class="selectIns">
                    <form method="get" action="">
                        <!-- Search Bar for Instructor Filtering -->
                        <input type="text" id="searchInstructor" onkeyup="filterInstructors()" placeholder="Search for instructor..." style="width: 74%; padding: 8px; margin-bottom: 15px; border-radius: 6px">

                        <!-- Dropdown to Select Instructor -->
                        <select name="instructor_ID" id="showSelect" onchange="this.form.submit()">
                            <option value="">Select Instructor:</option>
                            <?php foreach ($instructors as $instructor): ?>
                                <option value="<?php echo htmlspecialchars($instructor['instructor_ID']); ?>" <?php echo isset($selectedInstructorId) && $selectedInstructorId == $instructor['instructor_ID'] ? 'selected' : ''; ?>>
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
                                <!-- Syllabus Plan Card -->
                                <div class="planCard" id="syllabusCard" style="display: none; cursor: pointer;">
                                    <a href="print_syllabus" id="syllabusLink" style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                        <button type="submit" style="all: unset; display: block; width: 100%; height: 10%;">
                                            <input type="hidden" name="syllabus_subject_code" id="syllabus_subject_code">
                                            <input type="hidden" name="syllabus_subject_name" id="syllabus_subject_name">
                                            <p style="text-align: center; margin: 0;">Syllabus</p>
                                            <div style="text-align: justify; font-size: 16px; color: #555; margin: 20px 20px 0; line-height: 1.6;">
                                        </button>
                                    </a>
                                </div>

                                <!-- Competencies Plan Card -->
                                <div class="planCard" id="competenciesCard" style="display: none; cursor: pointer;">
                                    <a href="competencies.php" id="competenciesLink" style="display: block; width: 100%; height: 100%; text-decoration: none; color: inherit;">
                                        <button type="submit" style="all: unset; display: block; width: 100%;">
                                            <input type="hidden" name="subject_code" id="selected_subject_code">
                                            <input type="hidden" name="subject_name" id="selected_subject_name">
                                            <p style="text-align: center; margin: 0;">Competencies</p>
                                        </button>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="Topics" class="tabcontent">
                        <h6><br>The table below concludes all inputs.</h6>
                        <div id="container_ompe">

                            <button id="printButton" onclick="printTable()">Print Table</button>
                            <table class="remarksTable">
                                <thead>
                                    <tr>
                                        <th>Competencies</th>
                                        <th>Teacher's Remarks</th>
                                        <th>Average Student Rating</th>
                                        <th>Interpretation</th>
                                    </tr>
                                </thead>
                                <tbody id="interpretationTableBody">
                                    <!-- Dynamic rows will be inserted here by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
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
            // Set the subject code and name dynamically in the Syllabus link
            document.getElementById('syllabusLink').href = `print_syllabus.php?subject_code=${subjectCode}&subject_name=${subjectName}`;

            // Fetch and display interpretation for the selected subject
            console.log("Calling fetchAndDisplayInterpretation with subjectCode:", subjectCode);
            fetchAndDisplayInterpretation(subjectCode); // Make sure this function is called when a subject is selected
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

        // Function to filter instructors based on input
        function filterInstructors() {
            const input = document.getElementById('searchInstructor').value.toLowerCase();
            const select = document.getElementById('showSelect');

            // Loop through all options in the dropdown
            for (let i = 0; i < select.options.length; i++) {
                const option = select.options[i];
                const fullName = option.text.toLowerCase();

                // Show or hide options based on the input filter
                option.style.display = fullName.includes(input) ? '' : 'none';
            }
        }

        function printTable() {
            // Create a new window
            const printWindow = window.open('', '_blank');

            // Get the table's HTML content
            const tableContent = document.querySelector('#container_ompe').innerHTML;

            // Define the HTML structure for the print window
            printWindow.document.write(`
        <html>
        <head>
            <title>Print Table</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; }
                table { width: 100%; border-collapse: collapse; }
                th, td { padding: 10px; border: 1px solid #ddd; text-align: center; }
                th { background-color: #f2f2f2; }

                /* Hide the Print Table button in the new page */
                #printButton {
                    display: none;
                }

                /* Hide the print button when printing */
                @media print {
                    #printButton {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <h2>Competency Table</h2>
            ${tableContent}
        </body>
        </html>
    `);

            // Close the document after loading the content
            printWindow.document.close();

            // Trigger the print dialog
            printWindow.print();

            // Close the print window after printing or cancelling
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        }
    </script>
</body>

</html>