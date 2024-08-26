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
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Syllabus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>View Syllabus</h2>
        <form action="syllabus_detail.php" method="post">
            <label for="subject">Choose a subject:</label>
            <select name="subject_code" id="subject">
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject['subject_code']) ?>">
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View Syllabus</button>
        </form>
        <br>
        <button onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
