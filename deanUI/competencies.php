<?php
session_start();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$subjectCode = $subjectName = $units = $hours = $department = $schoolYearStart = $schoolYearEnd = '';
$gradingPeriod = $gradingQuarterStart = $gradingQuarterEnd = $totalCompetenciesDepEd = $totalCompetenciesSMCC = '';
$totalInstitutionalCompetencies = $totalCompetenciesBAndC = $totalCompetenciesImplemented = $totalCompetenciesNotImplemented = '';
$percentageCompetenciesImplemented = $preparedBy = $checkedBy = $notedBy = '';
$status = 'PENDING';  // Default status
$competencies = [];

// Fetch data based on subject_code and subject_name from GET
if (isset($_GET['subject_code']) && isset($_GET['subject_name'])) {
    $subjectCode = $_GET['subject_code'];
    $subjectName = $_GET['subject_name'];

    // Fetch all relevant data for the selected subject
    $sql = "SELECT * FROM competencies WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subjectCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch data dynamically from the database
        while ($row = $result->fetch_assoc()) {
            $units = $row['units'];
            $hours = $row['hours'];
            $department = $row['department'];
            $schoolYearStart = $row['school_year_start'];
            $schoolYearEnd = $row['school_year_end'];
            $gradingPeriod = $row['grading_period'];
            $gradingQuarterStart = $row['grading_quarter_start'];
            $gradingQuarterEnd = $row['grading_quarter_end'];
            $totalCompetenciesDepEd = $row['total_competencies_deped_tesda_ched'];
            $totalCompetenciesSMCC = $row['total_competencies_smcc'];
            $totalInstitutionalCompetencies = $row['total_institutional_competencies'];
            $totalCompetenciesBAndC = $row['total_competencies_b_and_c'];
            $totalCompetenciesImplemented = $row['total_competencies_implemented'];
            $totalCompetenciesNotImplemented = $row['total_competencies_not_implemented'];
            $percentageCompetenciesImplemented = $row['percentage_competencies_implemented'];
            $preparedBy = $row['prepared_by'];
            $checkedBy = $row['checked_by'];
            $notedBy = $row['noted_by'];
            $status = $row['status'];  // Fetch current status

            // Add competencies description and remarks to array
            $competencies[] = [
                'competency_description' => $row['competency_description'],
                'remarks' => $row['remarks']
            ];
        }
    }
    $stmt->close();
}

// Handle "Approve" button click
if (isset($_POST['approve'])) {
    // Update the status to "APPROVED"
    $sqlUpdateStatus = "UPDATE competencies SET status = 'APPROVED' WHERE subject_code = ?";
    $stmtUpdate = $conn->prepare($sqlUpdateStatus);
    $stmtUpdate->bind_param("s", $subjectCode);

    if ($stmtUpdate->execute()) {
        // Trigger JavaScript alert after successful approval
        echo "<script>alert('Subject code $subjectCode is approved');</script>";
        // Refresh the page to reflect the updated status
        echo "<script>window.location.href = '?subject_code=$subjectCode&subject_name=$subjectName';</script>";
    } else {
        echo "Error updating status: " . $stmtUpdate->error;
    }

    $stmtUpdate->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dean UI - View Competencies</title>
    <link rel="stylesheet" href="../view_competencies.css">
    <link rel="stylesheet" href="../header_footer_competency.css">
    <style>
        @media print {

            /* Hide buttons during print */
            .print-button,
            .back-button,
            .status-container,
            .status-button,
            .no-print {
                display: none;
            }

            /* Fixed header for print */
            .print-header {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                height: 100px;
                /* Adjust height as needed */
                background-color: white;
                /* Background color */
                z-index: 1000;
                /* Ensure it appears above content */
                padding: 10px;
                /* Padding for aesthetics */
            }

            /* Footer styles */
            .divFooter {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 70px;
                /* Adjust height for footer */
                background-color: white;
                /* Optional background */
                z-index: 1000;
                /* Ensure it appears above content */
                padding: 10px;
                /* Padding for aesthetics */
            }

            /* Ensure main content is not hidden behind the fixed header */
            .container {
                padding: 20px;
                /* Add padding to the content */
                margin-top: 120px;
                /* Adjust based on header height + extra space */
                margin-bottom: 90px;
                /* Adjust based on footer height + extra space */
            }

            /* Ensure content doesn't overlap with footer */
            .summary-table,
            .sign-section {
                margin-bottom: 30px;
                /* Adjust to ensure space above footer */
            }

            /* Additional styles can be added here */
        }


        .no-print {
            border-style: none;
            padding: 9px;
            border-radius: 10px;
            margin-top: 8px;
            margin-left: 8px;
            cursor: pointer;
        }

        .no-print:hover {
            background: #58abff;
        }

        .status-button {
            padding: 5px;
            font-size: 12px;
            color: white;
            border: none;
            cursor: default;
        }

        /* Red for PENDING */
        .status-button.pending {
            background-color: red;
        }

        /* Green for APPROVED */
        .status-button.approved {
            background-color: green;
        }

        /* Additional styles for the regular view */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
        }

        .college-name {
            font-size: 1.5em;
            font-weight: bold;
        }

        .logo {
            max-width: 100px;
            /* Adjust logo size */
        }

        /* Additional styles for other elements */
    </style>
</head>

