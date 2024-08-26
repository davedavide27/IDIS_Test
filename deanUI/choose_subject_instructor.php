<?php
session_start();

// Check if the user is logged in as Dean
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'dean') {
    header("Location: ../login.php");
    exit();
}

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

$subjects = [];
$instructors = [];

// Fetch subjects
$sql = "SELECT subject_code, subject_name FROM subject WHERE department = ?";
$stmt = $conn->prepare($sql);
$department = "COLLEGE OF ARTS AND SCIENCES"; // Example department
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

$stmt->close();

// Fetch instructors
$sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Subject and Instructor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Choose Subject and Instructor</h2>
        <form action="assign_instructor.php" method="post">
            <label for="subject">Choose a subject:</label>
            <select name="subject_code" id="subject">
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject['subject_code']) ?>">
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <label for="instructor">Choose an instructor:</label>
            <select name="instructor_id" id="instructor">
                <?php foreach ($instructors as $instructor): ?>
                    <option value="<?= htmlspecialchars($instructor['instructor_ID']) ?>">
                        <?= htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Assign Instructor</button>
        </form>
        <br>
        <button onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
