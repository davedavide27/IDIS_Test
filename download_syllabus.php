<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
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

// Initialize variables
$subject_code = "";
$subject_name = "";
$course_units = "";
$course_description = "";
$prerequisites_corequisites = "";
$contact_hours = "";
$performance_tasks = "";
$cilos = [];
$pilo_gilo = [];
$context = [];

// Check if subject_code and subject_name are provided through GET
if (isset($_GET['subject_code']) && isset($_GET['subject_name'])) {
    $subject_code = $_GET['subject_code'];
    $subject_name = $_GET['subject_name'];

    // Fetch syllabus data
    $sqlSyllabus = "SELECT * FROM syllabus WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlSyllabus)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $course_units = htmlspecialchars($row['course_units']);
            $course_description = htmlspecialchars($row['course_description']);
            $prerequisites_corequisites = htmlspecialchars($row['prerequisites_corequisites']);
            $contact_hours = htmlspecialchars($row['contact_hours']);
            $performance_tasks = htmlspecialchars($row['performance_tasks']);
        }
        $stmt->close();
    }

    // Fetch PILO-GILO mappings
    $sqlPiloGilo = "SELECT * FROM pilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlPiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $pilo_gilo[] = [
                'pilo' => htmlspecialchars($row['pilo']),
                'gilo' => htmlspecialchars($row['gilo'])
            ];
        }
        $stmt->close();
    }

    // Fetch CILO-GILO mappings
    $sqlCiloGilo = "SELECT * FROM cilo_gilo_map WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlCiloGilo)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $cilos[] = [
                'description' => htmlspecialchars($row['cilo_description']),
                'gilo1' => htmlspecialchars($row['gilo1']),
                'gilo2' => htmlspecialchars($row['gilo2'])
            ];
        }
        $stmt->close();
    }

    // Fetch context data
    $sqlContext = "SELECT * FROM context WHERE subject_code = ?";
    if ($stmt = $conn->prepare($sqlContext)) {
        $stmt->bind_param("s", $subject_code);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $context[] = [
                'section' => htmlspecialchars($row['section']),
                'hours' => htmlspecialchars($row['hours']),
                'ilo' => htmlspecialchars($row['ilo']),
                'topics' => htmlspecialchars($row['topics']),
                'institutional_values' => htmlspecialchars($row['institutional_values']),
                'teaching_activities' => htmlspecialchars($row['teaching_activities']),
                'resources' => htmlspecialchars($row['resources']),
                'assessment' => htmlspecialchars($row['assessment_tasks']),
                'course_map' => htmlspecialchars($row['course_map'])
            ];
        }
        $stmt->close();
    }
} else {
    $_SESSION['error_message'] = "Subject code or name not provided.";
    header("Location: syllabus.php"); // Redirect to syllabus page if no data
    exit();
}

// Set headers for Word document download
header("Content-type: application/vnd.ms-word");
header("Content-Disposition: attachment;Filename=" . htmlspecialchars($subject_name) . "_Syllabus.doc");

// Start generating the document content
echo "<html>";
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
echo "<body style='font-family: Arial, sans-serif;'>";

// Header section with logo and school info
echo "<div style='display: flex; justify-content: space-between; align-items: center;'>";
echo "<div><img src='../smcclogo.jfif' alt='SMCC Logo' style='height: 80px;'></div>";
echo "<div style='text-align: center; font-size: 21px;'>
        <div style='font-size: x-large; color: rgba(28, 6, 80, 0.877); font-family: Calambria;'>Saint Michael College of Caraga</div>
        <div style='font-size: medium; font-family: Bookman Old Style;'>Brgy. 4, Nasipit, Agusan del Norte, Philippines</div>
        <div style='font-family: Calambria;'>Tel. Nos. +63 085 343-3251 / +63 085 283-3113 Fax No. +63 085 808-0892</div>
        <div style='font-family: Bookman Old Style;'><a href='https://www.smccnasipit.edu.ph/'>www.smccnasipit.edu.ph</a></div>
      </div>";
echo "<div><img src='ISO&PAB.png' alt='Accreditation Logos' style='height: 80px;'></div>";
echo "</div>";