<body>

    <div class="print-header">
        <div class="header-container">
            <div class="headContents header-left">
                <img src="../smcclogo.jfif" alt="SMCC Logo" class="logo">
            </div>
            <div class="headContents header-center">
                <div class="college-name">Saint Michael College of Caraga</div>
                <div class="college-details">
                    <div>Brgy. 4, Nasipit, Agusan del Norte, Philippines</div>
                    <div>Tel. Nos. +63 085 343-3251 / +63 085 283-3113 Fax No. +63 085 808-0892</div>
                    <div><a href="https://www.smccnasipit.edu.ph/">www.smccnasipit.edu.ph</a></div>
                </div>
            </div>
            <div class="headContents header-right">
                <img src="ISO&PAB.png" alt="Accreditation Logos" class="logo">
            </div>
        </div>
    </div>

    <div class="container">
        <h2 style="text-align: center;">COMPETENCY IMPLEMENTATION</h2>
        <table class="header-info">
            <tr>
                <th class="status-container">Status</th>
                <td>
                    <button class="status-button <?php echo strtolower($status); ?>">
                        <?php echo htmlspecialchars($status); ?>
                    </button>
                </td>
            </tr>
            <tr>
                <th>I. Subject code:</th>
                <td><?php echo htmlspecialchars($subjectCode); ?></td>
            </tr>
            <tr>
                <th>II. Subject title:</th>
                <td><?php echo htmlspecialchars($subjectName); ?></td>
            </tr>
            <tr>
                <th>III. Units:</th>
                <td><?php echo htmlspecialchars($units); ?> Units</td>
            </tr>
            <tr>
                <th>IV. Hours:</th>
                <td><?php echo htmlspecialchars($hours); ?> Hours</td>
            </tr>
            <tr>
                <th>V. Department:</th>
                <td><?php echo htmlspecialchars($department); ?></td>
            </tr>
            <tr>
                <th>VI. School year:</th>
                <td><?php echo htmlspecialchars($schoolYearStart . ' - ' . $schoolYearEnd); ?></td>
            </tr>
            <tr>
                <th>VII. Grading period/quarter:</th>
                <td>
                    <?php
                    echo htmlspecialchars($gradingPeriod) . ' <strong>FROM</strong> ' . htmlspecialchars($gradingQuarterStart) . ' <strong>TO</strong> ' . htmlspecialchars($gradingQuarterEnd);
                    ?>
                </td>
            </tr>
        </table>

        <table class="competency-table">
            <thead>
                <tr>
                    <th>SMCC Competencies</th>
                    <th>Remarks On Class</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($competencies)): ?>
                    <?php foreach ($competencies as $competency): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($competency['competency_description']); ?></td>
                            <td><?php echo htmlspecialchars($competency['remarks']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No competencies found for this subject.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <br>
        <div>
            <p>IX.</p>
        </div>

        <table class="summary-table">
            <tr>
                <td>A. Total Competencies DepEd/TESDA/CHED:</td>
                <td><?php echo htmlspecialchars($totalCompetenciesDepEd); ?></td>
            </tr>
            <tr>
                <td>B. Total Competencies SMCC:</td>
                <td><?php echo htmlspecialchars($totalCompetenciesSMCC); ?></td>
            </tr>
            <tr>
                <td>C. Total Institutional Competencies:</td>
                <td><?php echo htmlspecialchars($totalInstitutionalCompetencies); ?></td>
            </tr>
            <tr>
                <td>D. Total B and C Competencies:</td>
                <td><?php echo htmlspecialchars($totalCompetenciesBAndC); ?></td>
            </tr>
            <tr>
                <td>E. Total Competencies Implemented:</td>
                <td><?php echo htmlspecialchars($totalCompetenciesImplemented); ?></td>
            </tr>
            <tr>
                <td>F. Total Competencies NOT Implemented:</td>
                <td><?php echo htmlspecialchars($totalCompetenciesNotImplemented); ?></td>
            </tr>
            <tr>
                <td>G. % Competencies Implemented:</td>
                <td><?php echo htmlspecialchars($percentageCompetenciesImplemented); ?>%</td>
            </tr>
        </table>

        <div class="sign-section">
            <table style="width: 100%; text-align: center; border-spacing: 40px 0;">
                <tr>
                    <td><strong>Prepared by:</strong></td>
                    <td><strong>Checked by:</strong></td>
                    <td><strong>Noted by:</strong></td>
                </tr>
                <tr>
                    <td style="border-bottom: 1px solid black;"><?php echo htmlspecialchars($preparedBy); ?></td>
                    <td style="border-bottom: 1px solid black;"><?php echo htmlspecialchars($checkedBy); ?></td>
                    <td style="border-bottom: 1px solid black;"><?php echo htmlspecialchars($notedBy); ?></td>
                </tr>
                <tr>
                    <td>
                        <h4>Subject Teacher</h4>
                    </td>
                    <td>
                        <h4>Subject Coordinator</h4>
                    </td>
                    <td>
                        <h4>Dean</h4>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="divFooter">
        <div style="text-align: center;">
            <img src="../footer.png" alt="Membership Logos" class="member-logos">
        </div>
    </div>

    <button class="no-print" onclick="hidePrintHeaders()">Print this page</button>
    <button class="no-print" onclick="window.history.back()">Back</button>

    <script>
        function hidePrintHeaders() {
            window.print();
        }
    </script>
</body>

</html>