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

// Fetch reports related to subjects and competencies
$reports = [];
$sql = "SELECT * FROM reports WHERE department = ?";
$stmt = $conn->prepare($sql);
$department = "COLLEGE OF ARTS AND SCIENCES"; // Example department
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
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
    <title>View Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>View Reports</h2>
        <table>
            <tr>
                <th>Subject</th>
                <th>Competency</th>
                <th>Remarks</th>
                <th>Percentage Implemented</th>
            </tr>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['subject']) ?></td>
                    <td><?= htmlspecialchars($report['competency']) ?></td>
                    <td><?= htmlspecialchars($report['remarks']) ?></td>
                    <td><?= htmlspecialchars($report['percentage_implemented']) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
        <button onclick="window.history.back();">Back</button>
    </div>
</body>
</html>
