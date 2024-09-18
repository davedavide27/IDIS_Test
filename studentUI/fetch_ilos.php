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

// Check if the subject code was provided via POST
if (isset($_POST['subject_code'])) {
    $subjectCode = $_POST['subject_code'];

    // Array to store ILOs by sections (PRELIM, MIDTERM, SEMIFINAL, FINAL)
    $studentILOs = [
        'PRELIM' => [],
        'MIDTERM' => [],
        'SEMIFINAL' => [],
        'FINAL' => []
    ];

    // Fetch ILOs from the context table for the selected subject
    $sqlILOs = "
        SELECT section, ilo 
        FROM context 
        WHERE subject_code = ?
        AND ilo IS NOT NULL
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
    $conn->close();

    // Return the ILOs data as JSON
    echo json_encode($studentILOs);
} else {
    echo json_encode(['error' => 'No subject code provided']);
}
