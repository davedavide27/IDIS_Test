<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$subject_code = $_GET['subject_code'] ?? '';

if (!$subject_code) {
    echo json_encode(['error' => 'Subject code is missing']);
    exit;
}

// Query the context data for the given subject code
$sql = "SELECT * FROM context WHERE subject_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);

$stmt->close();
$conn->close();
?>
