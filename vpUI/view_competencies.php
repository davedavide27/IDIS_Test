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

$vpFullName = '';
$instructors = [];
$subjects = [];
$competencies = [];
$subject_code = '';
$subject_name = '';

// Check if the vp is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'vp') {
    $vpId = $_SESSION['user_ID'];

    // Fetch vp's full name based on the vp ID
    $sql = "SELECT vp_fname, vp_mname, vp_lname FROM vp WHERE vp_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vpId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $vpFullName = $row['vp_fname'] . ' ' . $row['vp_mname'] . ' ' . $row['vp_lname'];
        $_SESSION['user_fullname'] = $vpFullName; // Store the full name in session
    } else {
        $vpFullName = 'Unknown User';
    }

    $stmt->close();

    // Fetch all instructors
    $sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $instructors[] = $row;
        }
    }

    // If an instructor is selected, fetch their subjects
    if (isset($_GET['instructor_ID'])) {
        $selectedInstructorID = $_GET['instructor_ID'];

        $sql = "SELECT subject_code, subject_name FROM subject WHERE instructor_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $selectedInstructorID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $subjects[] = $row;
            }
        }

        $stmt->close();
    }

    // Fetch competencies based on the selected subject code
    if (isset($_GET['subject_code'])) {
        $subject_code = $_GET['subject_code'];

        $sql = "SELECT subject_name FROM subject WHERE subject_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $subject_name = $result->fetch_assoc()['subject_name'];
        }

        $stmt->close();

        $sql = "SELECT competency_description, remarks FROM competencies WHERE subject_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $competencies[] = $row;
            }
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
    <title>IDIS - View Competencies</title>
    <style>
        @media print {
            .no-print, .no-print * {
                display: none !important;
            }
        }
        .competency-table {
            width: 100%;
            border-collapse: collapse;
        }
        .competency-table th, .competency-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .competency-table th {
            background-color: white;
            text-align: left;
        }
        .selected-subject {
            background-color: #FF0000;
            border-color: #badbcc;
        }
    </style>
</head>
<body>
    <div class="containerOfAll">
        <?php if (isset($_GET['subject_code']) && !empty($subject_code)): ?>
            <div class="competencyContainer">
                <h2>Competencies for: <?php echo htmlspecialchars($subject_name); ?> (<?php echo htmlspecialchars($subject_code); ?>)</h2>
                <table class="competency-table">
                    <tr>
                        <th>Competency Description</th>
                        <th>Remarks</th>
                    </tr>
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
                </table>
                <button class="no-print" onclick="window.print()">Print this page</button>
                <button class="no-print" onclick="window.history.back()">Back</button>
        <?php endif; ?>
    </div>
</body>
</html>
