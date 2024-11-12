<?php
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

if (isset($_POST['subject_code']) && isset($_POST['instructor_ID'])) {
    $subjectCode = $_POST['subject_code'];
    $instructorID = $_POST['instructor_ID'];

    // Fetch total number of competencies for the selected subject and instructor
    $sql = "SELECT COUNT(*) as subject_competencies 
            FROM competencies 
            WHERE subject_code = ? AND instructor_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $subjectCode, $instructorID);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $subjectCompetencies = $row['subject_competencies'];

    // Fetch the total number of competencies for the selected instructor across all their subjects
    $sqlTotal = "SELECT COUNT(*) as total_competencies 
                 FROM competencies 
                 WHERE instructor_id = ?";
    $stmtTotal = $conn->prepare($sqlTotal);
    $stmtTotal->bind_param("i", $instructorID);
    $stmtTotal->execute();
    $resultTotal = $stmtTotal->get_result();
    $rowTotal = $resultTotal->fetch_assoc();
    $totalCompetencies = $rowTotal['total_competencies'];

    // Return JSON response with the selected subject's competencies and total competencies
    echo json_encode([
        'subject_competencies' => $subjectCompetencies,
        'total_competencies' => $totalCompetencies
    ]);

    $stmt->close();
    $stmtTotal->close();
}

$conn->close();
