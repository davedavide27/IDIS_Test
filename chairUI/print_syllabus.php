<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: ../login.php");
    exit();
}

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

// Initialize variables
$subject_code = "";
$subject_name = "";
$course_units = "";
$course_description = "";
$prerequisites_corequisites = "";
$contact_hours = "";
$performance_tasks = "";
$status = "PENDING"; // Default status for syllabus
$cilos = [];
$pilo_gilo = [];
$context = [];
$prepared_by = "";
$prepared_date = "";
$resource_checked_by = "";
$resource_checked_date = "";
$reviewed_by_program_chair = "";
$reviewed_by_dean = "";
$reviewed_by_date = "";
$approved_by = "";
$approved_by_date = "";

// Check if subject_code and subject_name are provided through GET
if (isset($_GET['subject_code']) && isset($_GET['subject_name'])) {
    $subject_code = $_GET['subject_code'];
    $subject_name = $_GET['subject_name'];

    // Fetch syllabus data
    $sqlSyllabus = "SELECT * FROM syllabus WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlSyllabus)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If record exists, fetch the data
            $row = $result->fetch_assoc();
            $course_units = htmlspecialchars($row['course_units']);
            $course_description = htmlspecialchars($row['course_description']);
            $prerequisites_corequisites = htmlspecialchars($row['prerequisites_corequisites']);
            $contact_hours = htmlspecialchars($row['contact_hours']);
            $performance_tasks = htmlspecialchars($row['performance_tasks']);
            $status = htmlspecialchars($row['status']);  // Get the syllabus status
            $prepared_by = htmlspecialchars($row['prepared_by']);
            $prepared_by_date = !empty($row['prepared_by_date']) ? htmlspecialchars($row['prepared_by_date']) : date('Y-m-d');
            $resource_checked_by = htmlspecialchars($row['resource_checked_by']);
            $resource_checked_by_date = !empty($row['resource_checked_by_date']) ? htmlspecialchars($row['resource_checked_by_date']) : date('Y-m-d');
            $reviewed_by_program_chair = htmlspecialchars($row['reviewed_by_program_chair']);
            $reviewed_by_dean = htmlspecialchars($row['reviewed_by_dean']);
            $reviewed_by_date = !empty($row['reviewed_by_date']) ? htmlspecialchars($row['reviewed_by_date']) : date('Y-m-d');
            $approved_by = htmlspecialchars($row['approved_by']);
            $approved_by_date = !empty($row['approved_by_date']) ? htmlspecialchars($row['approved_by_date']) : date('Y-m-d');
        } else {
            // If no record exists, show a message instead of inserting a new record
            echo "<script>
            alert('No syllabus data for $subject_code $subject_name.');
            window.location.href = 'index.php';
          </script>";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing syllabus query: " . $conn->error;
    }


    // Fetch grading criteria data
    $sqlGradingSystem = "SELECT * FROM grading_system WHERE subject_code = ?";
    if ($stmtGradingSystem = $conn->prepare($sqlGradingSystem)) {
        $stmtGradingSystem->bind_param("s", $subject_code);
        $stmtGradingSystem->execute();
        $resultGradingSystem = $stmtGradingSystem->get_result();
        while ($row = $resultGradingSystem->fetch_assoc()) {
            $criteria_type = $row['criteria_type'];
            $criteria_name = $row['criteria_name'];
            $percentage = $row['percentage'];
            if ($criteria_type === 'written_task') {
                $written_task_criteria[$criteria_name] = $percentage;
            } elseif ($criteria_type === 'performance_task') {
                $performance_task_criteria[$criteria_name] = $percentage;
            } elseif ($criteria_type === 'quarterly_assessment') {
                $quarterly_assessment_criteria[$criteria_name] = $percentage;
            }
        }
        $stmtGradingSystem->close();
    }

    // Fetch PILO-GILO mappings with updated columns
    $sqlPiloGilo = "SELECT pilo, a, b, c, d FROM pilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlPiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $pilo_gilo[] = [
                'pilo' => htmlspecialchars($row['pilo']),
                'a' => htmlspecialchars($row['a']),
                'b' => htmlspecialchars($row['b']),
                'c' => htmlspecialchars($row['c']),
                'd' => htmlspecialchars($row['d'])
            ];
        }
        $stmt->close();
    }

    // Fetch CILO-GILO mappings with updated columns (a-o)
    $sqlCiloGilo = "SELECT cilo_description, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o FROM cilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlCiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $cilos[] = [
                'cilo_description' => htmlspecialchars($row['cilo_description']),
                'a' => htmlspecialchars($row['a']),
                'b' => htmlspecialchars($row['b']),
                'c' => htmlspecialchars($row['c']),
                'd' => htmlspecialchars($row['d']),
                'e' => htmlspecialchars($row['e']),
                'f' => htmlspecialchars($row['f']),
                'g' => htmlspecialchars($row['g']),
                'h' => htmlspecialchars($row['h']),
                'i' => htmlspecialchars($row['i']),
                'j' => htmlspecialchars($row['j']),
                'k' => htmlspecialchars($row['k']),
                'l' => htmlspecialchars($row['l']),
                'm' => htmlspecialchars($row['m']),
                'n' => htmlspecialchars($row['n']),
                'o' => htmlspecialchars($row['o'])
            ];
        }
        $stmt->close();
    }

    // Fetch context data
    $sqlContext = "SELECT * FROM context WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlContext)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $context[] = [
                'section' => htmlspecialchars($row['section']),
                'hours' => htmlspecialchars($row['hours']),
                'ilo' => htmlspecialchars($row['ilo']),
                'topics' => htmlspecialchars($row['topics']),
                'institutional_values' => htmlspecialchars($row['institutional_values']),
                'teaching_activities' => htmlspecialchars($row['teaching_activities']),
                'resources' => htmlspecialchars($row['resources']),
                'assessment' => htmlspecialchars($row['assessment_tasks']),
                'course_map' => htmlspecialchars($row['course_map'])
            ];
        }
        $stmt->close();
    }
} else {
    echo "<script>
            alert('Subject code or name not provided.');
            window.location.href = 'index.php';
          </script>";
    exit();
}

