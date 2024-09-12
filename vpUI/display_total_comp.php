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

if (isset($_POST['subject_code'])) {
    $subjectCode = $_POST['subject_code'];

    // Fetch total number of competencies for the selected subject
    $sql = "SELECT COUNT(*) as subject_competencies FROM competencies WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subjectCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $subjectCompetencies = $row['subject_competencies'];

    // Fetch the total number of competencies across all subjects
    $sqlTotal = "SELECT COUNT(*) as total_competencies FROM competencies";
    $resultTotal = $conn->query($sqlTotal);
    $rowTotal = $resultTotal->fetch_assoc();
    $totalCompetencies = $rowTotal['total_competencies'];

    // Return JSON response with the selected subject's competencies and total competencies
    echo json_encode([
        'subject_competencies' => $subjectCompetencies,
        'total_competencies' => $totalCompetencies
    ]);

    $stmt->close();
}

$conn->close();
?>
