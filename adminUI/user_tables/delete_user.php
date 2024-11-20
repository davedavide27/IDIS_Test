<?php
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

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Ensure the required fields are present
if (isset($input['id']) && isset($input['user_type'])) {
    $id = $input['id'];
    $user_type = $input['user_type'];

    // Map user types to their corresponding tables
    $table_mapping = [
        'student' => 'student',
        'instructor' => 'instructor',
        'dean' => 'dean',
        'edp' => 'edp',
        'vp' => 'vp',
        'librarian' => 'librarian',
    ];

    if (array_key_exists($user_type, $table_mapping)) {
        $table = $table_mapping[$user_type];
        $id_column = $user_type . "_ID";  // Dynamic ID column name based on user type

        // Prepare and execute the delete query
        $stmt = $conn->prepare("DELETE FROM $table WHERE $id_column = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Return success response
            echo json_encode(['success' => true]);
        } else {
            // Return error response
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }

        $stmt->close();
    } else {
        // Invalid user type
        echo json_encode(['success' => false, 'message' => 'Invalid user type']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
}

$conn->close();
