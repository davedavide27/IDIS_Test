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
$assignedSubjects = []; // Array to store assigned subjects

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

    // Fetch assigned subjects for the logged-in student from the student_subject table
    $sql = "SELECT subject.subject_name, subject.subject_code 
            FROM student_subject 
            JOIN subject ON student_subject.subject_code = subject.subject_code 
            WHERE student_subject.student_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $assignedSubjects[] = $row; // Store assigned subjects
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

        /* Highlighted subject button */
        .selected-subject {
            background-color: #FF0000;
            color: white;
        }

        /* No data message */
        .no-data-message {
            text-align: center;
            color: gray;
            font-size: 16px;
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
                        <div>
                            <h4>Assigned Subjects:</h4>
                        </div>

                        <!-- Loop through assigned subjects and display them -->
                        <?php if (!empty($assignedSubjects)): ?>
                            <?php foreach ($assignedSubjects as $subject): ?>
                                <div class="btnSubjects">
                                    <button onclick="selectSubject(this)"><?php echo htmlspecialchars($subject['subject_name']); ?> (<?php echo htmlspecialchars($subject['subject_code']); ?>)</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No subjects assigned yet.</p>
                        <?php endif; ?>
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
                            <button class="tablinks" onclick="openTab(event, 'ILOs')">ILOs</button>
                            <button class="tablinks" onclick="openTab(event, 'Topics')">Topics</button>
                        </nav>
                    </div>
                </header>
                <main>
                    <div class="filesContainer">
                        <div id="ILOs" class="tabcontent">
                            <h6><br>Evaluation of Intended Learning Outcomes (ILOs).</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <thead>
                                        <tr>
                                            <th>Intended Learning Outcomes (ILOs)</th>
                                            <th>Comments</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div id="Topics" class="tabcontent">
                            <h6><br>Rate if the topics are being discussed clearly from 1 to 5.</h6>
                            <div id="container">
                                <table class="remarksTable">
                                    <thead>
                                        <tr>
                                            <th>Course Outlines</th>
                                            <th>Rating</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- This tbody will be dynamically updated -->
                                    </tbody>
                                </table>
                            </div>
                </main>
            </div>
        </div>
    </div>

    <script>
        // Function to handle subject selection and highlighting the selected button
        function selectSubject(buttonElement) {
            // Remove the 'selected-subject' class from all buttons
            const subjectButtons = document.querySelectorAll('.btnSubjects button');
            subjectButtons.forEach(function(btn) {
                btn.classList.remove('selected-subject');
            });

            // Add the 'selected-subject' class to the clicked button
            buttonElement.classList.add('selected-subject');

            // Get the subject code from the button's text
            const subjectCode = buttonElement.textContent.match(/\(([^)]+)\)/)[1];

            // Clear previous ILOs and Topics data before fetching new data
            clearTable('#ILOs .remarksTable tbody');
            clearTable('#Topics .remarksTable tbody');

            // Fetch the ILOs and Topics for the selected subject using AJAX
            fetchILOs(subjectCode);
            fetchTopics(subjectCode); // Add this function to fetch topics
        }

        // Function to fetch ILOs for the selected subject via AJAX
        function fetchILOs(subjectCode) {
            const formData = new FormData();
            formData.append('subject_code', subjectCode);

            fetch('fetch_ilos.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    appendILOsToTable(data, subjectCode);
                })
                .catch(error => console.error('Error fetching ILOs:', error));
        }

        // Function to fetch Topics for the selected subject via AJAX
        function fetchTopics(subjectCode) {
            const formData = new FormData();
            formData.append('subject_code', subjectCode);

            fetch('fetch_topics.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    appendTopicsToTable(data, subjectCode);
                })
                .catch(error => console.error('Error fetching topics:', error));
        }

        // Function to clear the table content
        function clearTable(tableSelector) {
            const tableBody = document.querySelector(tableSelector);
            tableBody.innerHTML = ''; // Clear the table content
        }

