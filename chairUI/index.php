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

$chairFullName = '';
$chairId = '';

// Check if the program chair is logged in
if (isset($_SESSION['user_ID']) && $_SESSION['user_type'] == 'program_chair') {
    $chairId = $_SESSION['user_ID'];

    // Fetch program chair's full name based on the chair_ID
    $sql = "SELECT chair_fname, chair_mname, chair_lname FROM program_chair WHERE chair_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $chairId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $chairFullName = $row['chair_fname'] . ' ' . $row['chair_mname'] . ' ' . $row['chair_lname'];
        $_SESSION['user_fullname'] = $chairFullName; // Store the full name in session
    } else {
        $chairFullName = 'Unknown Program Chair';
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
    <script src="chair.js"></script>
</head>

<body>
    <div class="containerOfAll">
        <div class="subjectsContainer">
            <nav class="navSubject">
                <div class="logo">
                    <img src="../person.jpg" alt="sample logo">
                </div>
                <div>
                <form action="../logout.php" method="post">
                    <button class="logout_btn" type="submit">Logout</button>
                </form>
                    <ul>Name: <?php echo htmlspecialchars($chairFullName); ?></ul>
                    <ul>ID: <?php echo htmlspecialchars($chairId); ?></ul>
                </div>
                <div class="subsContainer">
                    <div class="subjects">
                        <div>
                            <h4>Pending Subjects:</h4>
                        </div>
                        <div class="btnSubjects">
                            <button>ADGEC 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button>FIL 102</button>
                        </div>
                        <div class="btnSubjects">
                            <button>GEC 1</button>
                        </div>
                        <div class="btnSubjects">
                            <button>GEC 2</button>
                        </div>
                        <div class="btnSubjects">
                            <button>GEC ELECT 1</button>
                        </div>
                    </div>
                </div>
            </nav>
            <div class="implementContainer">
                <header>
                    <h5>Instructional Delivery Implementation System (IDIS)</h5>
                    <p>Saint Michael College of Caraga (SMCC)</p>
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
                            <h6><br>Implement</h6>
                            <div id="containerPlan">
                                <div class="planCard">
                                    <a href="#">
                                        <p>Syllabus</p>
                                    </a>
                                </div>
                                <div class="planCard">
                                    <a href="#">
                                        <p>Competencies</p>
                                    </a>
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
                                            <input type="checkbox">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputCheck">
                                            <input type="checkbox">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>... </td>
                                        <td class="inputCheck">
                                            <input type="checkbox">
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
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</body>

</html>