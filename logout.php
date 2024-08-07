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

    // Determine the table and name column based on user type
    switch ($userType) {
        case 'student':
            $table = 'student';
            $nameColumn = 'student_fname';
            break;
        case 'dean':
            $table = 'dean';
            $nameColumn = 'dean_fname';
            break;
        case 'instructor':
            $table = 'instructor';
            $nameColumn = 'instructor_fname';
            break;
        default:
            echo "Invalid user type.";
            exit();
    }

    // Fetch user's first name based on the user ID
    $sql = "SELECT $nameColumn FROM $table WHERE ${userType}_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userFullName = $row[$nameColumn];
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
}

$conn->close();
exit();
?>
