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
            $row = $result->fetch_assoc();
            $course_units = htmlspecialchars($row['course_units']);
            $course_description = htmlspecialchars($row['course_description']);
            $prerequisites_corequisites = htmlspecialchars($row['prerequisites_corequisites']);
            $contact_hours = htmlspecialchars($row['contact_hours']);
            $performance_tasks = htmlspecialchars($row['performance_tasks']);
            $status = htmlspecialchars($row['status']);  // Get the syllabus status
        } else {
            echo '<script>alert("No syllabus data found for subject code: ' . htmlspecialchars($subject_code) . '");</script>';
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing syllabus query: " . $conn->error;
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
    $_SESSION['error_message'] = "Subject code or name not provided.";
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
                font-size: 12pt;
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
            padding: 5px;
            font-size: 12px;
            color: white;
            border: none;
            cursor: default;
        }

        /* Status color indicators */
        .status-button.pending {
            background-color: red;
        }

        .status-button.approved {
            background-color: green;
        }

        /* Context Table Specific Styling */
        .context-styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px auto;
            table-layout: fixed;
            /* Prevents columns from being too wide */
        }

        .context-styled-table th,
        .context-styled-table td {
            border: 1px solid black;
            padding: 8px;
            /* Uniform padding for cells */
            text-align: left;
            vertical-align: top;
            word-wrap: break-word;
            /* Ensures content breaks within the cells */
        }

        .context-styled-table th {
            background-color: #3498db;
            color: white;
            font-size: 10pt;
        }

        .context-styled-table td {
            font-size: 10pt;
            background-color: white;
        }

        /* Formatting for ILO/Competency column */
        .context-styled-table td:nth-child(2) strong {
            color: red;
            /* Prelim, Midterm, etc. sections */
            display: block;
            margin-bottom: 4px;
            /* Reduced bottom margin */
            margin-top: 4px;
            /* Reduced top margin to balance section spacing */
        }

        .context-styled-table td {
            padding: 8px;
            /* Slightly reduced padding for better space utilization */
        }

        /* Bullet points inside table cells */
        .context-styled-table td ul {
            padding-left: 20px;
            /* Indents bullet points */
            margin: 0;
            /* Removes default margin */
        }

        .context-styled-table td ul li {
            list-style-type: disc;
            /* Disc style for bullet points */
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
            <li class="status-container"><b>Status</b> <button class="status-button <?php echo strtolower($status); ?>">
                    <?php echo htmlspecialchars($status); ?>
                </button></li>
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
                <tr>
                    <td><span class="red-text">Written Task</span><br>
                        &nbsp;&nbsp;&nbsp;- Quizzes<br>
                        &nbsp;&nbsp;&nbsp;- Written Task
                    </td>
                    <td><span class="red-text">30%</span><br>
                        &nbsp;&nbsp;&nbsp;15%<br>
                        &nbsp;&nbsp;&nbsp;15%
                    </td>
                </tr>
                <tr>
                    <td><span class="red-text">Performance Tasks</span><br>
                        &nbsp;&nbsp;&nbsp;- Attendance<br>
                        &nbsp;&nbsp;&nbsp;- Behavior<br>
                        &nbsp;&nbsp;&nbsp;- Performance/Product/Laboratory
                    </td>
                    <td><span class="red-text">40%</span><br>
                        &nbsp;&nbsp;&nbsp;5%<br>
                        &nbsp;&nbsp;&nbsp;5%<br>
                        &nbsp;&nbsp;&nbsp;30%
                    </td>
                </tr>
                <tr>
                    <td><span class="red-text">Quarterly Assessment</span></td>
                    <td><span class="red-text">30%</span></td>
                </tr>
                <tr>
                    <td><strong>TOTAL Grade Percentage</strong></td>
                    <td><strong>100%</strong></td>
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
                        <strong>DAISA O. GUPIT, MIT</strong><br>
                        Subject Teacher
                    </td>
                    <td class="signature-cell">_____________<br>Date</td>
                </tr>
                <tr>
                    <td class="info-cell">
                        <span class="red-text">Resources Checked & Verified by:</span><br>
                        <strong>CONTISZA C. ABADIEZ, RL</strong><br>
                        College Librarian
                    </td>
                    <td class="signature-cell">_____________<br>Date</td>
                </tr>
                <tr>
                    <td class="info-cell">
                        <span class="red-text">Reviewed by:</span><br>
                        <strong>MARLON JUHN TIMOGAN, MIT</strong><br>
                        BSIT Program Chair<br>
                        <strong>DAISA O. GUPIT, MIT</strong><br>
                        Dean
                    </td>
                    <td class="signature-cell">_____________<br>Date</td>
                </tr>
                <tr>
                    <td colspan="2" class="info-cell-approved">
                        <span class="red-text">Approved by:</span><br>
                        <strong>BEVERLY D. JAMINAL, Ed.D.</strong><br>
                        Vice President for Academic Affairs and Research
                    </td>
                    <td class="signature-cell">_____________<br>Date</td>
                </tr>
            </tbody>
        </table>

        <br>
        <!-- Print Button -->
        <button class="print-button" onclick="printSyllabus()">Print</button>
        <button class="back-button" type="button" onclick="window.location.href='index.php';">Back</button>
        <div class="divFooter">
            <img src="../footer.png" alt="Membership Logos" class="member-logos">
        </div>
    </div>

    <script>
        function printSyllabus() {
            window.print();
        }
    </script>
</body>

</html>