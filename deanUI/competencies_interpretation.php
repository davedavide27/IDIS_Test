<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_ID'])) {
    echo json_encode(['error' => 'User not logged in.']);
    exit();
}

// Check if subject_code is provided via POST
if (!isset($_POST['subject_code'])) {
    echo json_encode(['error' => 'No subject code provided.']);
    exit();
}

$subject_code = $_POST['subject_code'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Initialize response
$response = [
    'competencies' => []
];

// Fetch competencies and teacher's remarks for the selected subject
$sqlCompetencies = "SELECT competency_description, remarks FROM competencies WHERE subject_code = ?";
$stmt = $conn->prepare($sqlCompetencies);
$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Initialize competencies with student rating fields set to 0
        $response['competencies'][] = [
            'competency_description' => htmlspecialchars($row['competency_description']),
            'remarks' => htmlspecialchars($row['remarks']),
            'total_rate' => 0,
            'rating_count' => 0,
            'average_student_rating' => 0,
            'interpretation' => 'Not Implemented (0%)'
        ];
    }
} else {
    echo json_encode(['error' => 'No competencies data found for the selected subject.']);
    exit();
}
$stmt->close();

// Fetch student ratings with proper alignment to competencies
$sqlRatings = "SELECT r.topic, r.rating, c.competency_description 
               FROM course_outline_ratings r 
               JOIN competencies c 
               ON r.subject_code = c.subject_code AND r.topic = c.competency_description 
               WHERE r.subject_code = ?";

$stmt = $conn->prepare($sqlRatings);
$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();

$ratingConversion = [
    5 => 70, // Rate 5 = 70%
    4 => 55,
    3 => 40,
    2 => 25,
    1 => 10
];

// Process ratings and map them to corresponding competencies
while ($row = $result->fetch_assoc()) {
    foreach ($response['competencies'] as &$competency) {
        if ($competency['competency_description'] === htmlspecialchars($row['competency_description'])) {
            // Convert rating and update totals
            if (isset($ratingConversion[$row['rating']])) {
                $competency['total_rate'] += $ratingConversion[$row['rating']];
                $competency['rating_count']++;
            }
        }
    }
}

$stmt->close();

// Calculate average rating and interpretation for each competency
foreach ($response['competencies'] as &$competency) {
    if ($competency['rating_count'] > 0) {
        // Calculate average student rating
        $averageStudentRating = $competency['total_rate'] / $competency['rating_count'];
        $competency['average_student_rating'] = number_format($averageStudentRating, 2) . '%';

        // Add teacher's remark to calculate final interpretation
        $teacherRemark = $competency['remarks'] === 'IMPLEMENTED' ? 30 : 0;
        $systemInterpretation = $averageStudentRating + $teacherRemark;

        // Handle the system interpretation based on 56% threshold
        $competency['interpretation'] = ($systemInterpretation >= 56) 
            ? 'Implemented (' . number_format($systemInterpretation, 2) . '%)' 
            : 'Not Implemented (' . number_format($systemInterpretation, 2) . '%)';
    } else {
        // No ratings for this competency
        $competency['average_student_rating'] = 'No ratings available';
        $competency['interpretation'] = 'Not Implemented (0%)';
    }
}

$conn->close();

// Return the data as JSON
echo json_encode($response);
