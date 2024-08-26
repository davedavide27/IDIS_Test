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

$teachers = [];
$sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $teachers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Teacher</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Select a Teacher</h2>
        <form action="assign_subject.php" method="post">
            <label for="teacher">Choose a teacher:</label>
            <select name="teacher_id" id="teacher">
                <?php foreach ($teachers as $teacher): ?>
                    <option value="<?= htmlspecialchars($teacher['instructor_ID']) ?>">
                        <?= htmlspecialchars($teacher['instructor_fname'] . ' ' . $teacher['instructor_mname'] . ' ' . $teacher['instructor_lname']) ?>
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
