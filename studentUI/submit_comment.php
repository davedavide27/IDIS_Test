<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subject_code']) && isset($_POST['ilo']) && isset($_POST['comment'])) {
    $subject_code = $_POST['subject_code'];
    $ilo = $_POST['ilo'];
    $comment = $_POST['comment'];

    // Check if the student has already commented on this ILO
    $sqlCheck = "SELECT * FROM ilo_comments WHERE student_id = ? AND subject_code = ? AND ilo = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("iss", $studentId, $subject_code, $ilo);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        // If a comment exists, update it
        $sqlUpdate = "UPDATE ilo_comments SET comment = ? WHERE student_id = ? AND subject_code = ? AND ilo = ?";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $stmtUpdate->bind_param("siss", $comment, $studentId, $subject_code, $ilo);

        if ($stmtUpdate->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comment updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
        }

        $stmtUpdate->close();
    } else {
        // If no comment exists, insert a new one
        $sqlInsert = "INSERT INTO ilo_comments (student_id, subject_code, ilo, comment) VALUES (?, ?, ?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("isss", $studentId, $subject_code, $ilo, $comment);

        if ($stmtInsert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Comment submitted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to submit comment']);
        }

        $stmtInsert->close();
    }

    $stmtCheck->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>
