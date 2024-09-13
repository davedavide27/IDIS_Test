<?php
session_start();

// Check if the user is logged in as EDP
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'edp') {
    header("Location: ../login.php");
    exit();
}

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

// Fetch all instructors
$instructors = [];
$sql = "SELECT instructor_ID, instructor_fname, instructor_mname, instructor_lname FROM instructor";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}

// If the form is submitted for creating a new subject
if (isset($_POST['create_subject'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
    $instructor_id = !empty($_POST['instructor_id']) ? $_POST['instructor_id'] : null;

    // Insert new subject
    $stmt = $conn->prepare("INSERT INTO subject (subject_code, subject_name, instructor_ID) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $subject_code, $subject_name, $instructor_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "New subject created successfully!";
    } else {
        $_SESSION['error_message'] = "Error creating new subject.";
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="select.css">
    <title>Create New Subject</title>
    <style>
        /* Style for success and error messages */
        .success-message {
            color: green;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .error-message {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        /* General styling for form */
        .containerOfAll {
            width: 50%;
            margin: 0 auto;
            font-family: Arial, sans-serif;
        }

        h3 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            display: block;
            width: 100%;
            text-align: center;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsive styling */
        @media screen and (max-width: 600px) {
            .containerOfAll {
                width: 100%;
                padding: 0 15px;
            }
        }
        .back-button {
            background-color: #f44336;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            top: 10px;
            left: 20px;
            width: 10%;
        }

        .back-button:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>

<div class="containerOfAll">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success-message">
            <?php
            echo $_SESSION['success_message'];
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php elseif (isset($_SESSION['error_message'])): ?>
        <div class="error-message">
            <?php
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <h3>Create a New Subject</h3>
    <button class="back-button" onclick="window.location.href='index.php';">Back</button>
    <!-- Form for Creating New Subject -->
    <form method="post" action="" onsubmit="return validateNewSubjectForm()">
        <input type="hidden" name="create_subject" value="1">

        <label for="subject_code">Subject Code:</label>
        <input type="text" name="subject_code" id="subject_code" required>

        <label for="subject_name">Subject Name:</label>
        <input type="text" name="subject_name" id="subject_name" required>

        <label for="instructor_id">Assign Instructor (Optional):</label>
        <select id="instructorSelectCreate" name="instructor_id">
            <option value="">Select an Instructor</option>
            <?php foreach ($instructors as $instructor): ?>
                <option value="<?php echo htmlspecialchars($instructor['instructor_ID']); ?>">
                    <?php echo htmlspecialchars($instructor['instructor_fname'] . ' ' . $instructor['instructor_mname'] . ' ' . $instructor['instructor_lname']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Create Subject</button>

    </form>
    
</div>

<script>
    function validateNewSubjectForm() {
        const subjectCode = document.getElementById("subject_code").value;
        const subjectName = document.getElementById("subject_name").value;

        if (subjectCode === "" || subjectName === "") {
            alert("Please fill all fields for creating a new subject.");
            return false;
        }

        return true;
    }
</script>

</body>
</html>
