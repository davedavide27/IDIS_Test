<?php
session_start();

// Check if the user is logged in as an instructor
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'instructor') {
    header("Location: ../login.php");
    exit();
}

$instructor_ID = $_SESSION['user_ID'];

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

$competencies = [];
$subject_code = "";
$subject_name = "";
$units = "";
$hours = "";
$departments = "COLLEGE OF ARTS AND SCIENCES";
$school_year_start = "";
$school_year_end = "";
$grading_period = "";
$grading_quarter_start = "";
$grading_quarter_end = "";
$total_competencies_deped_tesda_ched = "";
$total_competencies_smcc = "";
$total_institutional_competencies = "";
$total_competencies_b_and_c = "";
$total_competencies_implemented = "";
$total_competencies_not_implemented = "";
$percentage_competencies_implemented = "";
$prepared_by = "";
$checked_by = "";
$noted_by = "";

// Fetch existing competencies for the subject
if (isset($_POST['subject_code'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];

    $sql = "SELECT * FROM competencies WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $competencies[] = $row;
            // Populate form fields with the first entry's data
            $units = $row['units'];
            $hours = $row['hours'];
            $department = $row['department'];
            $school_year_start = $row['school_year_start'];
            $school_year_end = $row['school_year_end'];
            $grading_period = $row['grading_period'];
            $grading_quarter_start = $row['grading_quarter_start'];
            $grading_quarter_end = $row['grading_quarter_end'];
            $total_competencies_deped_tesda_ched = $row['total_competencies_deped_tesda_ched'];
            $total_competencies_smcc = $row['total_competencies_smcc'];
            $total_institutional_competencies = $row['total_institutional_competencies'];
            $total_competencies_b_and_c = $row['total_competencies_b_and_c'];
            $total_competencies_implemented = $row['total_competencies_implemented'];
            $total_competencies_not_implemented = $row['total_competencies_not_implemented'];
            $percentage_competencies_implemented = $row['percentage_competencies_implemented'];
            $prepared_by = $row['prepared_by'];
            $checked_by = $row['checked_by'];
            $noted_by = $row['noted_by'];
        }
    } else {
        // No existing data, setting to create new competency mode
        $competencies = []; // Empty array to indicate no competencies found
    }

    $stmt->close();
}

