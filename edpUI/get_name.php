<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['account_type']) && isset($_GET['account_id'])) {
    $account_type = trim($_GET['account_type']);
    $account_id = $_GET['account_id'];

    // Verify account type and set the SQL query accordingly
    if ($account_type === "instructor") {
        $sql = "SELECT instructor_fname, instructor_mname, instructor_lname FROM instructor WHERE instructor_ID = ?";
    } elseif ($account_type === "student") {
        $sql = "SELECT student_fname, student_mname, student_lname FROM student WHERE student_ID = ?";
    } else {
        echo "Invalid account type.";
        exit();
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Error in SQL preparation: " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $account_id);
    if (!$stmt->execute()) {
        echo "Error executing query: " . $stmt->error;
        $stmt->close();
        $conn->close();
        exit();
    }

    $stmt->bind_result($fname, $mname, $lname);
    $stmt->fetch();

    if ($fname) {
        echo htmlspecialchars($fname) . ' ' . ($mname ? htmlspecialchars($mname) . ' ' : '') . htmlspecialchars($lname); // Display name with optional middle name
    } else {
        echo "Account not found"; // Keep the "Account not found" message
    }

    $stmt->close();
} else {
    echo "Missing account type or account ID.";
}

$conn->close();
