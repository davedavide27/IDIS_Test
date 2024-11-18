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

// Define mappings for user types
$user_type_mappings = [
    'student_table' => [
        'table' => 'student',
        'id' => 'student_ID',
        'name' => "CONCAT(student_fname, ' ', student_mname, ' ', student_lname)",
        'section' => 'section',
        'year' => 'year_level',
        'department' => 'department',
        'filters' => ['year', 'course', 'section', 'department']
    ],
    'instructor_table' => [
        'table' => 'instructor',
        'id' => 'instructor_ID',
        'name' => "CONCAT(instructor_fname, ' ', instructor_mname, ' ', instructor_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'dean_table' => [
        'table' => 'dean',
        'id' => 'dean_ID',
        'name' => "CONCAT(dean_fname, ' ', dean_mname, ' ', dean_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'edp_table' => [
        'table' => 'edp',
        'id' => 'edp_ID',
        'name' => "CONCAT(edp_fname, ' ', edp_mname, ' ', edp_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'vp_table' => [
        'table' => 'vp',
        'id' => 'vp_ID',
        'name' => "CONCAT(vp_fname, ' ', vp_mname, ' ', vp_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'librarian_table' => [
        'table' => 'librarian',
        'id' => 'librarian_ID',
        'name' => "CONCAT(librarian_fname, ' ', librarian_mname, ' ', librarian_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
];

// Get user type from query parameter or default to 'student_table'
$user_type = isset($_GET['user_type']) && array_key_exists($_GET['user_type'], $user_type_mappings)
    ? $_GET['user_type']
    : 'student_table';

// Get filter values from query parameters
$year_filter = isset($_GET['year']) ? $_GET['year'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$section_filter = isset($_GET['section']) ? $_GET['section'] : '';
$department_filter = isset($_GET['department']) ? $_GET['department'] : '';

// Prepare SQL query with filters
$mapping = $user_type_mappings[$user_type];

// Base SQL query: Include course, department, and year_level only for student_table
$sql = sprintf(
    "SELECT %s AS id, %s AS name, %s AS section, %s AS year %s FROM %s WHERE 1",
    $mapping['id'],
    $mapping['name'],
    $mapping['section'],
    $mapping['year'],
    ($user_type === 'student_table' ? ', course, department, year_level' : ''),
    $mapping['table']
);

// Apply filters only if they exist for the user type
if ($user_type === 'student_table') {
    if (in_array('year', $mapping['filters']) && $year_filter) {
        $sql .= " AND year_level = '$year_filter'";
    }
    if (in_array('course', $mapping['filters']) && $course_filter) {
        $sql .= " AND course = '$course_filter'";
    }
    if (in_array('section', $mapping['filters']) && $section_filter) {
        $sql .= " AND section = '$section_filter'";
    }
    if (in_array('department', $mapping['filters']) && $department_filter) {
        $sql .= " AND department = '$department_filter'";
    }
} else {
    // For other user types, ensure 'course' and 'department' are not added
    if ($user_type === 'dean_table') {
        // For dean_table, ensure that 'course' and 'department' are excluded
        $sql = str_replace(", course", "", $sql);
        $sql = str_replace(" AND department = '$department_filter'", "", $sql);
    }
}

// Prepare query based on user type
if ($user_type === 'student_table') {
    $query = "SELECT student_id, CONCAT(student_fname, ' ', student_mname, ' ', student_lname) AS full_name, section, year_level, course, department, password FROM student";
} elseif ($user_type === 'instructor_table') {
    $query = "SELECT instructor_ID, CONCAT(instructor_fname, ' ', instructor_mname, ' ', instructor_lname) AS full_name, password, department FROM instructor";
} elseif ($user_type === 'dean_table') {
    $query = "SELECT dean_ID, CONCAT(dean_fname, ' ', dean_mname, ' ', dean_lname) AS full_name, password, department, password FROM dean";
} elseif ($user_type === 'edp_table') {
    $query = "SELECT edp_ID, CONCAT(edp_fname, ' ', edp_mname, ' ', edp_lname) AS full_name, password FROM edp";
} elseif ($user_type === 'vp_table') {
    $query = "SELECT vp_ID, CONCAT(vp_fname, ' ', vp_mname, ' ', vp_lname) AS full_name, password FROM vp";
} elseif ($user_type === 'librarian_table') {
    $query = "SELECT librarian_ID, CONCAT(librarian_fname, ' ', librarian_mname, ' ', librarian_lname) AS full_name, password, department FROM librarian";
} 


// Execute query and fetch users
$result = mysqli_query($conn, $query);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Close the connection
mysqli_close($conn);

// Now you can use the $users array as needed
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../user_tables/list_table.css">
</head>

<body>
    <div class="containerOfAll">
        <a href="../index.php">
            <button class="back-button">Back</button>
        </a>
        <h3>User Management</h3>

        <!-- Navigation Bar -->
        <div class="nav-container">
            <nav class="navigation-tabs">
                <ul>
                    <?php foreach ($user_type_mappings as $key => $value): ?>
                        <li>
                            <a href="?user_type=<?php echo htmlspecialchars($key); ?>"
                                class="<?php echo $key === $user_type ? 'active' : ''; ?>">
                                <?php echo ucwords(str_replace('_', ' ', str_replace('_table', '', $key))); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>

        <!-- Custom Tables for Each User Type -->
        <?php if ($user_type === 'student_table'): ?>
            <!-- Student Table -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Section</th>
                        <th>Year Level</th>
                        <th>Course</th>
                        <th>Department</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['section']); ?></td>
                            <td><?php echo htmlspecialchars($user['year_level']); ?></td>
                            <td><?php echo htmlspecialchars($user['course']); ?></td>
                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td>
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($user_type === 'instructor_table'): ?>
            <!-- Instructor Table -->
            <table>
                <thead>
                    <tr>
                        <th>Instructor ID</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Department</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['instructor_ID']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td>
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($user_type === 'dean_table'): ?>
            <!-- Dean Table -->
            <table>
                <thead>
                    <tr>
                        <th>Dean ID</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Department</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['dean_ID']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td><?php echo htmlspecialchars($user['department']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td>
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($user_type === 'edp_table'): ?>
            <!-- EDP Table -->
            <table>
                <thead>
                    <tr>
                        <th>EDP ID</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['edp_ID']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td>
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($user_type === 'vp_table'): ?>
            <!-- VP Table -->
            <table>
                <thead>
                    <tr>
                        <th>VP ID</th>
                        <th>Full Name</th>
                        <th>Password</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['vp_ID']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['password']); ?></td>
                            <td>
                                <button>Edit</button>
                                <button>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</body>

</html>
