<?php
session_start();

// Check if the user is authenticated and is a student
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$studentId = $_SESSION['user_ID'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed']);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Check if required parameters are present
    if (isset($input['subject_code']) && isset($input['ilo'])) {
        $subject_code = $input['subject_code'];
        $ilo = $input['ilo'];

        // Check if the student has already commented on this ILO
        $sqlCheck = "SELECT * FROM ilo_comments WHERE student_id = ? AND subject_code = ? AND ilo = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("iss", $studentId, $subject_code, $ilo);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        // Determine if a comment exists
        if ($resultCheck->num_rows > 0) {
            echo json_encode(['exists' => true, 'success' => true]);
        } else {
            echo json_encode(['exists' => false, 'success' => true]);
        }

        $stmtCheck->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
