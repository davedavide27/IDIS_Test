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

// Define user type mappings
$user_type_mappings = [
    'student' => [
        'table' => 'student',
        'id' => 'student_ID',
        'fname' => 'student_fname',
        'mname' => 'student_mname',
        'lname' => 'student_lname',
        'section' => 'section',
        'year' => 'year_level',
        'course' => 'course',
        'department' => 'department',
        'password' => 'password',  // Added password field for student
    ],
    'instructor' => [
        'table' => 'instructor',
        'id' => 'instructor_ID',
        'fname' => 'instructor_fname',
        'mname' => 'instructor_mname',
        'lname' => 'instructor_lname',
        'section' => 'department',
        'year' => "'N/A'",
        'course' => 'course', // Example for course if needed
        'department' => 'department',
        'password' => 'password', // Added password field for instructor
    ],
    'dean' => [
        'table' => 'dean',
        'id' => 'dean_ID',
        'fname' => 'dean_fname',
        'mname' => 'dean_mname',
        'lname' => 'dean_lname',
        'section' => 'department',
        'year' => "'N/A'",
        'course' => 'course', // Optional for dean
        'department' => 'department',
        'password' => 'password', // Added password field for dean
    ],
    'edp' => [
        'table' => 'edp',
        'id' => 'edp_ID',
        'fname' => 'edp_fname',
        'mname' => 'edp_mname',
        'lname' => 'edp_lname',
        'section' => 'department',
        'year' => "'N/A'",
        'course' => 'course', // Optional for EDP
        'department' => 'department',
        'password' => 'password', // Added password field for EDP
    ],
    'vp' => [
        'table' => 'vp',
        'id' => 'vp_ID',
        'fname' => 'vp_fname',
        'mname' => 'vp_mname',
        'lname' => 'vp_lname',
        'section' => 'department',
        'year' => "'N/A'",
        'course' => 'course', // Optional for VP
        'department' => 'department',
        'password' => 'password', // Added password field for VP
    ],
    'librarian' => [
        'table' => 'librarian',
        'id' => 'librarian_ID',
        'fname' => 'librarian_fname',
        'mname' => 'librarian_mname',
        'lname' => 'librarian_lname',
        'section' => 'department',
        'year' => "'N/A'",
        'course' => 'course', // Optional for librarian
        'department' => 'department',
        'password' => 'password', // Added password field for librarian
    ],
];

// Get data from the POST request
$user_id = isset($_POST['id']) ? $_POST['id'] : null;  // Existing user ID
$new_user_id = isset($_POST['new_user_id']) ? $_POST['new_user_id'] : null; // New user ID (if editable)
$user_type = isset($_POST['user_type']) ? $_POST['user_type'] : null;

// Ensure all required fields are set
$first_name = isset($_POST['first_name']) ? $_POST['first_name'] : null;
$middle_name = isset($_POST['middle_name']) ? $_POST['middle_name'] : null;
$last_name = isset($_POST['last_name']) ? $_POST['last_name'] : null;
$section = isset($_POST['section']) ? $_POST['section'] : null;
$year = isset($_POST['year_level']) ? $_POST['year_level'] : null;
$course = isset($_POST['course']) ? $_POST['course'] : null;
$department = isset($_POST['department']) ? $_POST['department'] : null;

// Optionally handle password (to be hashed later)
$password = isset($_POST['password']) ? $_POST['password'] : null;

// Check if user type is valid
if (array_key_exists($user_type, $user_type_mappings)) {
    // Get the corresponding table and column names from the mapping
    $mapping = $user_type_mappings[$user_type];
    $table = $mapping['table'];
    $id_column = $mapping['id'];
    $fname_column = $mapping['fname'];
    $mname_column = $mapping['mname'];
    $lname_column = $mapping['lname'];
    $section_column = $mapping['section'];
    $year_column = $mapping['year'];
    $course_column = isset($mapping['course']) ? $mapping['course'] : null;
    $dept_column = $mapping['department'];
    $password_column = $mapping['password'];

    // Handle optional fields like 'middle_name', 'year', 'course', or 'department' being null
    $update_fields = [];
    $params = [];
    $types = "";

    // Add first_name, middle_name, last_name
    if ($first_name) {
        $update_fields[] = "$fname_column = ?";
        $params[] = $first_name;
        $types .= "s";  // 's' stands for string
    }
    if ($middle_name) {
        $update_fields[] = "$mname_column = ?";
        $params[] = $middle_name;
        $types .= "s";
    }
    if ($last_name) {
        $update_fields[] = "$lname_column = ?";
        $params[] = $last_name;
        $types .= "s";
    }
    if ($section) {
        $update_fields[] = "$section_column = ?";
        $params[] = $section;
        $types .= "s";
    }
    if ($year) {
        $update_fields[] = "$year_column = ?";
        $params[] = $year;
        $types .= "s";
    }
    if ($course) {
        $update_fields[] = "$course_column = ?";
        $params[] = $course;
        $types .= "s";
    }
    if ($department) {
        $update_fields[] = "$dept_column = ?";
        $params[] = $department;
        $types .= "s";
    }

    // Handle password hashing (optional, for now commented out)
    if ($password) {
        // Uncomment the following line to hash the password before updating
        // $password = password_hash($password, PASSWORD_DEFAULT);  // This will hash the password securely

        // Add password update to the query
        $update_fields[] = "$password_column = ?";
        $params[] = $password;
        $types .= "s";  // Assuming password is a string
    }

    // If a new user ID is provided, add it to the update query
    if ($new_user_id) {
        $update_fields[] = "$id_column = ?";
        $params[] = $new_user_id;
        $types .= "s";  // 's' stands for string for the user ID
    }

    // Add the user_id condition to the query
    $update_sql = "UPDATE $table SET " . implode(", ", $update_fields) . " WHERE $id_column = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($update_sql)) {
        // Bind parameters dynamically
        $params[] = $user_id;  // Add original user_id as the last parameter
        $types .= "s";  // 's' stands for string

        $stmt->bind_param($types, ...$params);

        // Execute the query
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'User updated successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'Error updating user.'];
        }

        // Close the statement
        $stmt->close();
    } else {
        $response = ['success' => false, 'message' => 'Error preparing statement.'];
    }
} else {
    $response = ['success' => false, 'message' => 'Invalid user type.'];
}

// Close connection
$conn->close();

// Return response as JSON
echo json_encode($response);
