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
        } else {
            echo '<script>alert("localhost says: No syllabus data found for subject code: ' . htmlspecialchars($subject_code) . '");</script>';
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Error preparing syllabus query: " . $conn->error;
    }

    // Fetch PILO-GILO mappings
    $sqlPiloGilo = "SELECT * FROM pilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlPiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $pilo_gilo[] = [
                'pilo' => htmlspecialchars($row['pilo']),
                'gilo' => htmlspecialchars($row['gilo'])
            ];
        }
        $stmt->close();
    }

    // Fetch CILO-GILO mappings
    $sqlCiloGilo = "SELECT * FROM cilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlCiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $cilos[] = [
                'description' => htmlspecialchars($row['cilo_description']),
                'gilo1' => htmlspecialchars($row['gilo1']),
                'gilo2' => htmlspecialchars($row['gilo2'])
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

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Syllabus</title>
    <link rel="stylesheet" href="../syllabus.css">
    <link rel="stylesheet" href="print.css">
    <style>
        /* Hide buttons during print */
        @media print {

            .print-button,
            .back-button {
                display: none;
            }

            /* Adjust body and table for print margins */
            body,
            html {
                width: 100%;
                margin: 0;
                padding: 0;
            }

            /* Ensure the table fits within the page */
            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                word-wrap: break-word;
                page-break-inside: avoid;
                margin: 0 auto;
            }

            th,
            td {
                border: 1px solid black;
                padding: 6px;
                text-align: left;
                overflow-wrap: break-word;
                word-wrap: break-word;
                white-space: normal;
                /* Ensure long content wraps within the cell */
            }

            th {
                background-color: #3498db;
                color: white;
            }

            /* Prevent table rows from splitting across pages */
            tr {
                page-break-inside: avoid;
            }

            tbody {
                page-break-before: auto;
                page-break-after: auto;
            }

            /* Ensures background colors are retained when printing */
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Force page size and margins to avoid clipping */
            @page {
                size: A4;
                margin: 10mm;
            }
        }

        /* Default table styling for screen */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            /* Prevents the table from resizing */
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            overflow-wrap: break-word;
            word-wrap: break-word;
            white-space: normal;
            /* Ensures long text wraps in the cell */
        }

        th {
            background-color: #3498db;
            color: white;
        }

        /* Ensure the table remains within the container */
        table {
            max-width: 100%;
            /* Prevents the table from overflowing its container */
            margin: 0 auto;
        }

        /* Adjust cells that contain long words without spaces */
        td {
            word-break: break-word;
            /* Forces long words to break */
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

    <!-- Header Section -->
    <div class="divHeader">
        <div class="headContents">
            <img src="../smcclogo.jfif" alt="SMCC Logo" class="logo">
        </div>
        <div class="headContents" style="text-align: center; font-size: 21px;">
            <div style="font-size: x-large; color: rgba(28, 6, 80, 0.877); font-family: Calambria;">Saint Michael College of Caraga</div>
            <div style="font-size: medium;">
                <div style="font-family: Bookman Old Style;">Brgy. 4, Nasipit, Agusan del Norte, Philippines</div>
                <div style="font-family: Calambria;">&emsp;Tel. Nos. +63 085 343-3251 / +63 085 283-3113 Fax No. +63 085 808-0892 &emsp;</div>
                <div style="font-family: Bookman Old Style;"><a href="https://www.smccnasipit.edu.ph/">www.smccnasipit.edu.ph</a></div>
            </div>
        </div>
        <div class="headContents">
            <img src="ISO&PAB.png" alt="Accreditation Logos" class="logo">
        </div>
    </div>

    <!-- Main Content Section -->
    <div class="container">
        <h2>Syllabus Information</h2>

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
                <th>Program Intended Learning Outcomes (PILOs) <br><br> After completion of the program, the student must be able to:</th>
                <th>Graduate Intended Learning Outcomes (GILOs)</th>
            </tr>
            <?php if (!empty($pilo_gilo)) {
                foreach ($pilo_gilo as $mapping) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mapping['pilo']); ?></td>
                        <td><?php echo htmlspecialchars($mapping['gilo']); ?></td>
                    </tr>
            <?php }
            } else {
                echo "<tr><td colspan='2'>No PILOs-GILOs data available.</td></tr>";
            } ?>
        </table>


        <!-- Course Intended Learning Outcomes -->
        <h3>Course Intended Learning Outcomes (CILO)</h3>
        <h4>Course Mapping</h4>
        <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

        <!-- CILO-GILO Table -->
        <table id="ciloGiloTable">
            <tr>
                <th>Course Intended Learning Outcomes (CILOs)<br><br>
                    After completion of the program, the student must be able to:</th>
                <th>GILO 1</th>
                <th>GILO 2</th>
            </tr>
            <?php if (!empty($cilos)) {
                foreach ($cilos as $cilo) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cilo['description']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['gilo1']); ?></td>
                        <td><?php echo htmlspecialchars($cilo['gilo2']); ?></td>
                    </tr>
            <?php }
            } else {
                echo "<tr><td colspan='3'>No CILOs-GILOs data available.</td></tr>";
            } ?>
        </table>


        <!-- Context Table -->
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
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($context)) {
                    foreach ($context as $row) { ?>
                        <tr>
                            <td><?php echo $row['section']; ?></td>
                            <td><?php echo $row['hours']; ?></td>
                            <td><?php echo $row['ilo']; ?></td>
                            <td><?php echo $row['topics']; ?></td>
                            <td><?php echo $row['institutional_values']; ?></td>
                            <td><?php echo $row['teaching_activities']; ?></td>
                            <td><?php echo $row['resources']; ?></td>
                            <td><?php echo $row['assessment']; ?></td>
                            <td><?php echo $row['course_map']; ?></td>
                        </tr>
                <?php }
                } else {
                    echo "<tr><td colspan='9'>No context data available.</td></tr>";
                } ?>
            </tbody>
        </table>

        <!-- Performance Tasks Section -->
        <h4>Performance Tasks</h4>
        <p><?php echo !empty($performance_tasks) ? htmlspecialchars($performance_tasks) : 'No data available'; ?></p>

        <!-- Print Button -->
        <button class="print-button" onclick="printSyllabus()">Print</button>
        <button class="back-button" type="button" onclick="window.location.href='index.php';">Back</button>
        <!-- Download Word Button -->
        <button class="download-button" onclick="window.location.href='../download_syllabus.php?subject_code=<?php echo $subject_code; ?>&subject_name=<?php echo $subject_name; ?>'">Download as Word</button>


        <script>
            function printSyllabus() {
                window.print();
            }
        </script>

    </div>

</body>

</html>