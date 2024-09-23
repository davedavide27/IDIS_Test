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

// Initialize response
$response = [];

// Check if instructor_ID is provided through GET
if (isset($_GET['instructor_id'])) {
    $instructor_id = $_GET['instructor_id'];

    // Fetch assigned subjects based on instructor_ID
    $sql = "SELECT subject_code, subject_name FROM subject WHERE instructor_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $instructor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all subjects assigned to this instructor
        while ($row = $result->fetch_assoc()) {
            $response[] = [
                'subject_code' => $row['subject_code'],
                'subject_name' => $row['subject_name']
            ];
        }
        
        // If no subjects are found
        if (empty($response)) {
            $response['error'] = "No subjects assigned to this instructor.";
        }
        
        $stmt->close();
    } else {
        $response['error'] = "Error executing query.";
    }
} else {
    $response['error'] = "Instructor ID not provided.";
}

echo json_encode($response);
$conn->close();

