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

// Initialize syllabus data
$subject_code = "";
$subject_name = "";
$course_units = "";
$course_description = "";
$prerequisites_corequisites = "";
$contact_hours = "";
$performance_tasks = "";
$status = ""; // Add status initialization
$prepared_by = "";
$prepared_date = "";
$resource_checked_by = "";
$resource_checked_date = "";
$reviewed_by_program_chair = "";
$reviewed_by_dean = "";
$reviewed_by_date = "";
$approved_by = "";
$approved_by_date = "";


// Fetch subject code and name from POST data
if (isset($_POST['syllabus_subject_code']) && isset($_POST['syllabus_subject_name'])) {
    $subject_code = $_POST['syllabus_subject_code'];
    $subject_name = $_POST['syllabus_subject_name'];
} elseif (isset($_POST['subject_code']) && isset($_POST['subject_name'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
}

// Fetch existing syllabus data if it exists
if (!empty($subject_code)) {
    $sql = "SELECT * FROM syllabus WHERE subject_code = ? AND instructor_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $subject_code, $instructor_ID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $course_units = htmlspecialchars($row['course_units']);
                $course_description = htmlspecialchars($row['course_description']);
                $prerequisites_corequisites = htmlspecialchars($row['prerequisites_corequisites']);
                $contact_hours = htmlspecialchars($row['contact_hours']);
                $performance_tasks = htmlspecialchars($row['performance_tasks']);
                $status = htmlspecialchars($row['status']); // Add this line to fetch the status
                $prepared_by = htmlspecialchars($row['prepared_by']);
                $prepared_by_date = !empty($row['prepared_by_date']) ? htmlspecialchars($row['prepared_by_date']) : date('Y-m-d');
                $resource_checked_by = htmlspecialchars($row['resource_checked_by']);
                $resource_checked_by_date = !empty($row['resource_checked_by_date']) ? htmlspecialchars($row['resource_checked_by_date']) : date('Y-m-d');
                $reviewed_by_program_chair = htmlspecialchars($row['reviewed_by_program_chair']);
                $reviewed_by_dean = htmlspecialchars($row['reviewed_by_dean']);
                $reviewed_by_date = !empty($row['reviewed_by_date']) ? htmlspecialchars($row['reviewed_by_date']) : date('Y-m-d');
                $approved_by = htmlspecialchars($row['approved_by']);
                $approved_by_date = !empty($row['approved_by_date']) ? htmlspecialchars($row['approved_by_date']) : date('Y-m-d');
            }
            $result->free();
        }
        $stmt->close();
    }
}


// Initialize grading data
$written_task_criteria = [];
$performance_task_criteria = [];
$quarterly_assessment_criteria = [];

// Fetch existing grading system data from the database
$sqlGradingSystem = "SELECT * FROM grading_system WHERE subject_code = ? AND instructor_id = ?";
$stmtGradingSystem = $conn->prepare($sqlGradingSystem);
$stmtGradingSystem->bind_param("si", $subject_code, $instructor_ID);
$stmtGradingSystem->execute();
$resultGradingSystem = $stmtGradingSystem->get_result();

// Populate the arrays with data based on the criteria type
while ($row = $resultGradingSystem->fetch_assoc()) {
    if ($row['criteria_type'] == 'written_task') {
        $written_task_criteria[$row['criteria_name']] = $row['percentage'];
    } elseif ($row['criteria_type'] == 'performance_task') {
        $performance_task_criteria[$row['criteria_name']] = $row['percentage'];
    } elseif ($row['criteria_type'] == 'quarterly_assessment') {
        $quarterly_assessment_criteria[$row['criteria_name']] = $row['percentage'];
    }
}

// Close the prepared statement
$stmtGradingSystem->close();



