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

// Array to store topics by sections (PRELIM, MIDTERM, SEMIFINAL, FINAL)
$topics = [
    'PRELIM' => [],
    'MIDTERM' => [],
    'SEMIFINAL' => [],
    'FINAL' => []
];

// Check if the subject code was provided via POST
if (isset($_POST['subject_code'])) {
    $subjectCode = $_POST['subject_code'];

    // First, check if there are any topics available at all for the subject
    $sqlCheckTopics = "SELECT COUNT(*) AS total_topiccount FROM context WHERE subject_code = ? AND topics IS NOT NULL";
    $stmtCheckTopics = $conn->prepare($sqlCheckTopics);
    $stmtCheckTopics->bind_param("s", $subjectCode);
    $stmtCheckTopics->execute();
    $resultCheckTopics = $stmtCheckTopics->get_result();
    $row = $resultCheckTopics->fetch_assoc();
    $totalTopics = $row['total_topiccount'];
    $stmtCheckTopics->close();

    if ($totalTopics == 0) {
        // No topics available for this subject
        echo json_encode(['message' => 'No topics available for this subject']);
    } else {
        // Fetch topics that are approved
        $sql = "SELECT section, topics 
                FROM context 
                WHERE subject_code = ? 
                AND topics IS NOT NULL 
                AND status = 'APPROVED' 
                ORDER BY FIELD(section, 'PRELIM', 'MIDTERM', 'SEMIFINAL', 'FINAL')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $subjectCode);
        $stmt->execute();
        $result = $stmt->get_result();

        // Group topics by section
        while ($row = $result->fetch_assoc()) {
            $section = strtoupper($row['section']);
            if (array_key_exists($section, $topics)) {
                $topics[$section][] = $row['topics'];
            }
        }

        $stmt->close();

        // Check if there are any approved topics
        if (empty(array_filter($topics))) {
            // No approved topics
            echo json_encode(['message' => 'Topics for this subject have not been approved']);
        } else {
            // Return the approved topics
            echo json_encode($topics);
        }
    }
} else {
    echo json_encode(['error' => 'No subject code provided']);
}

$conn->close();
