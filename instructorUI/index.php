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

$instructorFullName = '';

// Check if the instructor is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'instructor') {
    $instructorId = $_SESSION['user_ID'];

    // Fetch instructor's full name based on the instructor ID
    $sql = "SELECT instructor_fname, instructor_mname, instructor_lname FROM instructor WHERE instructor_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $instructorFullName = $row['instructor_fname'] . ' ' . $row['instructor_mname'] . ' ' . $row['instructor_lname'];
    } else {
        $instructorFullName = 'Unknown Instructor';
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
</head>
<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="logo.png" alt="sample logo">
                </div>
                <div>
                    <ul>Name: <?php echo htmlspecialchars($instructorFullName); ?></ul>
                    <ul>ID:<?php echo htmlspecialchars($instructorId); ?></ul>
                </div>
                <div>
                <form action="../logout.php" method="post">
                        <button type="submit">Logout</button>
                    </form>
                </div>
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
            </nav>
            <div class="implementContainer">
                <header><h5>Instructional Delivery Implementation System (IDIS)</h5><p>Saint Micheal College of Caraga (SMCC)</p>
                    <div></div>
                    <div>
                        <nav class="navtab">
                                <button class="tablinks" onclick="openTab(event, 'ILOs')">Plans</button>
                                
                                <button class="tablinks" onclick="openTab(event, 'Topics')">Competencies</button>
                                
                                <button class="tablinks" onclick="openTab(event, 'Comments')">Comments</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Implement</h6>
                            <div id="containerPlan">
                                <div class="planCard">
                                    <a href=""><p>Syllabus</p></a>
                                </div>
                                <div class="planCard">
                                    <a href=""><p>Competencies</p></a>
                                </div>
                            </div>
                        </div>
                          
                        <div id="Topics" class="tabcontent">
                            <h6><br>Remark check if the competency is implemented.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <tr>
                                        <th>Competencies</th>
                                        <th>Remarks</th>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputCheck">
                                            <input type="checkbox" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputCheck">
                                            <input type="checkbox" >
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputCheck">
                                            <input type="checkbox" >
                                        </td>
                                    </tr>
                                    <tr class="submitRate">
                                        <td></td>
                                        <td><button>Submit</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                          
                        <div id="Comments" class="tabcontent">
                            <h6><br>Pop up Comments / Suggestions</h6>
                            <div id="containerComment">
                                <div class="commentCard">
                                    <div>
                                        <h6>ADGEC 1</h6>
                                    </div>
                                    <div>
                                        <p class="content">/*comments*/</p>
                                    </div>
                                    <div>
                                        <p class="footerTopic">Topic No. 3</p>
                                    </div>
                                </div>
                                <div class="commentCard">
                                    <div>
                                        <h6>ADGEC 1</h6>
                                    </div>
                                    <div>
                                        <p class="content">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ducimus dolores id debitis cum accusamus inventore praesentium sit voluptatum, distinctio dignissimos odio laboriosam, omnis assumenda eos iusto officia aut itaque. Molestias!</p>
                                    </div>
                                    <div>
                                        <p class="footerTopic">Topic No. 2</p>
                                    </div>
                                </div>
                                <div class="commentCard">
                                    <div>
                                        <h6>ADGEC 1</h6>
                                    </div>
                                    <div>
                                        <p class="content">Lorem ipsum dolor sit amet consectetur, adipisicing elit. Ducimus dolores id debitis cum accusamus inventore praesentium sit voluptatum, distinctio dignissimos odio laboriosam, omnis assumenda eos iusto officia aut itaque. Molestias!</p>
                                    </div>
                                    <div>
                                        <p class="footerTopic">Topic No. 2</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                          
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>
</html>
