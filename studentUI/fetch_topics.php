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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_code'])) {
    $subject_code = $_POST['subject_code'];

    // Fetch topics from the context table
    $sql = "SELECT section, topics 
            FROM context 
            WHERE subject_code = ? AND topics IS NOT NULL 
            ORDER BY FIELD(section, 'PRELIM', 'MIDTERM', 'SEMIFINAL', 'FINAL')";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();

    $topics = [
        'PRELIM' => [],
        'MIDTERM' => [],
        'SEMIFINAL' => [],
        'FINAL' => []
    ];

    while ($row = $result->fetch_assoc()) {
        $section = strtoupper($row['section']);
        if (array_key_exists($section, $topics)) {
            $topics[$section][] = $row['topics'];
        }
    }

    $stmt->close();

    // Return topics as JSON
    echo json_encode($topics);
}

$conn->close();
?>