// Function to append the ILOs to the existing table without overriding
function appendILOsToTable(data, subjectCode) {
    const tableBody = document.querySelector('#ILOs .remarksTable tbody');

    // Clear existing content
    tableBody.innerHTML = '';

    if (data.message) {
        // If there's a message (e.g., no approved ILOs), display it
        const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
        tableBody.innerHTML = noDataRow;
    } else {
        // Iterate over each section and append the ILOs
        let hasData = false;

        ['PRELIM', 'MIDTERM', 'SEMIFINAL', 'FINAL'].forEach(section => {
            if (data[section] && data[section].length > 0) {
                hasData = true;

                data[section].forEach(ilo => {
                    const row = `
                    <tr data-ilo="${ilo}_${section}">
                        <td>${ilo} (${section})</td>
                        <td>
                            <input type="text" placeholder="Enter comments..." class="ilo-comment">
                            <button onclick="submitComment('${subjectCode}', '${ilo}', this)">Submit</button>
                        </td>
                    </tr>`;
                    tableBody.innerHTML += row; // Append row without clearing the table
                });
            }
        });

        // If there's no ILO data, display a "No data" message
        if (!hasData) {
            const noDataRow = `<tr><td colspan="2" class="no-data-message">No ILOs available for the selected subject.</td></tr>`;
            tableBody.innerHTML = noDataRow;
        }
    }
}

// Function to append the Topics to the existing table without overriding
function appendTopicsToTable(data, subjectCode) {
    const tableBody = document.querySelector('#Topics .remarksTable tbody');

    // Clear existing content
    tableBody.innerHTML = '';

    if (data.message) {
        // If there's a message (e.g., no approved topics), display it
        const noDataRow = `<tr><td colspan="2" class="no-data-message">${data.message}</td></tr>`;
        tableBody.innerHTML = noDataRow;
    } else {
        // Iterate over each section and append the topics
        let hasData = false;

        ['PRELIM', 'MIDTERM', 'SEMIFINAL', 'FINAL'].forEach(section => {
            if (data[section] && data[section].length > 0) {
                hasData = true;

                data[section].forEach(topic => {
                    const row = `
                    <tr data-topic="${topic}_${section}">
                        <td>${topic} (${section})</td>
                        <td>
                            <input type="radio" name="${topic}_rating" value="1">1
                            <input type="radio" name="${topic}_rating" value="2">2
                            <input type="radio" name="${topic}_rating" value="3">3
                            <input type="radio" name="${topic}_rating" value="4">4
                            <input type="radio" name="${topic}_rating" value="5">5
                            <button onclick="submitRating('${subjectCode}', '${topic}', this)">Submit</button>
                        </td>
                    </tr>`;
                    tableBody.innerHTML += row; // Append row without clearing the table
                });
            }
        });

        // If there's no topic data, display a "No data" message
        if (!hasData) {
            const noDataRow = `<tr><td colspan="2" class="no-data-message">No topics available for the selected subject.</td></tr>`;
            tableBody.innerHTML = noDataRow;
        }
    }
}

        // Function to submit a comment for an ILO
        function submitComment(subjectCode, ilo, buttonElement) {
            const commentInput = buttonElement.previousElementSibling; // Get the comment input
            const comment = commentInput.value.trim(); // Get and trim the comment input

            if (comment === '') {
                alert("Please enter a comment.");
                return;
            }

            const formData = new FormData();
            formData.append('subject_code', subjectCode);
            formData.append('ilo', ilo);
            formData.append('comment', comment);

            fetch('submit_comment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Comment submitted successfully.');
                        commentInput.value = ''; // Clear the comment field
                    } else {
                        alert('Failed to submit comment.');
                    }
                })
                .catch(error => console.error('Error submitting comment:', error));
        }

        // Function to submit a rating for a topic
        function submitRating(subjectCode, topic, buttonElement) {
            // Get the selected rating value for the topic
            const selectedRating = document.querySelector(`input[name="${topic}_rating"]:checked`);

            if (!selectedRating) {
                alert("Please select a rating.");
                return;
            }

            const rating = selectedRating.value; // Get the selected rating value

            const formData = new FormData();
            formData.append('subject_code', subjectCode);
            formData.append('topic', topic);
            formData.append('rating', rating);

            fetch('submit_rating.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Rating submitted successfully.');
                        selectedRating.checked = false; // Clear the selected rating
                    } else {
                        alert('Failed to submit rating.');
                    }
                })
                .catch(error => console.error('Error submitting rating:', error));
        }
    </script>



</body>

</html>