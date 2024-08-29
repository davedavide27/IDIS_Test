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

$competencies = [];

if (isset($_POST['subject_code'])) {
    $selectedSubjectCode = $_POST['subject_code'];

    $sql = "SELECT competency_description, remarks FROM competencies WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $selectedSubjectCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $competencies[] = $row;
        }
    }

    $stmt->close();
}

$conn->close();

echo json_encode($competencies);
?>
