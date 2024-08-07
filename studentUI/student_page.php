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

$studentFullName = '';

// Check if the student is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'student') {
    $studentId = $_SESSION['user_ID'];

    // Fetch student's full name based on the student ID
    $sql = "SELECT student_fname, student_mname, student_lname FROM student WHERE student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $studentFullName = $row['student_fname'] . ' ' . $row['student_mname'] . ' ' . $row['student_lname'];
        $_SESSION['user_fullname'] = $studentFullName; // Store the full name in session
    } else {
        $studentFullName = 'Unknown Student';
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
                    <ul>Name: <?php echo htmlspecialchars($studentFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($studentId); ?></ul>
                </div>
                <div>
                    <button onclick="location.href='../logout.php';" class="logout-button">Logout</button>
                    <p id="logoutMessage" class="logout-message"></p>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div><h4>Subjects:</h4></div>
                        <div class="btnSubjects">
                            <button>REED 101</button>
                        </div>
                        <div class="btnSubjects">
                            <button>GEC 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button>GEC ELECT 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button>FIL 102</button>
                        </div>
                        <div class="btnSubjects">
                            <button>NSTP-LTS 2</button>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="implementContainer">
                <header>
                    <h5>Instructional Delivery Implementation System (IDIS)</h5>
                    <p>Saint Micheal College of Caraga (SMCC)</p>
                    <div></div>
                    <div>
                        <nav class="navtab">
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">ILOs</button>
                            <button class="tablinks" onclick="openTab(event, 'Topics')">Topics</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Evaluation to Intended Learning Outcomes.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Intended Learning Outcomes (ILOs)</th>
                                        <th>Comments</th>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td><input type="text"><button>Submit</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                          
                        <div id="Topics" class="tabcontent">
                            <h6><br>Rate if the topics are being discussed clearly from 1 to 5.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Course Outlines</th>
                                        <th>Rating</th>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="1range" id="1"><label for="1">1</label>
                                            <input type="radio" name="1range" id="2"><label for="2">2</label>
                                            <input type="radio" name="1range" id="3"><label for="3">3</label>
                                            <input type="radio" name="1range" id="4"><label for="4">4</label>
                                            <input type="radio" name="1range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="2range" id="1"><label for="1">1</label>
                                            <input type="radio" name="2range" id="2"><label for="2">2</label>
                                            <input type="radio" name="2range" id="3"><label for="3">3</label>
                                            <input type="radio" name="2range" id="4"><label for="4">4</label>
                                            <input type="radio" name="2range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputRange">
                                            <input type="radio" name="3range" id="1"><label for="1">1</label>
                                            <input type="radio" name="3range" id="2"><label for="2">2</label>
                                            <input type="radio" name="3range" id="3"><label for="3">3</label>
                                            <input type="radio" name="3range" id="4"><label for="4">4</label>
                                            <input type="radio" name="3range" id="5"><label for="5">5</label>
                                        </td>
                                    </tr>
                                    <tr class="submitRate">
                                        <td></td>
                                        <td><button>Submit</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                          
                    </div>
                </main>               
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
</body>
</html>
