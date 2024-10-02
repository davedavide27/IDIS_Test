<?php
session_start();

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

$userFullName = '';

if (isset($_SESSION['user_ID']) && isset($_SESSION['user_type'])) {
    $userId = $_SESSION['user_ID'];
    $userType = $_SESSION['user_type'];

    // Define tables and columns in associative arrays
    $tables = [
        'student' => 'student',
        'dean' => 'dean',
        'instructor' => 'instructor',
        'vp' => 'vp',
        'edp' => 'edp',
        'program_chair' => 'program_chair'
    ];

    $nameColumns = [
        'student' => 'student_fname',
        'dean' => 'dean_fname',
        'instructor' => 'instructor_fname',
        'vp' => 'vp_fname',
        'edp' => 'edp_fname',
        'program_chair' => 'chair_fname'
    ];

    // Map the correct ID columns for each user type
    $idColumns = [
        'student' => 'student_ID',
        'dean' => 'dean_ID',
        'instructor' => 'instructor_ID',
        'vp' => 'vp_ID',
        'edp' => 'edp_ID',
        'program_chair' => 'chair_ID'
    ];

    // Validate user type
    if (!array_key_exists($userType, $tables)) {
        echo "Invalid user type.";
        exit();
    }

    // Fetch user's first name based on the user ID
    $sql = "SELECT {$nameColumns[$userType]} FROM {$tables[$userType]} WHERE {$idColumns[$userType]} = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userFullName = $row[$nameColumns[$userType]];
    } else {
        $userFullName = 'Unknown User';
    }

    $stmt->close();
    
    // Destroy session and show logout message
    session_destroy();
    echo "<script>
            alert('$userFullName has been logged out');
            window.location.href = 'login.php';
          </script>";
} else {
    header("Location: login.php");
    exit();
}

$conn->close();

