<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the student is logged in
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'student') {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$studentId = $_SESSION['user_ID'];

// Get POST data (ratings should be a JSON string)
$subjectCode = $_POST['subject_code'] ?? '';
$ratings = json_decode($_POST['ratings'], true);

if (empty($subjectCode) || empty($ratings) || !is_array($ratings)) {
    echo json_encode(['success' => false, 'message' => 'Missing or invalid data']);
    exit();
}

$successCount = 0;
$failCount = 0;

// Iterate over all the ratings submitted
foreach ($ratings as $topicWithSection => $rating) {
    // Parse the topic and section from the data-topic format (e.g., "topic_section")
    list($topic, $section) = explode('_', $topicWithSection);

    if (empty($topic) || empty($rating) || empty($section)) {
        $failCount++;
        continue;
    }

    // Check if a rating for this topic and section already exists for the student
    $sql = "SELECT * FROM course_outline_ratings WHERE student_id = ? AND subject_code = ? AND topic = ? AND section = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $studentId, $subjectCode, $topic, $section);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing rating
        $sql = "UPDATE course_outline_ratings SET rating = ?, submitted_at = NOW() WHERE student_id = ? AND subject_code = ? AND topic = ? AND section = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $rating, $studentId, $subjectCode, $topic, $section);
    } else {
        // Insert new rating with section
        $sql = "INSERT INTO course_outline_ratings (student_id, subject_code, section, topic, rating) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssi", $studentId, $subjectCode, $section, $topic, $rating);
    }

    // Execute query
    if ($stmt->execute()) {
        $successCount++;
    } else {
        $failCount++;
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Send the response with success and fail counts
if ($successCount > 0) {
    echo json_encode(['success' => true, 'message' => "Ratings submitted successfully", 'failures' => $failCount]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit ratings', 'failures' => $failCount]);
}