// Handle "Approve" button click
if (isset($_POST['approve'])) {
    // Update the status to "APPROVED" in the syllabus table
    $sqlUpdateSyllabus = "UPDATE syllabus SET status = 'APPROVED' WHERE subject_code = ?";
    $stmtUpdateSyllabus = $conn->prepare($sqlUpdateSyllabus);
    $stmtUpdateSyllabus->bind_param("s", $subject_code);

    // Update the status in the related tables (cilo_gilo_map, pilo_gilo_map, context)
    $sqlUpdateCiloGilo = "UPDATE cilo_gilo_map SET status = 'APPROVED' WHERE subject_code = ?";
    $sqlUpdatePiloGilo = "UPDATE pilo_gilo_map SET status = 'APPROVED' WHERE subject_code = ?";
    $sqlUpdateContext = "UPDATE context SET status = 'APPROVED' WHERE subject_code = ?";

    // Prepare and execute the queries
    $stmtUpdateCiloGilo = $conn->prepare($sqlUpdateCiloGilo);
    $stmtUpdatePiloGilo = $conn->prepare($sqlUpdatePiloGilo);
    $stmtUpdateContext = $conn->prepare($sqlUpdateContext);

    $stmtUpdateCiloGilo->bind_param("s", $subject_code);
    $stmtUpdatePiloGilo->bind_param("s", $subject_code);
    $stmtUpdateContext->bind_param("s", $subject_code);

    // Execute all updates
    if (
        $stmtUpdateSyllabus->execute() &&
        $stmtUpdateCiloGilo->execute() &&
        $stmtUpdatePiloGilo->execute() &&
        $stmtUpdateContext->execute()
    ) {
        // Trigger JavaScript alert after successful approval
        echo "<script>alert('Subject code $subject_code and all related data are approved');</script>";
        // Refresh the page to reflect the updated status
        echo "<script>window.location.href = '?subject_code=$subject_code&subject_name=$subject_name';</script>";
    } else {
        echo "Error updating status: " . $stmtUpdateSyllabus->error;
    }

    // Close the prepared statements
    $stmtUpdateSyllabus->close();
    $stmtUpdateCiloGilo->close();
    $stmtUpdatePiloGilo->close();
    $stmtUpdateContext->close();
}

