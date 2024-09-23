<?php
session_start();

header('Content-Type: application/json'); // Set header to return JSON content

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
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit();
}

// Initialize arrays for CILO and corresponding mapping for columns a to o
$cilo = [];
$a = [];
$b = [];
$c = [];
$d = [];
$e = [];
$f = [];
$g = [];
$h = [];
$i = [];
$j = [];
$k = [];
$l = [];
$m = [];
$n = [];
$o = [];

// Fetch subject code from POST or GET data
$subject_code = isset($_POST['subject_code']) ? $_POST['subject_code'] : (isset($_GET['subject_code']) ? $_GET['subject_code'] : '');

if (!empty($subject_code)) {
    // Fetch data for the CILO mapping
    $sqlCiloGilo = "SELECT cilo_description, a, b, c, d, e, f, g, h, i, j, k, l, m, n, o 
                    FROM cilo_gilo_map 
                    WHERE subject_code = ? AND instructor_ID = ?";

    if ($stmtCiloGilo = $conn->prepare($sqlCiloGilo)) {
        $stmtCiloGilo->bind_param("si", $subject_code, $instructor_ID);

        if ($stmtCiloGilo->execute()) {
            $resultCiloGilo = $stmtCiloGilo->get_result();

            if ($resultCiloGilo->num_rows > 0) {
                while ($row = $resultCiloGilo->fetch_assoc()) {
                    $cilo[] = htmlspecialchars($row['cilo_description'] ?? '');
                    $a[] = htmlspecialchars($row['a'] ?? '');
                    $b[] = htmlspecialchars($row['b'] ?? '');
                    $c[] = htmlspecialchars($row['c'] ?? '');
                    $d[] = htmlspecialchars($row['d'] ?? '');
                    $e[] = htmlspecialchars($row['e'] ?? '');
                    $f[] = htmlspecialchars($row['f'] ?? '');
                    $g[] = htmlspecialchars($row['g'] ?? '');
                    $h[] = htmlspecialchars($row['h'] ?? '');
                    $i[] = htmlspecialchars($row['i'] ?? '');
                    $j[] = htmlspecialchars($row['j'] ?? '');
                    $k[] = htmlspecialchars($row['k'] ?? '');
                    $l[] = htmlspecialchars($row['l'] ?? '');
                    $m[] = htmlspecialchars($row['m'] ?? '');
                    $n[] = htmlspecialchars($row['n'] ?? '');
                    $o[] = htmlspecialchars($row['o'] ?? '');
                }
            } else {
                echo json_encode(['error' => 'No data found for the given subject code and instructor']);
                exit();
            }

            $resultCiloGilo->free();
        } else {
            echo json_encode(['error' => 'Failed to execute query: ' . $stmtCiloGilo->error]);
            exit();
        }

        $stmtCiloGilo->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare query: ' . $conn->error]);
        exit();
    }
} else {
    echo json_encode(['error' => 'Subject code is missing']);
    exit();
}

// Return the data as JSON
echo json_encode([
    'cilo' => $cilo,
    'a' => $a,
    'b' => $b,
    'c' => $c,
    'd' => $d,
    'e' => $e,
    'f' => $f,
    'g' => $g,
    'h' => $h,
    'i' => $i,
    'j' => $j,
    'k' => $k,
    'l' => $l,
    'm' => $m,
    'n' => $n,
    'o' => $o
]);

$conn->close();
