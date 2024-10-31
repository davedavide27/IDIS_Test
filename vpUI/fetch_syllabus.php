<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    header("Location: ../login.php");
    exit();
}

// Check if subject_code or subject_name is provided via POST
if (!isset($_POST['subject_code']) && !isset($_POST['subject_name'])) {
    echo json_encode(['error' => 'No subject code or name provided.']);
    exit();
}

$subject_code = isset($_POST['subject_code']) ? $_POST['subject_code'] : null;
$subject_name = isset($_POST['subject_name']) ? $_POST['subject_name'] : null;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Initialize syllabus data
$response = [
    'course_units' => '',
    'course_description' => '',
    'prerequisites_corequisites' => '',
    'contact_hours' => '',
    'performance_tasks' => '',
    'cilos' => [],
    'pilo_gilo' => [],
    'context' => []
];

// Helper function to execute a prepared statement
function executeQuery($conn, $query, $params) {
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    } else {
        echo json_encode(['error' => 'Error preparing query: ' . $conn->error]);
        exit();
    }
}

// Build the dynamic query based on what is provided (subject_code or subject_name or both)
$queryConditions = [];
$queryParams = [];

if ($subject_code) {
    $queryConditions[] = "subject_code = ?";
    $queryParams[] = $subject_code;
}

if ($subject_name) {
    $queryConditions[] = "subject_name = ?";
    $queryParams[] = $subject_name;
}

// Ensure at least one condition is present
if (empty($queryConditions)) {
    echo json_encode(['error' => 'No valid query conditions.']);
    exit();
}

// Generate the dynamic query condition string
$queryConditionString = implode(' AND ', $queryConditions);

// Fetch syllabus data
$sqlSyllabus = "SELECT * FROM syllabus WHERE $queryConditionString";
$result = executeQuery($conn, $sqlSyllabus, $queryParams);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $response['course_units'] = htmlspecialchars($row['course_units']);
    $response['course_description'] = htmlspecialchars($row['course_description']);
    $response['prerequisites_corequisites'] = htmlspecialchars($row['prerequisites_corequisites']);
    $response['contact_hours'] = htmlspecialchars($row['contact_hours']);
    $response['performance_tasks'] = htmlspecialchars($row['performance_tasks']);
} else {
    echo json_encode(['error' => 'No syllabus data found for the selected subject.']);
    exit();
}

// Fetch PILO-GILO mappings
$sqlPiloGilo = "SELECT * FROM pilo_gilo_map WHERE $queryConditionString";
$result = executeQuery($conn, $sqlPiloGilo, $queryParams);

while ($row = $result->fetch_assoc()) {
    $response['pilo_gilo'][] = [
        'pilo' => htmlspecialchars($row['pilo']),
        'gilo' => htmlspecialchars($row['gilo'])
    ];
}

// Fetch CILO-GILO mappings
$sqlCiloGilo = "SELECT * FROM cilo_gilo_map WHERE $queryConditionString";
$result = executeQuery($conn, $sqlCiloGilo, $queryParams);

while ($row = $result->fetch_assoc()) {
    $response['cilos'][] = [
        'description' => htmlspecialchars($row['cilo_description']),
        'gilo1' => htmlspecialchars($row['gilo1']),
        'gilo2' => htmlspecialchars($row['gilo2'])
    ];
}

// Fetch context data
$sqlContext = "SELECT * FROM context WHERE $queryConditionString";
$result = executeQuery($conn, $sqlContext, $queryParams);

while ($row = $result->fetch_assoc()) {
    $response['context'][] = [
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

$conn->close();

// Return data as JSON
echo json_encode($response);