// Handle "Deny" button click
if (isset($_POST['deny'])) {
    // Update the status to "DENIED" in the syllabus table
    $sqlUpdateSyllabus = "UPDATE syllabus SET status = 'DENIED' WHERE subject_code = ?";
    $stmtUpdateSyllabus = $conn->prepare($sqlUpdateSyllabus);
    $stmtUpdateSyllabus->bind_param("s", $subject_code);

    // Execute the query
    if ($stmtUpdateSyllabus->execute()) {
        echo "<script>alert('Subject code $subject_code has been denied');</script>";
        // Refresh the page to reflect the updated status
        echo "<script>window.location.href = '?subject_code=$subject_code&subject_name=$subject_name';</script>";
    } else {
        echo "Error updating status: " . $stmtUpdateSyllabus->error;
    }

    // Close the prepared statement
    $stmtUpdateSyllabus->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Syllabus</title>
    <link rel="stylesheet" href="../syllabus.css">
    <link rel="stylesheet" href="custom_table.css">
    <style>
        /* Hide buttons and status during print */
        @media print {

            .print-button,
            .back-button,
            .status-container,
            .status-button {
                display: none;
                /* Hide specific elements during print */
            }

            .container {
                max-width: 1500px;
                /* Set the width to fit A4 for printing */
                margin: 40px auto;
                padding: 20px;
                background-color: rgba(255, 255, 255, 0.9);
                /* Transparent white */
                border-radius: 8px;
                box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            }

            /* Adjust body and table for print margins */
            body,
            html {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            table {
                width: 100%;
                margin: 0 auto;
                /* Center the table */
                border-collapse: collapse;
                /* Ensure no double borders */
            }

            th,
            td {
                border: 1px solid black;
                /* Ensure visible borders in print */
                padding: 6px;
                text-align: left;
                overflow-wrap: break-word;
                word-wrap: break-word;
                white-space: normal;
            }

            th {
                background-color: #3498db;
                /* Keep the header background in print */
                color: white;
            }

            tr {
                page-break-inside: avoid;
                /* Prevent rows from splitting across pages */
            }

            body {
                -webkit-print-color-adjust: exact;
                /* Ensure exact colors in print */
                print-color-adjust: exact;
            }

            @page {
                size: A4;
                /* Set the print size */
                margin: 10mm;
            }

            .context-styled-table {
                page-break-inside: avoid;
                table-layout: auto;
                /* Auto layout to fit printing width */
            }

            .context-styled-table th,
            .context-styled-table td {
                font-size: 10pt;
                /* Ensures text fits when printed */
                padding: 5px;
            }

        }

        /* Default table styling for the screen */
        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            overflow-wrap: break-word;
            word-wrap: break-word;
            white-space: normal;
        }

        th {
            background-color: #3498db;
            /* Blue header for screen */
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            /* Avoid double borders */
        }

        td {
            word-break: break-word;
            /* Break long words for proper table display */
        }

        /* Status Button Styling */
        .status-button {
            padding: 8px 16px;
            /* Increase padding for better button size */
            font-size: 14px;
            /* Slightly larger font size for readability */
            color: white;
            /* Text color */
            border: none;
            /* Remove border */
            border-radius: 5px;
            /* Rounded corners for the button */
            cursor: default;
            /* Cursor stays as default */
            font-weight: bold;
            /* Bold text for emphasis */
            text-transform: uppercase;
            /* Uppercase text for consistency */
            transition: background-color 0.3s ease, transform 0.3s ease;
            /* Smooth transitions */
        }

        /* Status color indicators */
        .status-button.pending {
            background-color: red;
            /* Red for PENDING */
        }

        .status-button.approved {
            background-color: green;
            /* Green for APPROVED */
        }

        .status-button.denied {
            background-color: yellow;
            /* Yellow for DENIED */
            color: black;
            /* Black text for contrast on yellow */
        }

        /* Button hover effects */
        .status-button:hover {
            transform: scale(1.05);
            /* Slightly enlarge the button on hover */
        }

        /* Remove outline on button focus */
        .status-button:focus {
            outline: none;
        }


        /* Button container to align the buttons side by side */
        .button-container {
            display: flex;
            justify-content: left;
            /* Center the buttons */
            gap: 5px;
            /* Add space between buttons */
            margin-top: 10px;
        }

        /* Adjust back button styling to match the approve button */
        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #dc3545;
            /* Red background */
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            height: 0%;
        }

        .back-button:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .back-button:active {
            background-color: #bd2130;
            transform: translateY(1px);
        }

        /* Same approve button styling */
        .approve-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #28a745;
            /* Green background */
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .approve-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .approve-button:active {
            background-color: #1e7e34;
            transform: translateY(1px);
        }

        /* Same deny button styling */
        .deny-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #ffc107;
            /* Yellow background */
            color: black;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .deny-button:hover {
            background-color: #e0a800;
            /* Darker yellow */
            transform: translateY(-2px);
        }

        .deny-button:active {
            background-color: #c69500;
            /* Even darker yellow */
            transform: translateY(1px);
        }

        /* Context Table Specific Styling */
        .context-styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            table-layout: fixed;
        }

        .context-styled-table th,
        .context-styled-table td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
        }

        .context-styled-table th {
            background-color: #3498db;
            color: white;
        }

        .context-styled-table td {
            background-color: white;
        }

        /* Formatting for ILO/Competency column */
        .context-styled-table td:nth-child(2) strong {
            color: red;
            display: block;
            margin-bottom: 4px;
            margin-top: 4px;
        }

        /* Performance Task Row Styling */
        /* Performance Task Row Styling */
        .performance-task {
            background-color: #f9f9f9;
            padding: 5px;
            /* Reduced padding */
            font-weight: bold;
            text-align: left;
            border-top: 2px solid #3498db;
            margin: 0;
            /* Remove any margins */
            line-height: 1.2;
            /* Adjust line spacing */
            word-wrap: break-word;
        }

        /* Style for the disabled buttons */
        button:disabled {
            background-color: #ccc;
            /* Light gray background */
            color: #666;
            /* Darker gray text */
            border: 1px solid #999;
            /* Light gray border */
            cursor: not-allowed;
            /* Not-allowed cursor */
        }

        /* Style for buttons in the active state */
        button {
            background-color: #4CAF50;
            /* Green background */
            color: white;
            /* White text */
            border: none;
            /* No border */
            padding: 10px 20px;
            /* Padding for buttons */
            text-align: center;
            /* Center text */
            text-decoration: none;
            /* Remove underline */
            display: inline-block;
            /* Allow multiple buttons on the same line */
            font-size: 16px;
            /* Font size */
            margin: 4px 2px;
            /* Margin between buttons */
            transition: background-color 0.3s ease;
            /* Smooth background transition */
        }

        button:hover {
            background-color: #45a049;
            /* Darker green on hover */
        }
        
    </style>

