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

// Initialize arrays for PILO-GILO and CILO-GILO mappings
$pilo = []; 
$gilo = []; 
$cilo = []; 
$cilo_gilo1 = []; 
$cilo_gilo2 = []; 

// Fetch subject code from POST or GET data
$subject_code = isset($_POST['subject_code']) ? $_POST['subject_code'] : (isset($_GET['subject_code']) ? $_GET['subject_code'] : '');

// Ensure subject code is provided
if (!empty($subject_code)) {
    // Fetch PILO-GILO mappings
    $sqlPiloGilo = "SELECT pilo, gilo FROM pilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?";
    if ($stmtPiloGilo = $conn->prepare($sqlPiloGilo)) {
        $stmtPiloGilo->bind_param("si", $subject_code, $instructor_ID);
        if ($stmtPiloGilo->execute()) {
            $resultPiloGilo = $stmtPiloGilo->get_result();
            while ($row = $resultPiloGilo->fetch_assoc()) {
                $pilo[] = htmlspecialchars($row['pilo']);
                $gilo[] = htmlspecialchars($row['gilo']);
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

    // Fetch CILO-GILO mappings
    $sqlCiloGilo = "SELECT cilo_description, gilo1, gilo2 FROM cilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?";
    if ($stmtCiloGilo = $conn->prepare($sqlCiloGilo)) {
        $stmtCiloGilo->bind_param("si", $subject_code, $instructor_ID);
        if ($stmtCiloGilo->execute()) {
            $resultCiloGilo = $stmtCiloGilo->get_result();
            while ($row = $resultCiloGilo->fetch_assoc()) {
                $cilo[] = htmlspecialchars($row['cilo_description']);
                $cilo_gilo1[] = htmlspecialchars($row['gilo1']);
                $cilo_gilo2[] = htmlspecialchars($row['gilo2']);
            }
            $resultCiloGilo->free();
        } else {
            echo json_encode(['error' => 'Failed to fetch CILO-GILO mappings']);
            exit();
        }
        $stmtCiloGilo->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare CILO-GILO query']);
        exit();
    }
} else {
    echo json_encode(['error' => 'Subject code is missing']);
    exit();
}

// Return the data as JSON
echo json_encode([
    'pilo' => $pilo,
    'gilo' => $gilo,
    'cilo' => $cilo,
    'cilo_gilo1' => $cilo_gilo1,
    'cilo_gilo2' => $cilo_gilo2
]);

$conn->close();
?>
