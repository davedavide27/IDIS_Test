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

// Get POST data
$subjectCode = $_POST['subject_code'] ?? '';
$topic = $_POST['topic'] ?? '';
$rating = $_POST['rating'] ?? '';

if (empty($subjectCode) || empty($topic) || empty($rating)) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit();
}

// Check if a rating for this topic already exists
$sql = "SELECT * FROM course_outline_ratings WHERE student_id = ? AND subject_code = ? AND topic = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iss", $studentId, $subjectCode, $topic);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Update existing rating
    $sql = "UPDATE course_outline_ratings SET rating = ?, submitted_at = NOW() WHERE student_id = ? AND subject_code = ? AND topic = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $rating, $studentId, $subjectCode, $topic);
} else {
    // Insert new rating
    $sql = "INSERT INTO course_outline_ratings (student_id, subject_code, section, topic, rating) VALUES (?, ?, '', ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issi", $studentId, $subjectCode, $topic, $rating);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to submit rating']);
}

$stmt->close();
$conn->close();
?>