</head>

<body>
    <!-- Display any session error messages -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <p><?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?></p>
        </div>
    <?php endif; ?>

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
                    <img src="../ISO&PAB.png" alt="Accreditation Logos" class="logo">
                </div>
            </div>
        </div>
        <h2>Syllabus Information</h2>

        <!-- Display Course Information -->
        <ul>
            <li class="status-container"><b>Status</b>
                <button class="status-button <?php echo strtolower($status); ?>">
                    <?php echo htmlspecialchars($status); ?>
                </button>
            </li>
        </ul>



        <!-- Vision, Mission, Goal, Objectives, Michaelinian Identity -->
        <h3>I. School's Vision, Mission, Goal, Objectives, Michaelinian Identity</h3>

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

        <h4>Program Mapping</h4>
        <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

        <!-- PILO-GILO Table -->
        <table id="piloGiloTable">
            <tr>
                <!-- Header for PILO section -->
                <th rowspan="2">Program Intended Learning Outcomes (PILOs) <br><br> After completion of the program, the student must be able to:</th>

                <!-- Header for GILO section -->
                <th colspan="4" style="text-align:center">Graduate Intended Learning Outcomes (GILOs)</th>
            </tr>
            <tr>
                <!-- Adding GILO labels for columns a to d -->
                <th>a</th>
                <th>b</th>
                <th>c</th>
                <th>d</th>
            </tr>
            <?php if (!empty($pilo_gilo)) {
                foreach ($pilo_gilo as $mapping) { ?>
                    <tr>
                        <!-- Display PILO description -->
                        <td><?php echo htmlspecialchars($mapping['pilo']); ?></td>
                        <!-- Display each GILO value (from a to d) -->
                        <td><?php echo htmlspecialchars($mapping['a']); ?></td>
                        <td><?php echo htmlspecialchars($mapping['b']); ?></td>
                        <td><?php echo htmlspecialchars($mapping['c']); ?></td>
                        <td><?php echo htmlspecialchars($mapping['d']); ?></td>
                    </tr>
            <?php }
            } else {
                echo "<tr><td colspan='5'>No PILOs-GILOs data available.</td></tr>";
            } ?>
        </table>

        <!-- Display Course Information -->
        <h3>Course Information</h3>
        <ul>
            <li><b>Course Code:</b> <?php echo htmlspecialchars($subject_code); ?></li>
            <li><b>Course Name:</b> <?php echo htmlspecialchars($subject_name); ?></li>
            <li><b>Course Units:</b> <?php echo !empty($course_units) ? htmlspecialchars($course_units) : 'No data available'; ?></li>
            <li><b>Course Description:</b> <?php echo !empty($course_description) ? htmlspecialchars($course_description) : 'No data available'; ?></li>
            <li><b>Prerequisites:</b> <?php echo !empty($prerequisites_corequisites) ? htmlspecialchars($prerequisites_corequisites) : 'No data available'; ?></li>
            <li><b>Contact Hours:</b> <?php echo !empty($contact_hours) ? htmlspecialchars($contact_hours) : 'No data available'; ?></li>
        </ul>

        <!-- Course Intended Learning Outcomes -->
        <h3>Course Intended Learning Outcomes (CILO)</h3>
        <h4>Course Mapping</h4>
        <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

        <!-- CILO-GILO Table -->
        <table id="ciloGiloTable">
            <tr>
                <th rowspan="2">Course Intended Learning Outcomes (CILOs)<br><br>
                    After completion of the course, the student must be able to:
                </th>
                <th colspan="15" style="text-align: center">Program Intended Learning Outcome (PILO)</th>
            </tr>
            <!-- Adding columns for a to o -->
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
            <?php if (!empty($cilos)) {
                foreach ($cilos as $cilo) { ?>
                    <tr>
                        <!-- Display CILO description -->
                        <td><?php echo htmlspecialchars($cilo['cilo_description']); ?></td>
                        <!-- Display each GILO value (from a to o) -->
                        <td><?php echo htmlspecialchars($cilo['a']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['b']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['c']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['d']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['e']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['f']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['g']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['h']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['i']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['j']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['k']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['l']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['m']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['n']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['o']); ?></td>
                    </tr>
            <?php }
            } else {
                echo "<tr><td colspan='16'>No CILOs-GILOs data available.</td></tr>";
            } ?>
        </table>


        <h4>Context</h4>
        <table id="contextTable" class="context-styled-table">
            <thead>
                <tr>
                    <th>Hour(s)</th>
                    <th>Intended Learning Outcomes (ILO) / Competency(ies)</th>
                    <th>Topics</th>
                    <th>Institutional Values</th>
                    <th>Teaching and Learning Activities</th>
                    <th>Resources/References</th>
                    <th>Assessment Tasks</th>
                    <th>Course Map</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($context)) {
                    $displayedSections = []; // Track displayed sections
                    foreach ($context as $row) {
                        // Display section header only once
                        if (!in_array(strtoupper($row['section']), $displayedSections)) {
                            $displayedSections[] = strtoupper($row['section']);
                ?>
                            <tr>
                                <td><?php echo $row['hours']; ?></td>
                                <td>
                                    <strong><?php echo strtoupper($row['section']); ?></strong><br>
                                    <?php echo nl2br($row['ilo']); ?>
                                </td>
                                <td><?php echo nl2br($row['topics']); ?></td>
                                <td><?php echo $row['institutional_values']; ?></td>
                                <td><?php echo nl2br($row['teaching_activities']); ?></td>
                                <td><?php echo nl2br($row['resources']); ?></td>
                                <td><?php echo nl2br($row['assessment']); ?></td>
                                <td><?php echo $row['course_map']; ?></td>
                            </tr>
                        <?php
                        } else {
                        ?>
                            <tr>
                                <td><?php echo $row['hours']; ?></td>
                                <td><?php echo nl2br($row['ilo']); ?></td>
                                <td><?php echo nl2br($row['topics']); ?></td>
                                <td><?php echo $row['institutional_values']; ?></td>
                                <td><?php echo nl2br($row['teaching_activities']); ?></td>
                                <td><?php echo nl2br($row['resources']); ?></td>
                                <td><?php echo nl2br($row['assessment']); ?></td>
                                <td><?php echo $row['course_map']; ?></td>
                            </tr>
                <?php
                        }
                    }
                } else {
                    echo "<tr><td colspan='8'>No context data available.</td></tr>";
                }
                ?>
                <!-- Performance Tasks Section -->
                <tr>
                    <td colspan="8" class="performance-task">
                        <strong>Performance Tasks:</strong><br>
                        <?php echo !empty($performance_tasks) ? nl2br(htmlspecialchars($performance_tasks)) : 'No performance tasks available'; ?>
                    </td>
                </tr>

            </tbody>
        </table>


        <h4>XII. Grading System</h4>
        <table id="gradingTable" class="custom-table" style="margin-bottom: 20px;">
            <thead>
                <tr>
                    <th>Criteria</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <!-- Written Task Section -->
                <tr>
                    <td><span class="red-text">Written Task</span><br>
                        <?php if (!empty($written_task_criteria)) : ?>
                            <?php foreach ($written_task_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;- <?= htmlspecialchars($criteria); ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td><br>
                        <?php if (!empty($written_task_criteria)) : ?>
                            <?php foreach ($written_task_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;<?= (int)$percentage; ?>%<br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Performance Tasks Section -->
                <tr>
                    <td><span class="red-text">Performance Tasks</span><br>
                        <?php if (!empty($performance_task_criteria)) : ?>
                            <?php foreach ($performance_task_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;- <?= htmlspecialchars($criteria); ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td><br>
                        <?php if (!empty($performance_task_criteria)) : ?>
                            <?php foreach ($performance_task_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;<?= (int)$percentage; ?>%<br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Quarterly Assessment Section -->
                <tr>
                    <td><span class="red-text">Quarterly Assesment</span><br>
                        <?php if (!empty($quarterly_assessment_criteria)) : ?>
                            <?php foreach ($quarterly_assessment_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;- <?= htmlspecialchars($criteria); ?><br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                    <td><br>
                        <?php if (!empty($quarterly_assessment_criteria)) : ?>
                            <?php foreach ($quarterly_assessment_criteria as $criteria => $percentage) : ?>
                                &nbsp;&nbsp;&nbsp;<?= (int)$percentage; ?>%<br>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </td>
                </tr>

                <!-- Total -->
                <tr>
                    <td><strong>TOTAL Grade Percentage</strong></td>
                    <td><strong class="red-text">100%</strong></td>
                </tr>
            </tbody>
        </table>



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
                <strong><?= $prepared_by; ?></strong><br>
                Subject Teacher
            </td>
            <td class="signature-cell">
                <div class="signature-date"><?= date('M. d, Y', strtotime($prepared_by_date)); ?></div>
                _____________<br>Date
            </td>
        </tr>
        <tr>
            <td class="info-cell">
                <span class="red-text">Resources Checked & Verified by:</span><br>
                <strong><?= $resource_checked_by; ?></strong><br>
                College Librarian
            </td>
            <td class="signature-cell">
                <div class="signature-date"><?= date('M. d, Y', strtotime($resource_checked_by_date)); ?></div>
                _____________<br>Date
            </td>
        </tr>
        <tr>
            <td class="info-cell">
                <span class="red-text">Reviewed by:</span><br>
                <strong><?= $reviewed_by_program_chair; ?></strong><br>
                BSIT Program Chair<br>
                <br>
                <br>
                <strong><?= $reviewed_by_dean; ?></strong><br>
                Dean
            </td>
            <td class="signature-cell">
                <div class="signature-date"><?= date('M. d, Y', strtotime($reviewed_by_date)); ?></div>
                _____________<br>Date
            </td>
        </tr>
        <tr>
            <td colspan="2" class="info-cell-approved">
                <span class="red-text">Approved by:</span><br>
                <strong><?= $approved_by; ?></strong><br>
                Vice President for Academic Affairs and Research
            </td>
            <td class="signature-cell">
                <div class="signature-date"><?= date('M. d, Y', strtotime($approved_by_date)); ?></div>
                _____________<br>Date
            </td>
        </tr>
    </tbody>
</table>





        <div class="button-container">
            <!-- Approve Syllabus Button (only show if status is not PENDING) -->
            <?php if ($status !== 'APPROVED'): ?>
                <form method="post" onsubmit="return confirmApprove()">
                    <button class="approve-button" type="submit" name="approve">Approve Syllabus</button>
                </form>
            <?php else: ?>
                <form method="post">
                    <button class="approve-button" type="button" disabled>Approve Syllabus</button>
                </form>
            <?php endif; ?>


            <!-- Deny Syllabus Button (only show if status is not PENDING) -->
            <?php if ($status !== 'APPROVED'): ?>
                <form method="post" onsubmit="return confirmDeny()">
                    <button class="deny-button" type="submit" name="deny">Deny Syllabus</button>
                </form>
            <?php else: ?>
                <form method="post">
                    <button class="deny-button" type="button" disabled>Deny Syllabus</button>
                </form>
            <?php endif; ?>


            <!-- Back Button -->
            <button class="back-button" type="button" onclick="window.location.href='index.php';">Back</button>
        </div>

        <!-- Footer -->
        <div class="divFooter">
            <img src="../footer.png" alt="Membership Logos" class="member-logos">
        </div>





    </div>

    <script>
        function printSyllabus() {
            window.print();
        }

        function confirmApprove() {
            return confirm('Approve Syllabus?');
        }

        function confirmDeny() {
            return confirm('Deny Syllabus?');
        }
    </script>

</body>

</html>