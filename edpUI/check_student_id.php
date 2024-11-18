<?php
// Database connection
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

// Get student_ID from query parameter
$student_ID = isset($_GET['student_ID']) ? $_GET['student_ID'] : '';

// Prepare the SQL query to check if the student ID exists
$sql = "SELECT student_ID FROM student WHERE student_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_ID);
$stmt->execute();
$stmt->store_result();

// Check if the student ID already exists
if ($stmt->num_rows > 0) {
    echo "exists"; // Student ID exists
} else {
    echo "not_exists"; // Student ID does not exist
}

$stmt->close();
$conn->close();

