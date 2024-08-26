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

$edpFullName = '';

// Check if the EDP (Admin) is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'edp') {
    $edpId = $_SESSION['user_ID'];

    // Fetch EDP's full name based on the EDP ID
    $sql = "SELECT edp_fname, edp_mname, edp_lname FROM edp WHERE edp_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $edpId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $edpFullName = $row['edp_fname'] . ' ' . $row['edp_mname'] . ' ' . $row['edp_lname'];
    } else {
        $edpFullName = 'Unknown EDP';
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
    <title>IDIS - EDP</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js"></script>
</head>
<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="../logo.jpg" alt="sample logo">
                </div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($edpFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($edpId); ?></ul>
                </div>
                <div>
                    <form action="../logout.php" method="post">
                        <button type="submit">Logout</button>
                    </form>
                </div>
            </nav>
        </div>
        <div class="implementContainer">
            <header>
                <h5>Instructional Delivery Implementation System (IDIS)</h5>
                <p>Saint Michael College of Caraga (SMCC)</p>
            </header>
            <main>
                <h4 style="text-align: center;">User Assigning</h4>
                <div class="selectIns">
                    <button onclick="window.location.href='select_teacher.php'">Select Teacher</button>
                    <button onclick="window.location.href='select_student.php'">Select Student</button>
                    <button onclick="window.location.href='assign_subject.php'">Assign Subjects</button>
                </div>
                <h4 style="text-align: center;">System Modification</h4>
                <div class="selectIns">
                    <button onclick="window.location.href='edit_syllabi.php'">Edit Syllabi Format</button>
                </div>
            </main>               
        </div>
    </div>
</body>
</html>