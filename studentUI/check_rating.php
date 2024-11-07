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
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the JSON input
    $input = json_decode(file_get_contents("php://input"), true);
    
    // Check if required parameter is present
    if (isset($input['subject_code'])) {
        $subject_code = $input['subject_code'];

        // Fetch existing ratings for the student and subject code
        $sqlCheck = "SELECT topic, rating FROM course_outline_ratings WHERE student_id = ? AND subject_code = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        
        if ($stmtCheck === false) {
            echo json_encode(['success' => false, 'message' => 'SQL statement preparation failed: ' . $conn->error]);
            exit();
        }

        $stmtCheck->bind_param("is", $studentId, $subject_code);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();

        $existing_ratings = [];
        while ($row = $resultCheck->fetch_assoc()) {
            $existing_ratings[trim($row['topic'])] = (int)$row['rating']; // Store topic and rating, ensure no spaces
        }

        $stmtCheck->close();

        // Check if ratings were found
        $ratings_exist = !empty($existing_ratings);

        // Return existing ratings as a JSON response
        echo json_encode(['success' => true, 'ratings' => $existing_ratings, 'ratings_exist' => $ratings_exist]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

$conn->close();