// Course Information Section
echo "<h2>Syllabus Information</h2>";
echo "<h3>Course Information</h3>";
echo "<ul>
        <li><b>Course Code:</b> " . htmlspecialchars($subject_code) . "</li>
        <li><b>Course Name:</b> " . htmlspecialchars($subject_name) . "</li>
        <li><b>Course Units:</b> " . (!empty($course_units) ? htmlspecialchars($course_units) : 'No data available') . "</li>
        <li><b>Course Description:</b> " . (!empty($course_description) ? htmlspecialchars($course_description) : 'No data available') . "</li>
        <li><b>Prerequisites:</b> " . (!empty($prerequisites_corequisites) ? htmlspecialchars($prerequisites_corequisites) : 'No data available') . "</li>
        <li><b>Contact Hours:</b> " . (!empty($contact_hours) ? htmlspecialchars($contact_hours) : 'No data available') . "</li>
      </ul>";

// Vision, Mission, and Goals
echo "<h3>I. School's Vision, Mission, Goal, Objectives, Michaelinian Identity</h3>
      <h4>Vision</h4>
      <p>Saint Michael College of Caraga envisions to be a university by 2035 and upholds spiritual formation and excellence in teaching, service, and research.</p>";

echo "<h4>Mission</h4>
      <ul>
        <li>SMCC shall provide spiritual formation and learning culture.</li>
        <li>SMCC shall engage in dynamic, innovative, and interdisciplinary research.</li>
        <li>SMCC shall serve the diverse and local communities through service-learning.</li>
      </ul>";

echo "<h4>Goal</h4>
      <p>Uphold Culture of Excellence in Spiritual Formation, Instruction, Research, and Extension.</p>";

// GILO Mapping Table
echo "<h4>Program Mapping</h4>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead><tr><th>Program Intended Learning Outcomes (PILOs)</th><th>Graduate Intended Learning Outcomes (GILOs)</th></tr></thead><tbody>";
if (!empty($pilo_gilo)) {
    foreach ($pilo_gilo as $mapping) {
        echo "<tr><td>" . htmlspecialchars($mapping['pilo']) . "</td><td>" . htmlspecialchars($mapping['gilo']) . "</td></tr>";
    }
} else {
    echo "<tr><td colspan='2'>No PILOs-GILOs data available.</td></tr>";
}
echo "</tbody></table>";

// CILO Mapping Table
echo "<h3>Course Intended Learning Outcomes (CILO)</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead><tr><th>CILO Description</th><th>GILO 1</th><th>GILO 2</th></tr></thead><tbody>";
if (!empty($cilos)) {
    foreach ($cilos as $cilo) {
        echo "<tr><td>" . htmlspecialchars($cilo['description']) . "</td><td>" . htmlspecialchars($cilo['gilo1']) . "</td><td>" . htmlspecialchars($cilo['gilo2']) . "</td></tr>";
    }
} else {
    echo "<tr><td colspan='3'>No CILOs-GILOs data available.</td></tr>";
}
echo "</tbody></table>";

// Context Table
echo "<h3>Context</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead>
          <tr>
            <th>Section</th>
            <th>Hours</th>
            <th>ILO / Competency(ies)</th>
            <th>Topics</th>
            <th>Institutional Values</th>
            <th>Teaching Activities</th>
            <th>Resources</th>
            <th>Assessment Tasks</th>
            <th>Course Map</th>
          </tr>
        </thead>
        <tbody>";
if (!empty($context)) {
    foreach ($context as $row) {
        echo "<tr>
                <td>" . htmlspecialchars($row['section']) . "</td>
                <td>" . htmlspecialchars($row['hours']) . "</td>
                <td>" . htmlspecialchars($row['ilo']) . "</td>
                <td>" . htmlspecialchars($row['topics']) . "</td>
                <td>" . htmlspecialchars($row['institutional_values']) . "</td>
                <td>" . htmlspecialchars($row['teaching_activities']) . "</td>
                <td>" . htmlspecialchars($row['resources']) . "</td>
                <td>" . htmlspecialchars($row['assessment']) . "</td>
                <td>" . htmlspecialchars($row['course_map']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='9'>No context data available.</td></tr>";
}
echo "</tbody></table>";

// Performance Tasks Section
echo "<h3>Performance Tasks</h3>";
echo "<p>" . (!empty($performance_tasks) ? htmlspecialchars($performance_tasks) : 'No data available') . "</p>";

echo "</body></html>";

$conn->close();
?>
