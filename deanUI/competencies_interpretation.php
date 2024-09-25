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

// Fetch competencies and remarks directly from the course_outline_ratings table
$sqlCompetencies = "SELECT competency_description, remarks 
                    FROM course_outline_ratings 
                    WHERE subject_code = ? 
                    GROUP BY competency_description, remarks";
$stmt = $conn->prepare($sqlCompetencies);
$stmt->bind_param("s", $subject_code);
$stmt->execute();
$result = $stmt->get_result();

// Rating conversion for the system
$ratingConversion = [
    5 => 70, // Rate 5 = 70%
    4 => 55,
    3 => 40,
    2 => 25,
    1 => 10
];

// Process competencies and calculate system interpretation
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $competencyDescription = htmlspecialchars($row['competency_description']);
        $teacherRemark = htmlspecialchars($row['remarks']);

        // Initialize total student rating and count for each competency
        $totalStudentRating = 0;
        $ratingCount = 0;

        // Fetch student ratings for each competency from course_outline_ratings
        $sqlRatings = "SELECT rating FROM course_outline_ratings 
                       WHERE subject_code = ? AND competency_description = ?";
        $stmtRatings = $conn->prepare($sqlRatings);
        $stmtRatings->bind_param("ss", $subject_code, $competencyDescription);
        $stmtRatings->execute();
        $resultRatings = $stmtRatings->get_result();

        // Process ratings and calculate the total student rating
        while ($ratingRow = $resultRatings->fetch_assoc()) {
            $rating = intval($ratingRow['rating']);
            if (isset($ratingConversion[$rating])) {
                $totalStudentRating += $ratingConversion[$rating];
                $ratingCount++;
            }
        }

        // Close the ratings statement
        $stmtRatings->close();

        // Calculate the average student rating if there are ratings
        $averageStudentRating = 0;
        if ($ratingCount > 0) {
            $averageStudentRating = $totalStudentRating / $ratingCount;
        }

        // Add teacher's remark to calculate the final system interpretation
        $teacherRemarkValue = ($teacherRemark === 'IMPLEMENTED') ? 30 : 0;
        $finalInterpretation = $averageStudentRating + $teacherRemarkValue;

        // Set interpretation based on the threshold (56%)
        $interpretation = ($finalInterpretation >= 56)
            ? 'Implemented (' . number_format($finalInterpretation, 2) . '%)'
            : 'Not Implemented (' . number_format($finalInterpretation, 2) . '%)';

        // Prepare the response for this competency
        $response['competencies'][] = [
            'competency_description' => $competencyDescription,
            'remarks' => $teacherRemark,
            'average_student_rating' => $ratingCount > 0 ? number_format($averageStudentRating, 2) . '%' : 'No ratings available',
            'interpretation' => $interpretation
        ];
    }
} else {
    echo json_encode(['error' => 'No competencies data found for the selected subject.']);
    exit();
}

// Close the database connection
$stmt->close();
$conn->close();

// Return the data as JSON
echo json_encode($response);