// Insert/Update the course_outline_ratings table
function updateCourseOutlineRatings($conn, $studentIds, $competency_description, $remarks, $subject_code, $section, $topic)
{
    foreach ($studentIds as $studentId) {
        $stmtCheck = $conn->prepare("SELECT id FROM course_outline_ratings WHERE student_id = ? AND subject_code = ? AND section = ? AND topic = ?");
        $stmtCheck->bind_param("isss", $studentId, $subject_code, $section, $topic);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        if ($resultCheck->num_rows > 0) {
            // Update the existing ratings
            $stmtUpdateRating = $conn->prepare("UPDATE course_outline_ratings SET competency_description = ?, remarks = ? WHERE student_id = ? AND subject_code = ? AND section = ? AND topic = ?");
            $stmtUpdateRating->bind_param("ssisss", $competency_description, $remarks, $studentId, $subject_code, $section, $topic);
            $stmtUpdateRating->execute();
            $stmtUpdateRating->close();
        } else {
            // Insert new ratings
            $defaultRating = 0;
            $stmtInsertRating = $conn->prepare("INSERT INTO course_outline_ratings (student_id, subject_code, section, topic, competency_description, remarks, rating) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmtInsertRating->bind_param("isssssi", $studentId, $subject_code, $section, $topic, $competency_description, $remarks, $defaultRating);
            $stmtInsertRating->execute();
            $stmtInsertRating->close();
        }

        $stmtCheck->close();
    }
}

// Check if the form is submitted for saving edits or adding new competency
if (isset($_POST['save_edits'])) {
    // Update all form fields first
    $stmtUpdateAllFields = $conn->prepare("UPDATE competencies SET units = ?, hours = ?, department = ?, school_year_start = ?, school_year_end = ?, grading_period = ?, grading_quarter_start = ?, grading_quarter_end = ?, total_competencies_deped_tesda_ched = ?, total_competencies_smcc = ?, total_institutional_competencies = ?, total_competencies_b_and_c = ?, total_competencies_implemented = ?, total_competencies_not_implemented = ?, percentage_competencies_implemented = ?, prepared_by = ?, checked_by = ?, noted_by = ? WHERE subject_code = ?");
    $stmtUpdateAllFields->bind_param(
        "iissssssiiiiiiissss",
        $_POST['units'],
        $_POST['hours'],
        $_POST['department'],
        $_POST['school_year_start'],
        $_POST['school_year_end'],
        $_POST['grading_period'],
        $_POST['grading_quarter_start'],
        $_POST['grading_quarter_end'],
        $_POST['total_competencies_deped_tesda_ched'],
        $_POST['total_competencies_smcc'],
        $_POST['total_institutional_competencies'],
        $_POST['total_competencies_b_and_c'],
        $_POST['total_competencies_implemented'],
        $_POST['total_competencies_not_implemented'],
        $_POST['percentage_competencies_implemented'],
        $_POST['prepared_by'],
        $_POST['checked_by'],
        $_POST['noted_by'],
        $_POST['subject_code']
    );
    $stmtUpdateAllFields->execute();
    $stmtUpdateAllFields->close();

    // Prepare the statement for updating existing competencies
    $stmtUpdate = $conn->prepare("UPDATE competencies SET competency_description = ?, remarks = ? WHERE competency_id = ?");
    // Prepare the statement for inserting new competencies
    $stmtInsert = $conn->prepare("INSERT INTO competencies (subject_code, subject_name, competency_description, remarks, units, hours, department, school_year_start, school_year_end, grading_period, grading_quarter_start, grading_quarter_end, total_competencies_deped_tesda_ched, total_competencies_smcc, total_institutional_competencies, total_competencies_b_and_c, total_competencies_implemented, total_competencies_not_implemented, percentage_competencies_implemented, prepared_by, checked_by, noted_by, instructor_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $competency_ids_from_db = [];

    // Ensure there are competencies to process
    if (!empty($_POST['competencies'])) {
        // Fetch all students from the students table
        $sqlStudents = "SELECT student_id FROM student";
        $resultStudents = $conn->query($sqlStudents);
        $studentIds = [];
        while ($row = $resultStudents->fetch_assoc()) {
            $studentIds[] = $row['student_id'];
        }

        // Fetch the sections and topics from the context table for the subject
        $sqlContext = "SELECT section, topics FROM context WHERE subject_code = ?";
        $stmtContext = $conn->prepare($sqlContext);
        $stmtContext->bind_param("s", $_POST['subject_code']);
        $stmtContext->execute();
        $resultContext = $stmtContext->get_result();
        $sectionsTopics = [];
        while ($row = $resultContext->fetch_assoc()) {
            $sectionsTopics[] = $row;  // Each row has 'section' and 'topics'
        }
        $stmtContext->close();

// Handle the case only when inserting new competencies
if (empty($sectionsTopics)) {
    // Only handle this if it's a new competency (not during updates)
    if (empty($_POST['competency_id'])) {
        echo "<script>
                alert('Error: No sections or topics found for the subject when inserting a new competency.');
                window.location.href = 'index.php';
              </script>";
        exit; // Stop further script execution to prevent the insert
    }
} else {
    $totalSections = count($sectionsTopics);
    // Proceed with saving the competency

            // Loop through the competencies arrays
            foreach ($_POST['competencies'] as $index => $competency_description) {
                $remarks = $_POST['remarks'][$index];
                $competency_id = $_POST['competency_id'][$index];

                if (!empty($competency_id)) {
                    // Update existing competency
                    $stmtUpdate->bind_param("ssi", $competency_description, $remarks, $competency_id);
                    $stmtUpdate->execute();
                    $competency_ids_from_db[] = $competency_id;
                } else {
                    // Insert new competency
                    $stmtInsert->bind_param(
                        "sssssiissssssiiiiiiisss",
                        $_POST['subject_code'],
                        $_POST['subject_name'],
                        $competency_description,
                        $remarks,
                        $_POST['units'],
                        $_POST['hours'],
                        $_POST['department'],
                        $_POST['school_year_start'],
                        $_POST['school_year_end'],
                        $_POST['grading_period'],
                        $_POST['grading_quarter_start'],
                        $_POST['grading_quarter_end'],
                        $_POST['total_competencies_deped_tesda_ched'],
                        $_POST['total_competencies_smcc'],
                        $_POST['total_institutional_competencies'],
                        $_POST['total_competencies_b_and_c'],
                        $_POST['total_competencies_implemented'],
                        $_POST['total_competencies_not_implemented'],
                        $_POST['percentage_competencies_implemented'],
                        $_POST['prepared_by'],
                        $_POST['checked_by'],
                        $_POST['noted_by'],
                        $instructor_ID
                    );
                    $stmtInsert->execute();
                    $competency_ids_from_db[] = $stmtInsert->insert_id;
                }

                // Assign each competency to a corresponding topic (e.g., topic 1 gets competency 1, etc.)
                $currentTopic = $sectionsTopics[$index % $totalSections]; // Cycle through topics
                $section = $currentTopic['section'];
                $topic = $currentTopic['topics'];

                // Update the course_outline_ratings for each section and topic
                updateCourseOutlineRatings($conn, $studentIds, $competency_description, $remarks, $_POST['subject_code'], $section, $topic);
            }
        }

        // Delete competencies that are not in the POST request (removed by the user)
        if (!empty($competency_ids_from_db)) {
            $competency_ids_from_db = implode(",", $competency_ids_from_db);
            $stmtDelete = $conn->prepare("DELETE FROM competencies WHERE subject_code = ? AND competency_id NOT IN ($competency_ids_from_db)");
            $stmtDelete->bind_param("s", $_POST['subject_code']);
            $stmtDelete->execute();
            $stmtDelete->close();
        }
    }

    $stmtUpdate->close();
    $stmtInsert->close();

    // Set the session success message
    $_SESSION['success_message'] = "Competencies updated successfully!";
}

// Close the connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../competencies.css">
    <script src="main.js"></script>
    <title>Edit Competencies</title>
<<<<<<< HEAD
    <style>
        @media print {

            .no-print,
            .no-print * {
                display: none !important;
            }
        }

        input,
        select {
            border: none;
            background: transparent;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            outline: none;
            font-weight: bold;
        }

        .back-button button {
            margin: 0;
            height: 25pt;
            width: 45pt;
            border-style: none;
        }

        .back-button button:hover {
            background-color: #6ab2ff;
        }

        button {
            border-radius: 8px;
        }
    </style>
=======
>>>>>>> 3cc10778cac73ef49e0f18745924a93ae4f1940b
</head>

<body>
    <!-- JavaScript to display success message and redirect to index.php -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <script>
            alert('<?php echo $_SESSION['success_message']; ?>');
            window.location.href = "index.php"; // Redire   ct to index.php after alert
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <div class="container">
        <h3>Competency Implementation</h3>
        <form action="" method="post">
            <input type="hidden" name="save_edits" value="1">
            <input type="hidden" name="subject_code" value="<?php echo htmlspecialchars($subject_code); ?>">
            <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>">

            <table class="info-table">
                <tr>
                    <td><label for="subject_code_display">I. Subject Code</label></td>
                    <td><input type="text" id="subject_code_display" name="subject_code_display" value="<?php echo htmlspecialchars($subject_code); ?>" readonly></td>
                </tr>
                <tr>
                    <td><label for="subject_name_display">II. Subject Title</label></td>
                    <td><input type="text" id="subject_name_display" name="subject_name_display" value="<?php echo htmlspecialchars($subject_name); ?>" readonly></td>
                </tr>
                <tr>
                    <td><label for="units">III. Units</label></td>
                    <td><input type="number" id="units" name="units" value="<?php echo htmlspecialchars($units); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="hours">IV. Hours</label></td>
                    <td><input type="number" id="hours" name="hours" value="<?php echo htmlspecialchars($hours); ?>" required></td>
                </tr>
                <tr>
                    <td><label for="department">V. Department</label></td>
                    <td>
                        <select id="department" name="department" required>
                            <option value="<?php echo htmlspecialchars($departments); ?>"><?php echo htmlspecialchars($departments); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                <tr>
                    <td><label for="school_year_start">VI. School Year</label></td>
                    <td class="school-year-section">
                        <input type="number" id="school_year_start" name="school_year_start" value="<?php echo htmlspecialchars($school_year_start); ?>" required>
                        <span> - </span>
                        <input type="number" id="school_year_end" name="school_year_end" value="<?php echo htmlspecialchars($school_year_end); ?>" required>
                    </td>
                </tr>
                <tr>
                    <td><label for="grading_period">VII. Grading Period/Quarter</label></td>
                    <td class="grading-period-section">
                        <select id="grading_period" name="grading_period" required>
                            <option value="1st SEMESTER" <?php echo $grading_period === '1st SEMESTER' ? 'selected' : ''; ?>>1st SEMESTER</option>
                            <option value="2nd SEMESTER" <?php echo $grading_period === '2nd SEMESTER' ? 'selected' : ''; ?>>2nd SEMESTER</option>
                        </select>
                        <span> - </span>
                        <select id="grading_quarter_start" name="grading_quarter_start" required>
                            <option value="PRELIM" <?php echo $grading_quarter_start === 'PRELIM' ? 'selected' : ''; ?>>PRELIM</option>
                            <option value="MIDTERM" <?php echo $grading_quarter_start === 'MIDTERM' ? 'selected' : ''; ?>>MIDTERM</option>
                            <option value="SEMI-FINAL" <?php echo $grading_quarter_start === 'SEMI-FINAL' ? 'selected' : ''; ?>>SEMI-FINAL</option>
                            <option value="FINAL" <?php echo $grading_quarter_start === 'FINAL' ? 'selected' : ''; ?>>FINAL</option>
                        </select>
                        <span> - </span>
                        <select id="grading_quarter_end" name="grading_quarter_end" required>
                            <option value="PRELIM" <?php echo $grading_quarter_end === 'PRELIM' ? 'selected' : ''; ?>>PRELIM</option>
                            <option value="MIDTERM" <?php echo $grading_quarter_end === 'MIDTERM' ? 'selected' : ''; ?>>MIDTERM</option>
                            <option value="SEMI-FINAL" <?php echo $grading_quarter_end === 'SEMI-FINAL' ? 'selected' : ''; ?>>SEMI-FINAL</option>
                            <option value="FINAL" <?php echo $grading_quarter_end === 'FINAL' ? 'selected' : ''; ?>>FINAL</option>
                        </select>
                    </td>
                </tr>

                </tr>
            </table>
    </div>


    <table class="competency-table" id="competencyTable">
    <thead>
        <tr>
            <th>SMCC Competencies</th>
            <th>Remarks On Class</th>
            <th class="no-print">Action</th>
        </tr>
    </thead>
    
    <tbody>
        <?php if (!empty($competencies)): ?>
            <?php foreach ($competencies as $competency): ?>
                <tr>
                    <td>
                        <input type="hidden" name="competency_id[]" value="<?php echo $competency['competency_id']; ?>">
                        <input type="text" name="competencies[]" value="<?php echo htmlspecialchars($competency['competency_description']); ?>" required class="competency-input">
                    </td>
                    <td>
                        <select name="remarks[]" required class="remarks-select">
                            <option value="IMPLEMENTED" <?php echo $competency['remarks'] === 'IMPLEMENTED' ? 'selected' : ''; ?>>IMPLEMENTED</option>
                            <option value="NOT IMPLEMENTED" <?php echo $competency['remarks'] === 'NOT IMPLEMENTED' ? 'selected' : ''; ?>>NOT IMPLEMENTED</option>
                        </select>
                    </td>
                    <td class="no-print">
                        <button type="button" class="remove-competency-btn" onclick="removeCompetency(this)">Remove</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td><input type="text" name="competencies[]" required class="competency-input"></td>
                <td>
                    <select name="remarks[]" required class="remarks-select">
                        <option value="IMPLEMENTED">IMPLEMENTED</option>
                        <option value="NOT IMPLEMENTED">NOT IMPLEMENTED</option>
                    </select>
                </td>
                <td class="no-print">
                    <button type="button" class="remove-competency-btn" onclick="removeCompetency(this)">Remove</button>
                </td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<button type="button" class="add-competency-btn no-print" onclick="addCompetency()">Add Competency</button>


    <div class="input-container">
        <ul class="competency-list">
            <li>
                <label>Total number of Competencies DepEd/TESDA/CHED:</label>
                <input type="number" name="total_competencies_deped_tesda_ched" value="<?php echo htmlspecialchars($total_competencies_deped_tesda_ched); ?>" required>
            </li>
            <li>
                <label>Total Number of Competencies SMCC based on DepEd/TESDA/CHED:</label>
                <input type="number" name="total_competencies_smcc" value="<?php echo htmlspecialchars($total_competencies_smcc); ?>" required>
            </li>
            <li>
                <label>Total Number of Institutional Competencies:</label>
                <input type="number" name="total_institutional_competencies" value="<?php echo htmlspecialchars($total_institutional_competencies); ?>" required>
            </li>
            <li>
                <label>Total Number of B and C:</label>
                <input type="number" name="total_competencies_b_and_c" value="<?php echo htmlspecialchars($total_competencies_b_and_c); ?>" required>
            </li>
            <li>
                <label>Total Number of Competencies Implemented:</label>
                <input type="number" name="total_competencies_implemented" value="<?php echo htmlspecialchars($total_competencies_implemented); ?>" required>
            </li>
            <li>
                <label>Total Number of Competencies NOT Implemented:</label>
                <input type="number" name="total_competencies_not_implemented" value="<?php echo htmlspecialchars($total_competencies_not_implemented); ?>" required>
            </li>
            <li>
                <label>% Number of Competencies Implemented:</label>
                <input type="number" name="percentage_competencies_implemented" value="<?php echo htmlspecialchars($percentage_competencies_implemented); ?>" required>
            </li>
        </ul>

        <div class="signatures">
            <p class="signature-label">Prepared by: <input type="text" name="prepared_by" value="<?php echo htmlspecialchars($prepared_by); ?>" required></p>
            <p class="signature-label">Checked by: <input type="text" name="checked_by" value="<?php echo htmlspecialchars($checked_by); ?>" required></p>
            <p class="signature-label">Noted by: <input type="text" name="noted_by" value="<?php echo htmlspecialchars($noted_by); ?>" required></p>
        </div>
    </div>

<<<<<<< HEAD
        <button class="print-button no-print" type="submit"><?php echo !empty($competencies) ? 'Save Edits' : 'Save New Competency'; ?></button>

        <div class="back-button">
            <button class="no-print" type="button" onclick="window.location.href='index.php'">Back</button>
        </div>
=======

    <button class="print-button no-print" type="submit"><?php echo !empty($competencies) ? 'Save Changes' : 'Save New Competency'; ?></button>
    <button class="cancel-button no-print" type="button" onclick="window.location.href='index.php'">Cancel</button>
>>>>>>> 3cc10778cac73ef49e0f18745924a93ae4f1940b
    </form>
    </div>
    <script>
        function addCompetency() {
            const table = document.getElementById('competencyTable');
            const row = table.insertRow(-1);
            const cell1 = row.insertCell(0);
            const cell2 = row.insertCell(1);
            const cell3 = row.insertCell(2);

            // Add hidden input for competency_id to handle new entries
            cell1.innerHTML = '<input type="hidden" name="competency_id[]" value=""><input type="text" name="competencies[]" style="width: 100%;" required>';
            cell2.innerHTML = '<select name="remarks[]" required><option value="IMPLEMENTED">IMPLEMENTED</option><option value="NOT IMPLEMENTED">NOT IMPLEMENTED</option>';
            cell3.innerHTML = '<button type="button" class="remove-competency-btn" onclick="removeCompetency(this)">Remove</button>';
        }

        function removeCompetency(button) {
            const row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }
    </script>
</body>

</html>