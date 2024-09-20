<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}

if (isset($_GET['instructor_id'])) {
    $instructor_id = $_GET['instructor_id'];

    // Fetch distinct subjects with either pending competencies or pending syllabus
    $sql = "
    SELECT DISTINCT s.subject_code, s.subject_name 
    FROM subject s
    LEFT JOIN competencies c ON s.subject_code = c.subject_code 
    LEFT JOIN syllabus sy ON s.subject_code = sy.subject_code 
    WHERE s.instructor_ID = ? 
    AND (c.status = 'PENDING' OR c.status IS NULL OR sy.status = 'PENDING' OR sy.status IS NULL)
    ORDER BY s.subject_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    if (empty($subjects)) {
        echo json_encode(['error' => 'No PENDING subjects found for the selected instructor.']);
    } else {
        echo json_encode($subjects);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'No instructor selected.']);
}

$conn->close();
?>
