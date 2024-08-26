<?php
session_start();

// Check if the user is logged in as EDP
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'edp') {
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

$students = [];
$sql = "SELECT student_ID, student_fname, student_mname, student_lname FROM student";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Select a Student</h2>
        <form action="assign_subject.php" method="post">
            <label for="student">Choose a student:</label>
            <select name="student_id" id="student">
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['student_ID']) ?>">
                        <?= htmlspecialchars($student['student_fname'] . ' ' . $student['student_mname'] . ' ' . $student['student_lname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Assign Subjects</button>
        </form>
        <br>
        <button onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
