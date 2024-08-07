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

$vpFullName = '';

// Check if the vp is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'vp') {
    $vpId = $_SESSION['user_ID'];

    // Fetch vp's full name based on the student ID
    $sql = "SELECT vp_fname, vp_mname, vp_lname FROM vp WHERE vp_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $vpId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $vpFullName = $row['vp_fname'] . ' ' . $row['vp_mname'] . ' ' . $row['vp_lname'];
        $_SESSION['user_fullname'] = $vpFullName; // Store the full name in session
    } else {
        $vpFullName = 'Unknown User';
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
    <title>IDIS</title>
    <link rel="stylesheet" href="style.css">
    <script src="main.js"></script>
    <style>
        .logout-message {
            display: none;
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="logo.png" alt="sample logo">
                </div>
                <div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($vpFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($vpId); ?></ul>
                </div>
                
                </div>
                <h4 style="text-align: center;">00 out of 00</h4>
                <br>
                <div class="selectIns">
                    <select name="" id="showSelect" placeholder="da">
                        <option value="">Program Chair</option>
                        <option value="">Subject Coordinator</option>
                    </select>
                </div>
                <br><br>
                <h4 style="text-align: center;">00 out of 00</h4>
                <div class="selectIns">
                    <select name="" id="showSelect" placeholder="da">
                        <option value="">Instructor1</option>
                        <option value="">Instructor2</option>
                        <option value="">Instructor3</option>
                        <option value="">Instructor4</option>
                    </select>
                </div>
                <br><br>
                <h4 style="text-align: center;">00 out of 00</h4>
                <div class="subsContainer">
                    <div class="subjects">
                        <div><h4>Subjects:</h4></div>
                        <div class="btnSubjects">
                            <button >ADGEC 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button >FIL 102</button>
                        </div>
                        <div class="btnSubjects">
                            <button >GEC 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button >GEC 2</button>
                        </div>
                        <div class="btnSubjects">
                            <button >GEC ELECT 1</button>
                        </div>
                    </div>
                </div>
                <div>
                    <button onclick="location.href='../logout.php';" class="logout-button">Logout</button>
                    <p id="logoutMessage" class="logout-message"></p>
                </div>
            </nav>
            <div class="implementContainer">
                <header><h5>Instructional Delivery Implementation System (IDIS)</h5><p>Saint Micheal College of Caraga (SMCC)</p>
                    <div></div>
                    <div>
                        <nav class="navtab">
                                <button class="tablinks" onclick="openTab(event, 'ILOs')">Plans</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>View for sinaturies</h6>
                            <div id="container">
                                <div class="planCard">
                                    <a href=""><p>Syllabus</p></a>
                                </div>
                                <div class="planCard">
                                    <a href=""><p>Competencies</p></a>
                                </div>
                            </div>
                        </div>
                        <script>
        function showLogoutMessage(message) {
            var logoutMessage = document.getElementById('logoutMessage');
            logoutMessage.textContent = message;
            logoutMessage.style.display = 'block';
            setTimeout(function() {
                logoutMessage.style.display = 'none';
            }, 3000);
        }
    </script>
                    </div>
                </main>               
            </div>
        </div>
    </div>
</body>
</html>