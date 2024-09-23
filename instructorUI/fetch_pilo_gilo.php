<?php
session_start();

// Check if the instructor is logged in
if (!isset($_SESSION['user_ID'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$instructor_ID = $_SESSION['user_ID'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ids_database";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

// Initialize arrays for PILO-GILO mappings
$pilo = []; 
$a = []; 
$b = []; 
$c = []; 
$d = []; 

// Fetch subject code from POST or GET data
$subject_code = isset($_POST['subject_code']) ? $_POST['subject_code'] : (isset($_GET['subject_code']) ? $_GET['subject_code'] : '');

// Ensure subject code is provided
if (!empty($subject_code)) {
    // Fetch PILO-GILO mappings with the new columns a, b, c, d
    $sqlPiloGilo = "SELECT pilo, a, b, c, d FROM pilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?";
    if ($stmtPiloGilo = $conn->prepare($sqlPiloGilo)) {
        $stmtPiloGilo->bind_param("si", $subject_code, $instructor_ID);
        if ($stmtPiloGilo->execute()) {
            $resultPiloGilo = $stmtPiloGilo->get_result();
            while ($row = $resultPiloGilo->fetch_assoc()) {
                $pilo[] = htmlspecialchars($row['pilo']);
                $a[] = htmlspecialchars($row['a']);
                $b[] = htmlspecialchars($row['b']);
                $c[] = htmlspecialchars($row['c']);
                $d[] = htmlspecialchars($row['d']);
            }
            $resultPiloGilo->free();
        
        } else {
            echo json_encode(['error' => 'Failed to fetch PILO-GILO mappings']);
            exit();
        }
        $stmtPiloGilo->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare PILO-GILO query']);
        exit();
    }
} else {
    echo json_encode(['error' => 'Subject code is missing']);
    exit();
}

// Return the data as JSON
echo json_encode([
    'pilo' => $pilo,
    'a' => $a,
    'b' => $b,
    'c' => $c,
    'd' => $d,
]);

$conn->close();
?>
