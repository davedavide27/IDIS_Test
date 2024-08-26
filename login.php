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

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    // Determine the table and ID column based on user type
    switch ($userType) {
        case 'student':
            $table = 'student';
            $idColumn = 'student_ID';
            $redirectUrl = 'studentUI/student_page.php';
            break;
        case 'dean':
            $table = 'dean';
            $idColumn = 'dean_ID';
            $redirectUrl = 'deanUI/index.php'; 
            break;
        case 'instructor':
            $table = 'instructor';
            $idColumn = 'instructor_ID';
            $redirectUrl = 'instructorUI/index.php'; 
            break;
        case 'vp':
            $table = 'vp';
            $idColumn = 'vp_ID';
            $redirectUrl = 'vpUI/index.php'; 
            break;
        case 'edp':
            $table = 'edp';
            $idColumn = 'edp_ID';
            $redirectUrl = 'edpUI/index.php'; 
            break;
        default:
            $error_message = "Invalid user type.";
    }

    if (empty($error_message)) {
        $sql = "SELECT * FROM $table WHERE $idColumn = ? AND password = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $id, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Store the user ID and type in the session
            $_SESSION['user_ID'] = $id;
            $_SESSION['user_type'] = $userType;
            header("Location: $redirectUrl");
            exit();
        } else {
            $error_message = "Invalid ID or password.";
            // Display error message and stay on the current template
            include($userType . "_login.php");
            exit();
        }
    }
    $stmt->close();
}

$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDIS Authentication</title>
    <link rel="stylesheet" href="style1.css">
</head>
<body>
    <header>
        <h1>Login As:</h1>
    </header>
    <main>
        <div class="card">
            <div class="title"><p>STUDENT</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="student_login.php"><button class="login">LOGIN</button></a>
        </div>
        <div class="card">
            <div class="title"><p>INSTRUCTOR</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="instructor_login.php"><button class="login">LOGIN</button></a>
        </div>
        <div class="card">
            <div class="title"><p>PROGRAM CHAIR</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="chair_login.php"><button class="login">LOGIN</button></a>
        </div>
        <div class="card">
            <div class="title"><p>DEAN</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="dean_login.php"><button class="login">LOGIN</button></a>
        </div>
        <div class="card">
            <div class="title"><p>VICE-PRESIDENT</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="vice_pres_login.php"><button class="login">LOGIN</button></a>
        </div>
        <div class="card">
            <div class="title"><p>EDP</p></div>
            <div class="content"><img src="" alt=""></div>
            <a href="edp_login.php"><button class="login">LOGIN</button></a>
        </div>
    </main>
</body>
</html>