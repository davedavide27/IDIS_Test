<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get subject code
$subject_code = $_POST['subject_code'] ?? '';

if (empty($subject_code)) {
    echo json_encode(['error' => 'No subject code provided']);
    exit;
}

// Fetch the ILOs and comments from the database
$sql = "SELECT ilo, comment, student_id, timestamp 
        FROM ilo_comments
        WHERE subject_code = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'SQL prepare failed: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();

$commentsData = [];
while ($row = $result->fetch_assoc()) {
    $ilo = $row['ilo'];
    $comment = [
        'comment' => $row['comment'],
        'student_id' => $row['student_id'],
        'timestamp' => $row['timestamp']
    ];

    if (!isset($commentsData[$ilo])) {
        $commentsData[$ilo] = [
            'ilo' => $ilo,
            'comments' => []
        ];
    }

    $commentsData[$ilo]['comments'][] = $comment;
}

// Output the JSON data
echo json_encode($commentsData);
$stmt->close();
$conn->close();
?>