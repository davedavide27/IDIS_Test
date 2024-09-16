<?php
// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the request is POST and the subject code is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['syllabus_subject_code'])) {
    // Get the subject code from the POST data
    $subjectCode = $_POST['syllabus_subject_code'];

    // SQL query to fetch the syllabus based on the subject code
    $sql = "SELECT * FROM syllabus WHERE subject_code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subjectCode);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if any syllabus data exists
    if ($result->num_rows > 0) {
        // Fetch the syllabus data and encode it as JSON
        $syllabus = $result->fetch_assoc();
        echo json_encode($syllabus);
    } else {
        // No syllabus found, return an empty response
        echo json_encode(null);
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();
?>