if (isset($_POST['save_syllabus'])) {
    $conn->begin_transaction(); // Begin transaction for atomicity

    try {
        // Check if syllabus exists
        $sqlCheckSyllabus = "SELECT * FROM syllabus WHERE subject_code = ? AND instructor_ID = ?";
        $stmtCheckSyllabus = $conn->prepare($sqlCheckSyllabus);
        $stmtCheckSyllabus->bind_param("si", $subject_code, $instructor_ID);
        $stmtCheckSyllabus->execute();
        $resultSyllabus = $stmtCheckSyllabus->get_result();

        if ($resultSyllabus->num_rows > 0) {
            // Update existing syllabus
            $sqlUpdateSyllabus = "
                UPDATE syllabus 
                SET 
                    course_units = ?, course_description = ?, prerequisites_corequisites = ?, 
                    contact_hours = ?, performance_tasks = ?, prepared_by = ?, prepared_by_date = ?, 
                    resource_checked_by = ?, resource_checked_by_date = ?, reviewed_by_program_chair = ?, 
                    reviewed_by_dean = ?, reviewed_by_date = ?, approved_by = ?, approved_by_date = ? 
                WHERE subject_code = ? AND instructor_ID = ?";
            $stmtUpdateSyllabus = $conn->prepare($sqlUpdateSyllabus);
            $stmtUpdateSyllabus->bind_param(
                "sssssssssssssssi",
                $_POST['course_units'],
                $_POST['course_description'],
                $_POST['prerequisites_corequisites'],
                $_POST['contact_hours'],
                $_POST['performance_tasks'],
                $_POST['prepared_by'],
                $_POST['prepared_by_date'],
                $_POST['resource_checked_by'],
                $_POST['resource_checked_by_date'],
                $_POST['reviewed_by_program_chair'],
                $_POST['reviewed_by_dean'],
                $_POST['reviewed_by_date'],
                $_POST['approved_by'],
                $_POST['approved_by_date'],
                $subject_code,
                $instructor_ID
            );
            $stmtUpdateSyllabus->execute();
        } else {
            // Insert new syllabus
            $sqlInsertSyllabus = "
                INSERT INTO syllabus (
                    subject_code, instructor_ID, course_units, course_description, prerequisites_corequisites, 
                    contact_hours, performance_tasks, prepared_by, prepared_by_date, resource_checked_by, 
                    resource_checked_by_date, reviewed_by_program_chair, reviewed_by_dean, reviewed_by_date, 
                    approved_by, approved_by_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmtInsertSyllabus = $conn->prepare($sqlInsertSyllabus);
            $stmtInsertSyllabus->bind_param(
                "sissssssssssssss",
                $subject_code,
                $instructor_ID,
                $_POST['course_units'],
                $_POST['course_description'],
                $_POST['prerequisites_corequisites'],
                $_POST['contact_hours'],
                $_POST['performance_tasks'],
                $_POST['prepared_by'],
                $_POST['prepared_date'],
                $_POST['resource_checked_by'],
                $_POST['resource_checked_date'],
                $_POST['reviewed_by_program_chair'],
                $_POST['reviewed_by_dean'],
                $_POST['reviewed_by_date'],
                $_POST['approved_by'],
                $_POST['approved_by_date']
            );
            $stmtInsertSyllabus->execute();
        }
        // Insert or update the grading criteria (percentage distribution)
        // Handle different sections of the grading system (Written Task, Performance Tasks, Quarterly Assessment)
        $criteria_types = [
            'written_task' => ['criteria_name' => 'written_task_criteria', 'percentage' => 'written_task_percentage', 'delete_flag' => 'written_task_delete'],
            'performance_task' => ['criteria_name' => 'performance_task_criteria', 'percentage' => 'performance_task_percentage', 'delete_flag' => 'performance_task_delete'],
            'quarterly_assessment' => ['criteria_name' => 'quarterly_assessment_criteria', 'percentage' => 'quarterly_assessment_percentage', 'delete_flag' => 'quarterly_assessment_delete']
        ];

        foreach ($criteria_types as $section => $fieldNames) {
            if (isset($_POST[$fieldNames['criteria_name']]) && is_array($_POST[$fieldNames['criteria_name']])) {
                $criteriaNames = $_POST[$fieldNames['criteria_name']];
                $percentages = $_POST[$fieldNames['percentage']];
                $deleteFlags = $_POST[$fieldNames['delete_flag']];  // Assuming delete flag is passed for each criteria

                for ($i = 0; $i < count($criteriaNames); $i++) {
                    $criteria_name = $criteriaNames[$i];

                    // Skip entries with empty criteria names
                    if (empty($criteria_name)) {
                        continue;
                    }

                    $percentage = !empty($percentages[$i]) ? $percentages[$i] : 0;  // Use default value if percentage is empty
                    $delete_flag = !empty($deleteFlags[$i]) ? $deleteFlags[$i] : 0;  // Handle delete flag

                    if ($delete_flag == 1) {
                        // Delete criteria entry if flag is set to 1
                        $stmtDelete = $conn->prepare("DELETE FROM grading_system WHERE subject_code = ? AND instructor_id = ? AND criteria_name = ? AND criteria_type = ?");
                        $stmtDelete->bind_param("siss", $subject_code, $instructor_ID, $criteria_name, $section); // Pass $section as the criteria_type
                        $stmtDelete->execute();
                        $stmtDelete->close();
                    } else {
                        // Insert or update criteria (check if exists)
                        $stmtCheck = $conn->prepare("SELECT * FROM grading_system WHERE subject_code = ? AND instructor_id = ? AND criteria_name = ? AND criteria_type = ?");
                        $stmtCheck->bind_param("siss", $subject_code, $instructor_ID, $criteria_name, $section); // Pass $section as the criteria_type
                        $stmtCheck->execute();
                        $result = $stmtCheck->get_result();

                        if ($result->num_rows > 0) {
                            // Update existing entry
                            $stmtUpdate = $conn->prepare("UPDATE grading_system SET percentage = ? WHERE subject_code = ? AND instructor_id = ? AND criteria_name = ? AND criteria_type = ?");
                            $stmtUpdate->bind_param("isiss", $percentage, $subject_code, $instructor_ID, $criteria_name, $section); // Pass $section as the criteria_type
                            $stmtUpdate->execute();
                            $stmtUpdate->close();
                        } else {
                            // Insert new criteria
                            $stmtInsert = $conn->prepare("INSERT INTO grading_system (subject_code, instructor_id, criteria_name, percentage, criteria_type) VALUES (?, ?, ?, ?, ?)");
                            $stmtInsert->bind_param("sisss", $subject_code, $instructor_ID, $criteria_name, $percentage, $section); // Pass $section as the criteria_type
                            $stmtInsert->execute();
                            $stmtInsert->close();
                        }

                        $stmtCheck->close();
                        $result->free();
                    }
                }
            }
        }




        // Clear existing context data
        $stmtClearContext = $conn->prepare("DELETE FROM context WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearContext->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearContext->execute();

        // Insert or update context data
        $sections = ['prelim', 'midterm', 'semifinal', 'final'];
        $stmtInsertContext = $conn->prepare("
            INSERT INTO context (instructor_id, subject_code, section, hours, ilo, topics, institutional_values, teaching_activities, resources, assessment_tasks, course_map)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsertContext->bind_param(
            "issssssssss",
            $instructor_ID,
            $subject_code,
            $section,
            $hours,
            $ilo,
            $topics,
            $institutional_values,
            $teaching_activities,
            $resources,
            $assessment_tasks,
            $course_map
        );

        foreach ($sections as $section) {
            $section = strtoupper($section); // e.g., PRELIM, MIDTERM, etc.

            if (isset($_POST[$section . '_hours']) && is_array($_POST[$section . '_hours'])) {
                foreach ($_POST[$section . '_hours'] as $index => $hour) {
                    $hours = !empty($_POST[$section . '_hours'][$index]) ? $_POST[$section . '_hours'][$index] : null;
                    $ilo = !empty($_POST[$section . '_ilo'][$index]) ? $_POST[$section . '_ilo'][$index] : null;
                    $topics = !empty($_POST[$section . '_topics'][$index]) ? $_POST[$section . '_topics'][$index] : null;
                    $institutional_values = !empty($_POST[$section . '_institutional_values'][$index]) ? $_POST[$section . '_institutional_values'][$index] : null;
                    $teaching_activities = !empty($_POST[$section . '_teaching_activities'][$index]) ? $_POST[$section . '_teaching_activities'][$index] : null;
                    $resources = !empty($_POST[$section . '_resources'][$index]) ? $_POST[$section . '_resources'][$index] : null;
                    $assessment_tasks = !empty($_POST[$section . '_assessment'][$index]) ? $_POST[$section . '_assessment'][$index] : null;
                    $course_map = !empty($_POST[$section . '_course_map'][$index]) ? $_POST[$section . '_course_map'][$index] : null;

                    if ($hours || $ilo || $topics || $institutional_values || $teaching_activities || $resources || $assessment_tasks || $course_map) {
                        $stmtInsertContext->execute();
                    }
                }
            }
        }

        // Clear existing PILO-GILO mappings for the subject
        $stmtClearPiloGiloMappings = $conn->prepare("DELETE FROM pilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearPiloGiloMappings->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearPiloGiloMappings->execute();

        // Insert new PILO-GILO mappings
        if (isset($_POST['pilo']) && is_array($_POST['pilo'])) {
            $piloValues = $_POST['pilo'];
            $a_values = $_POST['a'];
            $b_values = $_POST['b'];
            $c_values = $_POST['c'];
            $d_values = $_POST['d'];

            for ($i = 0; $i < count($piloValues); $i++) {
                $pilo = $piloValues[$i];

                // Use default values if the GILO values are empty or missing
                $a = !empty($a_values[$i]) ? $a_values[$i] : '';
                $b = !empty($b_values[$i]) ? $b_values[$i] : '';
                $c = !empty($c_values[$i]) ? $c_values[$i] : '';
                $d = !empty($d_values[$i]) ? $d_values[$i] : '';

                if (!empty($pilo)) {
                    // Insert PILO and corresponding GILOs (a, b, c, d)
                    $stmtPiloGilo = $conn->prepare("INSERT INTO pilo_gilo_map (subject_code, instructor_ID, pilo, a, b, c, d) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmtPiloGilo->bind_param("sisssss", $subject_code, $instructor_ID, $pilo, $a, $b, $c, $d);
                    if (!$stmtPiloGilo->execute()) {
                        throw new Exception("Failed to insert PILO-GILO mapping: " . $stmtPiloGilo->error);
                    }
                }
            }
        }
        // Clear existing CILO-GILO mappings for the subject
        $stmtClearCiloGiloMappings = $conn->prepare("DELETE FROM cilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearCiloGiloMappings->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearCiloGiloMappings->execute();

        // Check for errors in the previous query
        if ($stmtClearCiloGiloMappings->error) {
            $_SESSION['error_message'] = "Error clearing CILO-GILO mappings: " . $stmtClearCiloGiloMappings->error;
            echo "<script>alert('Error: " . addslashes($stmtClearCiloGiloMappings->error) . "'); window.location.href='index.php';</script>";
            exit;
        }

        // Insert new CILO-GILO mappings
        if (isset($_POST['cilo_description']) && is_array($_POST['cilo_description'])) {
            $cilo_descriptions = $_POST['cilo_description'];
            $a_values = $_POST['a'];
            $b_values = $_POST['b'];
            $c_values = $_POST['c'];
            $d_values = $_POST['d'];
            $e_values = $_POST['e'];
            $f_values = $_POST['f'];
            $g_values = $_POST['g'];
            $h_values = $_POST['h'];
            $i_values = $_POST['i'];
            $j_values = $_POST['j'];
            $k_values = $_POST['k'];
            $l_values = $_POST['l'];
            $m_values = $_POST['m'];
            $n_values = $_POST['n'];
            $o_values = $_POST['o'];

            // Loop through CILOs and GILOs
            for ($i = 0; $i < count($cilo_descriptions); $i++) {
                $cilo_description = !empty($cilo_descriptions[$i]) ? $cilo_descriptions[$i] : null;
                $a = $a_values[$i] ?? '';
                $b = $b_values[$i] ?? '';
                $c = $c_values[$i] ?? '';
                $d = $d_values[$i] ?? '';
                $e = $e_values[$i] ?? '';
                $f = $f_values[$i] ?? '';
                $g = $g_values[$i] ?? '';
                $h = $h_values[$i] ?? '';
                $i_col = $i_values[$i] ?? ''; // Avoid conflict with loop variable $i
                $j = $j_values[$i] ?? '';
                $k = $k_values[$i] ?? '';
                $l = $l_values[$i] ?? '';
                $m = $m_values[$i] ?? '';
                $n = $n_values[$i] ?? '';
                $o = $o_values[$i] ?? '';

                // Prepare and bind the INSERT query for CILO-GILO mapping
                $stmtCiloGilo = $conn->prepare("
            INSERT INTO cilo_gilo_map (
                subject_code, instructor_ID, cilo_description,
                a, b, c, d, e, f, g, h, i, j, k, l, m, n, o
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
                $stmtCiloGilo->bind_param(
                    "sissssssssssssssss",
                    $subject_code,
                    $instructor_ID,
                    $cilo_description,
                    $a,
                    $b,
                    $c,
                    $d,
                    $e,
                    $f,
                    $g,
                    $h,
                    $i_col,
                    $j,
                    $k,
                    $l,
                    $m,
                    $n,
                    $o
                );

                // Execute the statement and check for errors
                if (!$stmtCiloGilo->execute()) {
                    $_SESSION['error_message'] = "Failed to insert CILO-GILO mapping: " . $stmtCiloGilo->error;
                    echo "<script>alert('Error: " . addslashes($stmtCiloGilo->error) . "'); window.location.href='index.php';</script>";
                    exit;
                }
                $stmtCiloGilo->close();  // Close the statement after execution
            }
        }

        // Commit the transaction if no errors
        $conn->commit();
        echo "<script>alert('Syllabus submitted successfully!'); window.location.href='index.php';</script>";
    } catch (Exception $e) {
        // Rollback the transaction in case of any error
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Syllabus</title>
    <link rel="stylesheet" href="../syllabus.css">
    <link rel="stylesheet" href="custom_table.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        select {
            width: 100%;
            /* Stretch to fill the available space */
            min-width: 44px;
            /* Increase the minimum width */
            max-width: 100%;
            /* Ensure the select element uses the full cell width */
            padding: 4px;
            /* Adjust padding for better spacing */
        }

        .signature-line {
            margin-left: 75%;
            margin-top: -19px;
            text-align: center;
            font-size: 10pt;
        }

        .prepared-signiture p {
            width: 250px;
        }


        /*input box sa names and signature*/
        .custom-table .info-cell input {
            text-align: center;
            font-size: 14px;
            width: 50%;
            margin-bottom: -30px;
            height: 12px;
            font-weight: 600;
        }

        .custom-table .info-cell p {
            margin: 5px;
        }

        .custom-table .info-cell-approved input {
            text-align: center;
            font-size: 14px;
            width: 50%;
            margin-bottom: -30px;
            height: 12px;
            font-weight: 600;
        }

        .custom-table .info-cell-approved p {
            margin: 5px;
        }

        .info-cell span {
            padding-right: 430px;
        }

        .info-cell-approved span {
            padding-right: 295px;
        }

        /*
        .signature-cell input {
            margin: 15%;
            margin-bottom: 30px;
            text-align: center;
            font-size: 17px;
            width: 70%;
            height: 20px;
            font-weight: 600;
            border-style: none;
        }

        .signature-cell p {
            margin: 0;
            margin-top: -40px;
            padding-left: 15%;
            font-size: 14px;
            width: 70%;
        }*/
    </style>
    <script>
        // Auto resize textarea
        document.addEventListener('DOMContentLoaded', () => {
            const textareas = document.querySelectorAll('.autoResizeTextarea');

            const resizeTextarea = (textarea) => {
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
            };
            textareas.forEach(textarea => {
                textarea.addEventListener('input', () => resizeTextarea(textarea));
                resizeTextarea(textarea);
            });
        });
    </script>

    <?php
    // Fetch subject code and name from POST data
    $subject_code = '';
    $subject_name = '';
    if (isset($_POST['syllabus_subject_code']) && isset($_POST['syllabus_subject_name'])) {
        $subject_code = $_POST['syllabus_subject_code'];
        $subject_name = $_POST['syllabus_subject_name'];
    } elseif (isset($_POST['subject_code']) && isset($_POST['subject_name'])) {
        $subject_code = $_POST['subject_code'];
        $subject_name = $_POST['subject_name'];
    }
    ?>

</head>

<body>
    <?php
    // Check if success message exists
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        // Unset the session message after displaying it once
        unset($_SESSION['success_message']);
    ?>
        <script>
            // Show SweetAlert and redirect after clicking OK
            window.onload = function() {
                Swal.fire({
                    title: 'localhost says',
                    text: '<?php echo $message; ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'index.php'; // Redirect to index.php
                });
            };
        </script>
    <?php
    }
    ?>
    <style>
        .status-container {
            display: flex;
            align-items: center;
            justify-content: left;
            gap: 10px;
            /* Adds space between items */
        }

        .status-button-like {
            padding: 8px 15px;
            font-weight: bold;
            color: #fff;
            border-radius: 5px;
            display: inline-block;
            text-align: center;
            text-transform: uppercase;
            font-size: 0.9rem;
            cursor: default;
            /* Disables the pointer cursor */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border: none;
            background-color: #ccc;
            /* Default color if no status */
            letter-spacing: 1px;
            /* Adds spacing between letters */
        }

        /* Different colors based on status */
        .status-button-like.denied {
            background-color: #f1c40f;
            /* Yellow for pending */
        }

        .status-button-like.approved {
            background-color: #2ecc71;
            /* Green for approved */
        }

        .status-button-like.pending {
            background-color: #e74c3c;
            /* Red for denied */
        }

        /* Styling for status text to complement the button-like appearance */
        .status-text {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            margin-left: 5px;
            /* Adjusts spacing next to the button */
        }

        .status-text.pending {
            color: #f1c40f;
        }

        .status-text.approved {
            color: #2ecc71;
        }

        .status-text.denied {
            color: #e74c3c;
        }

        .submit-button:disabled {
            opacity: 0.6;
            /* Dimmed appearance */
            cursor: not-allowed;
        }

        .custom-table .signature-cell {
            text-align: center;
            vertical-align: bottom;
            padding-top: 10px;
            font-size: 12px;
            width: 20%;
        }
    </style>



    <!-- Main Content Section -->
    <div class="container">
        <!-- Header Section -->
        <div class="divHeader">
            <div class="header-container">
                <!-- Left Section (Logo) -->
                <div class="headContents header-left">
                    <img src="../smcclogo.jfif" alt="SMCC Logo" class="logo">
                </div>
                <!-- Center Section (Text) -->
                <div class="headContents header-center">
                    <div class="college-name">Saint Michael College of Caraga</div>
                    <div class="college-details">
                        <div>Brgy. 4, Nasipit, Agusan del Norte, Philippines</div>
                        <div>Tel. Nos. +63 085 343-3251 / +63 085 283-3113 Fax No. +63 085 808-0892</div>
                        <div><a href="https://www.smccnasipit.edu.ph/">www.smccnasipit.edu.ph</a></div>
                    </div>
                </div>
                <!-- Right Section (Accreditation Logos) -->
                <div class="headContents header-right">
                    <img src="ISO&PAB.png" alt="Accreditation Logos" class="logo">
                </div>
            </div>
        </div>
        <h2>Edit Syllabus</h2>
        <!-- Display messages if any -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<p style="color: green;">' . $_SESSION['success_message'] . '</p>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['info_message'])) {
            echo '<p style="color: blue;">' . $_SESSION['info_message'] . '</p>';
            unset($_SESSION['info_message']);
        }
        ?>

        <!-- Check if success message exists in the session -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <script>
                showSuccessAndRedirect(); // Show alert and redirect
            </script>
            <?php unset($_SESSION['success_message']); // Clear success message 
            ?>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="subject_code" value="<?php echo htmlspecialchars($subject_code); ?>">
            <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>">



            <!-- Read-Only Vision, Mission Section -->
            <h3>I. School's Vision, Mission, Goal, Objectives, Michaelinian Identity</h3>
            <ul>
                <li class="status-container"><b>Status: </b>
                    <span class="status-button-like <?php echo strtolower($status); ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </span>
                </li>
            </ul>


            <h4>Vision</h4>
            <p>Saint Michael College of Caraga envisions to a university by 2035 and upholds spiritual formation and excellence in teaching, service, and, research.</p>

            <h4>Mission</h4>
            <p>As such, SMCC commits itself:</p>
            <ul>
                <li>SMCC shall provide spiritual formation and learning culture that will ensure the students with an excellent and rewarding learning experience that transforms lives, abound spirituality, develop skills, and prepare future leaders.</li>
                <li>SMCC shall engage in dynamic, innovative, and interdisciplinary research to advance and achieve institutional initiatives.</li>
                <li>SMCC shall commit to serve the diverse and local communities by fostering innovations through service-learning that enhances reciprocal community partnerships for spiritual and social development.</li>
            </ul>

            <h4>Goal</h4>
            <p>Uphold Culture of Excellence in the Areas of Spiritual Formation, Instruction, Research, and Extension, thus producing Graduates that are Globally Competent, Spiritually Embodied, and Socially Responsible.</p>

            <h4>General Objectives</h4>
            <ul>
                <li>To integrate positive and evangelical values in all areas and design Christian formation programs that are effective and responsive to the psycho-spiritual needs of the learners, parents, and personnel.</li>
                <li>To enhance consistently the curriculum and cultivate teachers’ effectiveness to promote quality instruction.</li>
                <li>To continue upgrading facilities and services for the satisfaction of the clientele.</li>
                <li>To intensify the curriculum-based and institutional research that is dynamic, innovative, and interdisciplinary.</li>
            </ul>

            <h4>Michaelinian Identity</h4>
            <p>Secured by Saint Michael the Archangel's Sword of Bravery and Victory, the Michaelinians of today and tomorrow are:</p>
            <ul>
                <li><b>S</b> - ocially Responsible</li>
                <li><b>M</b> - issionaries of Christian Values</li>
                <li><b>C</b> - ommitted Individuals</li>
                <li><b>C</b> - ompetent in their Chosen Fields of Endeavor</li>
            </ul>

            <h3>II. Graduate Intended Learning Outcomes (GILO)</h3>
            <ul>
                <li>Demonstrate social responsibilities.</li>
                <li>Become missionaries of Christian values.</li>
                <li>Uphold unconditional commitment to life and to action.</li>
                <li>Exude competence in one’s chosen fields of endeavor.</li>
            </ul>

            <h4>PROGRAM MAPPING</h4>
            <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

            <table id="piloGiloTable" class="pilo-gilo-table">
                <thead>
                    <tr>
                        <th>Program Intended Learning Outcomes (PILOs)<br><br>After completion of the program, the student must be able to:</th>
                        <th colspan="4">Graduate Intended Learning Outcomes (GILOs)</th>
                        <th><button type="button" class="button add-row-button" onclick="addPiloGiloRow()">+</button></th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>a</th>
                        <th>b</th>
                        <th>c</th>
                        <th>d</th>
                        <th></th> <!-- Empty header for the remove button -->
                    </tr>
                </thead>
                <tbody>
                    <!-- Template row -->
                    <tr class="piloGiloRow">
                        <td>
                            <textarea name="pilo[]" placeholder="Enter PILO"></textarea>
                        </td>
                        <td><select name="a[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select></td>
                        <td><select name="b[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select></td>
                        <td><select name="c[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select></td>
                        <td><select name="d[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select></td>
                        <td>
                            <button type="button" class="button remove-row-button" onclick="removePiloGiloRow(this)" disabled>-</button>
                        </td>
                    </tr>
                </tbody>
            </table>


            <script>
                // Function to populate existing data into the table
                function populatePiloGiloRows(piloArray, giloAArray, giloBArray, giloCArray, giloDArray) {
                    var table = document.getElementById('piloGiloTable');

                    // Loop through the PILO and GILO arrays
                    for (var i = 0; i < piloArray.length; i++) {
                        var newRow;

                        if (i === 0) {
                            // For the first row, use the existing template row
                            newRow = document.querySelector('.piloGiloRow');
                        } else {
                            // For subsequent rows, clone the first row
                            newRow = document.querySelector('.piloGiloRow').cloneNode(true);

                            // Enable the remove button for new rows
                            newRow.querySelector('button.remove-row-button').disabled = false;

                            // Append the new row to the table
                            table.appendChild(newRow);
                        }

                        // Set the PILO field for the row
                        newRow.querySelector('textarea[name="pilo[]"]').value = piloArray[i];

                        // Set the GILO fields for the row
                        newRow.querySelector('select[name="a[]"]').value = giloAArray[i];
                        newRow.querySelector('select[name="b[]"]').value = giloBArray[i];
                        newRow.querySelector('select[name="c[]"]').value = giloCArray[i];
                        newRow.querySelector('select[name="d[]"]').value = giloDArray[i];
                    }
                }

                // Add new PILO-GILO row
                function addPiloGiloRow() {
                    var firstRow = document.querySelector('.piloGiloRow');
                    var newRow = firstRow.cloneNode(true);

                    // Clear the values for the new row's inputs
                    newRow.querySelector('textarea[name="pilo[]"]').value = ''; // Clear PILO field
                    newRow.querySelector('select[name="a[]"]').selectedIndex = 0; // Reset GILO a select box
                    newRow.querySelector('select[name="b[]"]').selectedIndex = 0; // Reset GILO b select box
                    newRow.querySelector('select[name="c[]"]').selectedIndex = 0; // Reset GILO c select box
                    newRow.querySelector('select[name="d[]"]').selectedIndex = 0; // Reset GILO d select box

                    // Enable the remove button for the new row
                    newRow.querySelector('button.remove-row-button').disabled = false;

                    // Set up the remove button with the correct function
                    newRow.querySelector('button.remove-row-button').setAttribute('onclick', 'removePiloGiloRow(this)');

                    // Append the new row to the table
                    document.getElementById('piloGiloTable').appendChild(newRow);
                }

                // Remove a PILO-GILO row, ensuring the first row cannot be deleted
                function removePiloGiloRow(button) {
                    var table = document.getElementById('piloGiloTable');
                    var rows = table.getElementsByClassName('piloGiloRow');

                    // Check if the row is the first one
                    if (button.closest('.piloGiloRow') === rows[0]) {
                        alert('The first row cannot be deleted.');
                    } else {
                        // Remove the row if it is not the first one
                        button.closest('.piloGiloRow').remove();
                    }
                }

                // Fetch the PILO and GILO data when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    // Use PHP to pass the subject code from server-side to client-side
                    var subjectCode = '<?php echo $subject_code; ?>'; // Use dynamic subject code from PHP

                    if (subjectCode) {
                        fetch('fetch_pilo_gilo.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                if (data.pilo && data.a && data.b && data.c && data.d && data.pilo.length > 0) {
                                    // Ensure the number of PILOs and GILOs match
                                    if (data.pilo.length === data.a.length &&
                                        data.pilo.length === data.b.length &&
                                        data.pilo.length === data.c.length &&
                                        data.pilo.length === data.d.length) {
                                        populatePiloGiloRows(data.pilo, data.a, data.b, data.c, data.d); // Populate with PILO and GILO data
                                    } else {
                                        console.warn('Mismatch between number of PILOs and GILOs.');
                                    }
                                } else {
                                    console.warn('No data found for the specified subject code.');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });
            </script>


            <!-- Course Information Section -->
            <h4>Course Information</h4>
            <ul>
                <li>Course Code: <input type="text" name="course_code" value="<?php echo htmlspecialchars($subject_code); ?>" readonly></li>
                <li>Course Name: <input type="text" name="course" value="<?php echo htmlspecialchars($subject_name); ?>" readonly></li>
                <li>Course Units: <input type="text" name="course_units" value="<?php echo htmlspecialchars($course_units); ?>" placeholder="Enter Course Units"></li>
                <li>Course Description: <textarea name="course_description" class="autoResizeTextarea" placeholder="Enter Course Description"><?php echo htmlspecialchars($course_description); ?></textarea></li>
                <li>Prerequisites: <input type="text" name="prerequisites_corequisites" value="<?php echo htmlspecialchars($prerequisites_corequisites); ?>" placeholder="Enter Prerequisites"></li>
                <li>Contact Hours: <input type="text" name="contact_hours" value="<?php echo htmlspecialchars($contact_hours); ?>" placeholder="Enter Contact Hours"></li>
            </ul>

            <!-- Course Intended Learning Outcomes Section -->
            <h3>Course Intended Learning Outcomes (CILO)</h3>
            <h4>COURSE MAPPING</h4>
            <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

            <table id="ciloGiloTable" border="1">
                <thead>
                    <!-- New row for the header that spans across columns a to o -->
                    <tr>
                        <th rowspan="2">Course Intended Learning Outcomes (CILOs)<br><br>
                            After completion of the course, the student must be able to:
                        </th>
                        <th colspan="15" style="text-align: center">Program Intended Learning Outcome (PILO)</th>
                        <th rowspan="2"><button type="button" class="button add-row-button" onclick="addCiloGiloRow()">+</button></th>
                    </tr>
                    <!-- Sub-header for individual PILO columns -->
                    <tr>
                        <th>a</th>
                        <th>b</th>
                        <th>c</th>
                        <th>d</th>
                        <th>e</th>
                        <th>f</th>
                        <th>g</th>
                        <th>h</th>
                        <th>i</th>
                        <th>j</th>
                        <th>k</th>
                        <th>l</th>
                        <th>m</th>
                        <th>n</th>
                        <th>o</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="ciloGiloRow">
                        <td><textarea name="cilo_description[]" class="autoResizeTextarea" placeholder="Enter CILO"></textarea></td>
                        <!-- PILO selections for each column -->
                        <td>
                            <select name="a[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="b[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="c[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="d[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="e[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="f[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="g[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="h[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="i[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="j[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="k[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="l[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="m[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="n[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td>
                            <select name="o[]">
                                <option value=""></option>
                                <option value="I">I</option>
                                <option value="D">D</option>
                                <option value="P">P</option>
                            </select>
                        </td>
                        <td><button type="button" class="button remove-row-button" onclick="removeCiloGiloRow(this)" disabled>-</button></td>
                    </tr>
                </tbody>
            </table>


            <script>
                // Function to resize the select element based on its selected option
                function adjustSelectWidth(select) {
                    // Create a temporary element to calculate the width of the selected option
                    const temp = document.createElement("span");
                    temp.style.visibility = "hidden";
                    temp.style.fontSize = window.getComputedStyle(select).fontSize; // Ensure the font size matches
                    temp.textContent = select.options[select.selectedIndex].text;

                    document.body.appendChild(temp); // Add to the DOM to measure its width

                    // Adjust the select element's width based on the calculated width
                    select.style.width = temp.offsetWidth + 20 + "px"; // Adding some padding

                    document.body.removeChild(temp); // Clean up the temporary element
                }

                // Add an event listener to each select element to resize it when the value changes
                document.querySelectorAll('select[name$="[]"]').forEach(select => {
                    // Initial adjustment on page load
                    adjustSelectWidth(select);

                    // Adjust when the user changes the selected value
                    select.addEventListener('change', function() {
                        adjustSelectWidth(this);
                    });
                });

                // Function to populate existing data into the table
                function populateCiloGiloRows(ciloData) {
                    const table = document.getElementById('ciloGiloTable');
                    const firstRow = document.querySelector('.ciloGiloRow');

                    // Clear all existing rows except the first one
                    const rows = table.querySelectorAll('.ciloGiloRow');
                    rows.forEach((row, index) => {
                        if (index !== 0) row.remove(); // Keep only the first row template
                    });

                    // Populate the first row with data
                    if (ciloData.cilo.length > 0) {
                        populateRow(firstRow, ciloData, 0); // Populate the first row with the first set of CILO data
                    }

                    // Add additional rows for the remaining CILOs
                    for (let i = 1; i < ciloData.cilo.length; i++) {
                        const newRow = firstRow.cloneNode(true); // Clone the first row
                        populateRow(newRow, ciloData, i); // Populate the new row with CILO data
                        newRow.querySelector('button.remove-row-button').disabled = false; // Enable the remove button for new rows
                        table.querySelector('tbody').appendChild(newRow); // Append the new row to the table
                    }
                }

                // Function to populate each row with CILO data (excluding gilo1 and gilo2)
                function populateRow(row, ciloData, index) {
                    row.querySelector('textarea[name="cilo_description[]"]').value = ciloData.cilo[index] || '';

                    // Populate the columns a to o
                    ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o'].forEach(col => {
                        row.querySelector(`select[name="${col}[]"]`).value = ciloData[col][index] || '';
                    });
                }

                // Add new row for CILO mapping
                function addCiloGiloRow() {
                    const table = document.getElementById('ciloGiloTable');
                    const firstRow = document.querySelector('.ciloGiloRow'); // Select the first row to clone
                    const newRow = firstRow.cloneNode(true); // Clone the row

                    // Clear the textarea and reset the select inputs
                    newRow.querySelector('textarea[name="cilo_description[]"]').value = '';
                    newRow.querySelectorAll('select').forEach(select => {
                        select.value = ''; // Reset each select input to the empty option
                    });

                    // Enable the remove button for the new row
                    newRow.querySelector('button.remove-row-button').disabled = false;

                    // Set the remove button function
                    newRow.querySelector('button.remove-row-button').onclick = function() {
                        removeCiloGiloRow(this);
                    };

                    // Append the new row to the table body
                    table.querySelector('tbody').appendChild(newRow);
                }

                // Remove a CILO row, ensuring at least 1 row remains
                function removeCiloGiloRow(button) {
                    const table = document.getElementById('ciloGiloTable');
                    const rows = table.getElementsByClassName('ciloGiloRow');

                    if (rows.length > 1) {
                        button.closest('.ciloGiloRow').remove(); // Remove the row if there is more than one
                    } else {
                        alert('At least one row must remain.');
                    }
                }

                // Fetch the CILO data when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    const subjectCode = '<?php echo $subject_code; ?>'; // Use dynamic subject code from PHP

                    if (subjectCode) {
                        fetch('fetch_cilo_gilo.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                console.log(data); // Log the received data for debugging
                                if (data.cilo && data.cilo.length > 0) {
                                    populateCiloGiloRows(data); // Populate with CILO data
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });
            </script>



            <h4>Context</h4>
            <table id="contextTable">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Hours</th>
                        <th>Intended Learning Outcomes (ILO) / Competency(ies)</th>
                        <th>Topics</th>
                        <th>Institutional Values</th>
                        <th>Teaching and Learning Activities</th>
                        <th>Resources/References</th>
                        <th>Assessment Tasks</th>
                        <th>Course Map</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="prelimSection" class="section-prelim">
                    <tr>
                        <td>PRELIM</td>
                        <td><textarea name="PRELIM_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('prelimSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="midtermSection" class="section-midterm">
                    <tr>
                        <td>MIDTERM</td>
                        <td><textarea name="MIDTERM_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('midtermSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="semifinalSection" class="section-semifinal">
                    <tr>
                        <td>SEMI FINAL</td>
                        <td><textarea name="SEMIFINAL_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('semifinalSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="finalSection" class="section-final">
                    <tr>
                        <td>FINAL</td>
                        <td><textarea name="FINAL_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('finalSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
            </table>


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Fetch the subject code from the backend (passed from PHP to JavaScript)
                    const subjectCode = '<?php echo $subject_code; ?>'; // Dynamic PHP variable

                    // Fetch context data for the given subject code
                    if (subjectCode) {
                        fetch('fetch_context.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                if (Array.isArray(data) && data.length > 0) {
                                    populateContextTable(data); // Populate the table with the fetched context data
                                } else {
                                    console.warn('No context data found for subject code:', subjectCode);
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching context data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });

                // Function to populate the context table with the fetched data
                function populateContextTable(contextData) {
                    // Iterate over the fetched data
                    contextData.forEach(row => {
                        // Check if the row contains any valid data
                        if (row.hours || row.ilo || row.topics || row.institutional_values || row.teaching_activities || row.resources || row.assessment_tasks || row.course_map) {
                            // Determine the correct section based on the `section` field from the data (e.g., 'PRELIM', 'MIDTERM')
                            const sectionId = row.section.toLowerCase() + 'Section'; // e.g., 'prelimSection'
                            const sectionBody = document.getElementById(sectionId);

                            if (sectionBody) {
                                // Clone the first row to use as a template for each additional row
                                const newRow = sectionBody.querySelector('tr').cloneNode(true);

                                // Populate the cloned row with the data from the current row
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_hours[]"]').value = row.hours || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_ilo[]"]').value = row.ilo || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_topics[]"]').value = row.topics || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_institutional_values[]"]').value = row.institutional_values || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_teaching_activities[]"]').value = row.teaching_activities || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_resources[]"]').value = row.resources || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_assessment[]"]').value = row.assessment_tasks || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_course_map[]"]').value = row.course_map || '';

                                // Append the populated row to the section body
                                sectionBody.appendChild(newRow);
                            }
                        }
                    });
                }

                // Function to add a new row in the corresponding section
                function addRow(sectionId) {
                    const tableBody = document.getElementById(sectionId);
                    const lastRow = tableBody.querySelector('tr:last-child'); // Get the last row in the section
                    const newRow = lastRow.cloneNode(true); // Clone the last row

                    // Clear the values in the cloned row's textareas
                    const textareas = newRow.getElementsByTagName('textarea');
                    for (let i = 0; i < textareas.length; i++) {
                        textareas[i].value = ''; // Clear the textarea value
                    }

                    // Append the new row to the section
                    tableBody.appendChild(newRow);
                }

                // Function to remove a row from the corresponding section
                function removeRow(button) {
                    const tableBody = button.closest('tbody'); // Get the section (tbody)
                    if (tableBody.rows.length > 1) {
                        button.closest('tr').remove(); // Remove the row if there is more than one row
                    } else {
                        alert("At least one row must remain.");
                    }
                }
            </script>
            <h4>Performance Tasks</h4>
            <textarea name="performance_tasks" class="autoResizeTextarea" placeholder="Enter Performance Tasks"><?php echo htmlspecialchars($performance_tasks); ?></textarea>



            <h4>XII. Grading System</h4>
            <table id="gradingTable" class="custom-table" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>Criteria</th>
                        <th>Percentage</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Written Task Section -->
                    <tr>
                        <td colspan="3"><span class="red-text">Written Task</span></td>
                    </tr>

                    <?php if (!empty($written_task_criteria)) : ?>
                        <?php foreach ($written_task_criteria as $criteria => $percentage) : ?>
                            <tr class="written-task-row" data-section="written_task">
                                <td><input type="text" name="written_task_criteria[]" value="<?php echo htmlspecialchars($criteria); ?>"></td>
                                <td><input type="number" name="written_task_percentage[]" value="<?php echo (int)$percentage; ?>" style="width: 60px;" min="0" max="100" />%</td>
                                <td>
                                    <button type="button" onclick="deleteRow(this)">Delete</button>
                                    <input type="hidden" name="written_task_delete[]" value="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="written-task-row initial-row" data-section="written_task">
                            <td><input type="text" name="written_task_criteria[]" placeholder="Enter Criteria"></td>
                            <td><input type="number" name="written_task_percentage[]" value="0" style="width: 60px;" min="0" max="100" />%</td>
                            <td>
                                <button type="button" onclick="deleteRow(this)">Delete</button>
                                <input type="hidden" name="written_task_delete[]" value="0">
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td colspan="3">
                            <button type="button" onclick="addCriteria('written_task')">Add Written Task Criteria</button>
                        </td>
                    </tr>

                    <!-- Performance Tasks Section -->
                    <tr>
                        <td colspan="3"><span class="red-text">Performance Tasks</span></td>
                    </tr>

                    <?php if (!empty($performance_task_criteria)) : ?>
                        <?php foreach ($performance_task_criteria as $criteria => $percentage) : ?>
                            <tr class="performance-task-row" data-section="performance_task">
                                <td><input type="text" name="performance_task_criteria[]" value="<?php echo htmlspecialchars($criteria); ?>"></td>
                                <td><input type="number" name="performance_task_percentage[]" value="<?php echo (int)$percentage; ?>" style="width: 60px;" min="0" max="100" />%</td>
                                <td>
                                    <button type="button" onclick="deleteRow(this)">Delete</button>
                                    <input type="hidden" name="performance_task_delete[]" value="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="performance-task-row initial-row" data-section="performance_task">
                            <td><input type="text" name="performance_task_criteria[]" placeholder="Enter Criteria"></td>
                            <td><input type="number" name="performance_task_percentage[]" value="0" style="width: 60px;" min="0" max="100" />%</td>
                            <td>
                                <button type="button" onclick="deleteRow(this)">Delete</button>
                                <input type="hidden" name="performance_task_delete[]" value="0">
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td colspan="3">
                            <button type="button" onclick="addCriteria('performance_task')">Add Performance Task Criteria</button>
                        </td>
                    </tr>

                    <!-- Quarterly Assessment Section -->
                    <tr>
                        <td colspan="3"><span class="red-text">Quarterly Assessment</span></td>
                    </tr>

                    <?php if (!empty($quarterly_assessment_criteria)) : ?>
                        <?php foreach ($quarterly_assessment_criteria as $criteria => $percentage) : ?>
                            <tr class="quarterly-assessment-row" data-section="quarterly_assessment">
                                <td><input type="text" name="quarterly_assessment_criteria[]" value="<?php echo htmlspecialchars($criteria); ?>"></td>
                                <td><input type="number" name="quarterly_assessment_percentage[]" value="<?php echo (int)$percentage; ?>" style="width: 60px;" min="0" max="100" />%</td>
                                <td>
                                    <button type="button" onclick="deleteRow(this)">Delete</button>
                                    <input type="hidden" name="quarterly_assessment_delete[]" value="0">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="quarterly-assessment-row initial-row" data-section="quarterly_assessment">
                            <td><input type="text" name="quarterly_assessment_criteria[]" placeholder="Enter Criteria"></td>
                            <td><input type="number" name="quarterly_assessment_percentage[]" value="0" style="width: 60px;" min="0" max="100" />%</td>
                            <td>
                                <button type="button" onclick="deleteRow(this)">Delete</button>
                                <input type="hidden" name="quarterly_assessment_delete[]" value="0">
                            </td>
                        </tr>
                    <?php endif; ?>

                    <tr>
                        <td colspan="3">
                            <button type="button" onclick="addCriteria('quarterly_assessment')">Add Quarterly Assessment Criteria</button>
                        </td>
                    </tr>

                    <!-- Total -->
                    <tr>
                        <td><strong>TOTAL Grade Percentage</strong></td>
                        <td><strong>100%</strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <script>
                function addCriteria(section) {
                    const tableBody = document.querySelector(`#gradingTable tbody`);
                    const sectionRows = Array.from(tableBody.querySelectorAll(`tr[data-section="${section}"]`));
                    const lastRow = sectionRows.length > 0 ? sectionRows[sectionRows.length - 1] : tableBody.querySelector(`.section-header[data-section="${section}"]`);

                    // Add new row only if there's no row with empty criteria
                    let rowHTML = `
        <tr class="${section}-row" data-section="${section}">
            <td><input type="text" name="${section}_criteria[]" placeholder="Enter Criteria"></td>
            <td><input type="number" name="${section}_percentage[]" value="0" style="width: 60px;" min="0" max="100" />%</td>
            <td>
                <button type="button" class="delete-btn" onclick="deleteRow(this)">Delete</button>
                <input type="hidden" name="${section}_delete[]" value="0">
            </td>
        </tr>
        `;

                    if (lastRow) {
                        lastRow.insertAdjacentHTML("afterend", rowHTML);
                    }
                    updateDeleteButtons(section);
                }

                function deleteRow(button) {
                    const row = button.closest('tr');
                    const section = row.getAttribute('data-section');
                    const sectionRows = document.querySelectorAll(`tr[data-section="${section}"]`);

                    // Prevent deletion if there's only one row remaining
                    if (sectionRows.length <= 0) {
                        alert('At least one row must remain for each criteria.');
                        return; // Exit function if only one row left
                    }

                    const deleteFlagInput = row.querySelector('input[type="hidden"][name$="_delete[]"]');
                    deleteFlagInput.value = "1"; // Set delete flag to 1

                    row.style.display = "none"; // Hide the row instead of removing it
                }


                function validateData() {
                    let isValid = true;

                    // Ensure each section has at least one row with valid data
                    const sections = ['written_task', 'performance_task', 'quarterly_assessment'];
                    sections.forEach(section => {
                        const sectionRows = document.querySelectorAll(`tr[data-section="${section}"]`);
                        if (sectionRows.length === 0) {
                            isValid = false;
                            alert(`At least one row must be added for ${section.replace('_', ' ').toUpperCase()}.`);
                        }
                    });

                    // Check each row for missing criteria or percentages
                    document.querySelectorAll('tr[data-section]').forEach(row => {
                        const criteriaInput = row.querySelector('input[type="text"]');
                        const percentageInput = row.querySelector('input[type="number"]');

                        // Ensure there is no empty criteria input
                        if (criteriaInput && criteriaInput.value.trim() === "") {
                            isValid = false;
                            alert('Please fill in all criteria fields.');
                            criteriaInput.focus();
                        }

                        // Ensure there is no empty percentage input
                        if (percentageInput && percentageInput.value.trim() === "") {
                            isValid = false;
                            alert('Please fill in all percentage fields.');
                            percentageInput.focus();
                        }
                    });

                    return isValid;
                }
            </script>



            <!-- Signature Section -->
            <table id="signatureTable" class="custom-table">
                <tbody>
                    <tr>
                        <td rowspan="4" class="logo-cell">
                            <img src="../image.png" alt="Logo"><br>
                            <span class="red-text">Curriculum 2022</span>
                        </td>
                        <td class="title-cell red-text">COLLEGE OF COMPUTING AND INFORMATION SCIENCES</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="info-cell">
                            <span class="red-text">Prepared by:</span><br>
                            _____________________<br>
                            <input type="text" name="prepared_by" value="<?php echo $prepared_by; ?>" required>
                            <p>Subject Teacher</p>
                        </td>
                        <td class="signature-cell">
                            _____________________<br>Date
                        </td>
                    </tr>
                    <tr>
                        <td class="info-cell">
                            <span class="red-text">Resources Checked & Verified by:</span><br>
                            ______________________<br>
                            <input type="text" name="resource_checked_by" value="<?php echo $resource_checked_by; ?>" required>
                            <p>College Librarian</p>
                        </td>
                        <td class="signature-cell">
                            _____________________<br>Date
                        </td>
                    </tr>
                    <tr>
                        <td class="info-cell">
                            <span class="red-text">Reviewed by:</span><br>
                            ______________________<br>
                            <input type="text" name="reviewed_by_program_chair" value="<?php echo $reviewed_by_program_chair; ?>" required>
                            <p>BSIT Program Chair</p><br>
                            ______________________<br>
                            <input type="text" name="reviewed_by_dean" value="<?php echo $reviewed_by_dean; ?>" required>
                            <p>Dean</p>
                        </td>
                        <td class="signature-cell">
                            _____________________<br>Date
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" class="info-cell-approved">
                            <span class="red-text">Approved by:</span><br>
                            ______________________<br>
                            <input type="text" name="approved_by" value="<?php echo $approved_by; ?>" required>
                            <p>Vice President for Academic Affairs and Research</p>
                        </td>
                        <td class="signature-cell">
                            _____________________<br>Date
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- Signature Section -->

            <!-- Submit Button -->
            <button class="submit-button" type="submit" name="save_syllabus"
                <?php echo ($status === 'APPROVED') ? 'disabled' : ''; ?>>
                Submit Syllabus
            </button>


            <!-- Back Button -->
            <button class="back-button" type="button" onclick="window.location.href='index.php';">Back</button>

            <div class="divFooter">
                <img src="../footer.png" alt="Membership Logos" class="member-logos">
            </div>
        </form>
    </div>

</body>
<script>
    //    function checkFormValues() {
    //       const subjectCode = document.getElementById('syllabus_subject_code').value;
    //       const subjectName = document.getElementById('syllabus_subject_name').value;

    // Log the values to verify if they are correct
    //      console.log("Form Submitted - Subject Code:", subjectCode);
    //      console.log("Form Submitted - Subject Name:", subjectName);

    // Returning true to allow form submission to continue
    //      return true;
    //  }
</script>

</div>
</div>

</html>