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

// Initialize an array to store competencies
$competencies = [];

// Check if subject code is passed via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_code'])) {
    $subjectCode = $_POST['subject_code'];

    // Fetch competencies for the selected subject
    $sql = "SELECT competency_description, remarks FROM competencies WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subjectCode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Loop through and collect competencies
        while ($row = $result->fetch_assoc()) {
            $competencies[] = $row;
        }
    }

    // Close the statement
    $stmt->close();
}

// Close the connection
$conn->close();

// Return the competencies as a JSON response
echo json_encode($competencies);
exit(); // End script execution after returning JSON
?>
