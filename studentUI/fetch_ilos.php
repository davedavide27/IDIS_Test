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

// Array to store ILOs by sections (PRELIM, MIDTERM, SEMIFINAL, FINAL)
$studentILOs = [
    'PRELIM' => [],
    'MIDTERM' => [],
    'SEMIFINAL' => [],
    'FINAL' => []
];

// Check if the subject code was provided via POST
if (isset($_POST['subject_code'])) {
    $subjectCode = $_POST['subject_code'];

    // First, check if there are any ILOs available at all for the subject
    $sqlCheckILOs = "SELECT COUNT(*) AS total_ilocount FROM context WHERE subject_code = ? AND ilo IS NOT NULL";
    $stmtCheckILOs = $conn->prepare($sqlCheckILOs);
    $stmtCheckILOs->bind_param("s", $subjectCode);
    $stmtCheckILOs->execute();
    $resultCheckILOs = $stmtCheckILOs->get_result();
    $row = $resultCheckILOs->fetch_assoc();
    $totalILOs = $row['total_ilocount'];
    $stmtCheckILOs->close();

    if ($totalILOs == 0) {
        // No ILOs available for this subject
        echo json_encode(['message' => 'No ILOs available for this subject']);
    } else {
        // Fetch ILOs that are approved
        $sqlILOs = "
            SELECT section, ilo 
            FROM context 
            WHERE subject_code = ? 
            AND ilo IS NOT NULL 
            AND status = 'APPROVED'
            ORDER BY FIELD(section, 'PRELIM', 'MIDTERM', 'SEMIFINAL', 'FINAL'), ilo";

        $stmtILOs = $conn->prepare($sqlILOs);
        $stmtILOs->bind_param("s", $subjectCode);
        $stmtILOs->execute();
        $resultILOs = $stmtILOs->get_result();

        // Group ILOs by section
        while ($row = $resultILOs->fetch_assoc()) {
            $section = strtoupper($row['section']);
            if (array_key_exists($section, $studentILOs)) {
                $studentILOs[$section][] = $row['ilo'];
            }
        }

        $stmtILOs->close();

        // Check if there are any approved ILOs
        if (empty(array_filter($studentILOs))) {
            // No approved ILOs
            echo json_encode(['message' => 'ILOs for this subject have not been approved']);
        } else {
            // Return the approved ILOs
            echo json_encode($studentILOs);
        }
    }
} else {
    echo json_encode(['error' => 'No subject code provided']);
}

$conn->close();
