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

$subjects = [];
$sql = "SELECT subject_code, subject_name FROM subject";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subjectCode = $_POST['subject_code'];
    $teacherId = $_POST['teacher_id'] ?? null;
    $studentId = $_POST['student_id'] ?? null;

    if ($teacherId) {
        // Assign subject to the teacher
        $sql = "UPDATE subject SET instructor_ID = ? WHERE subject_code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $teacherId, $subjectCode);
        $stmt->execute();
        $stmt->close();
    } elseif ($studentId) {
        // Assign subject to the student (for simplicity, this example does not have a direct relation, but you might want to store this in another table)
        // Assuming there's a table student_subject or similar.
        // Example:
        // $sql = "INSERT INTO student_subject (student_ID, subject_code) VALUES (?, ?)";
        // $stmt = $conn->prepare($sql);
        // $stmt->bind_param("is", $studentId, $subjectCode);
        // $stmt->execute();
        // $stmt->close();
    }

    // Redirect or show a success message
    echo "Subject assigned successfully!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Subjects</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Assign Subjects</h2>
        <form action="assign_subject.php" method="post">
            <label for="subject">Choose a subject:</label>
            <select name="subject_code" id="subject">
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= htmlspecialchars($subject['subject_code']) ?>">
                        <?= htmlspecialchars($subject['subject_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Assign</button>
        </form>
        <br>
        <button onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
