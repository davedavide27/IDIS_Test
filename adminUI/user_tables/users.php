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
    'student' => [
        'table' => 'student',
        'id' => 'student_ID',
        'name' => "CONCAT(student_fname, ' ', student_mname, ' ', student_lname)",
        'section' => 'section',
        'year' => 'year_level',
        'department' => 'department',
        'filters' => ['year', 'course', 'section', 'department']
    ],
    'instructor' => [
        'table' => 'instructor',
        'id' => 'instructor_ID',
        'name' => "CONCAT(instructor_fname, ' ', instructor_mname, ' ', instructor_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'dean' => [
        'table' => 'dean',
        'id' => 'dean_ID',
        'name' => "CONCAT(dean_fname, ' ', dean_mname, ' ', dean_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'edp' => [
        'table' => 'edp',
        'id' => 'edp_ID',
        'name' => "CONCAT(edp_fname, ' ', edp_mname, ' ', edp_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'vp' => [
        'table' => 'vp',
        'id' => 'vp_ID',
        'name' => "CONCAT(vp_fname, ' ', vp_mname, ' ', vp_lname)",
        'section' => 'department',
        'year' => "'N/A'",
        'filters' => []
    ],
    'librarian' => [
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
    : 'student';

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
    ($user_type === 'student' ? ', course, department, year_level' : ''),
    $mapping['table']
);

// Apply filters only if they exist for the user type
if ($user_type === 'student') {
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
if ($user_type === 'student') {
    $query = "SELECT student_ID, CONCAT(student_fname, ' ', student_mname, ' ', student_lname) AS full_name, section, year_level, course, department, password FROM student";
} elseif ($user_type === 'instructor') {
    $query = "SELECT instructor_ID, CONCAT(instructor_fname, ' ', instructor_mname, ' ', instructor_lname) AS full_name, password, department FROM instructor";
} elseif ($user_type === 'dean') {
    $query = "SELECT dean_ID, CONCAT(dean_fname, ' ', dean_mname, ' ', dean_lname) AS full_name, password, department, password FROM dean";
} elseif ($user_type === 'edp') {
    $query = "SELECT edp_ID, CONCAT(edp_fname, ' ', edp_mname, ' ', edp_lname) AS full_name, password FROM edp";
} elseif ($user_type === 'vp') {
    $query = "SELECT vp_ID, CONCAT(vp_fname, ' ', vp_mname, ' ', vp_lname) AS full_name, password FROM vp";
} elseif ($user_type === 'librarian') {
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
    <link rel="stylesheet" href="user_modal.css">
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

        <!-- Dynamic User Table -->
        <table>
            <thead>
                <tr>
                    <!-- Dynamic Headers -->
                    <?php
                    // Headers based on user type
                    $headers = $user_type === 'student' ? ['ID', 'Name', 'Section', 'Year Level', 'Course', 'Department', 'Password', 'Actions'] : ($user_type === 'instructor' ? ['Instructor ID', 'Name', 'Password', 'Department', 'Actions'] : ($user_type === 'dean' ? ['Dean ID', 'Name', 'Password', 'Department', 'Actions'] : ($user_type === 'edp' ? ['EDP ID', 'Name', 'Password', 'Actions'] : ($user_type === 'vp' ? ['VP ID', 'Name', 'Password', 'Actions'] :
                        ['Librarian ID', 'Name', 'Password', 'Department', 'Actions']))));

                    foreach ($headers as $header) {
                        echo "<th>$header</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <?php
                        // Display user details based on user type
                        if ($user_type === 'student') {
                            echo "<td>{$user['student_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['section']}</td>
                                  <td>{$user['year_level']}</td>
                                  <td>{$user['course']}</td>
                                  <td>{$user['department']}</td>
                                  <td>{$user['password']}</td>";
                            $id = $user['student_ID'];
                        } elseif ($user_type === 'instructor') {
                            echo "<td>{$user['instructor_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['password']}</td>
                                  <td>{$user['department']}</td>";
                            $id = $user['instructor_ID'];
                        } elseif ($user_type === 'dean') {
                            echo "<td>{$user['dean_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['password']}</td>
                                  <td>{$user['department']}</td>";
                            $id = $user['dean_ID'];
                        } elseif ($user_type === 'edp') {
                            echo "<td>{$user['edp_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['password']}</td>";
                            $id = $user['edp_ID'];
                        } elseif ($user_type === 'vp') {
                            echo "<td>{$user['vp_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['password']}</td>";
                            $id = $user['vp_ID'];
                        } elseif ($user_type === 'librarian') {
                            echo "<td>{$user['librarian_ID']}</td>
                                  <td>{$user['full_name']}</td>
                                  <td>{$user['password']}</td>
                                  <td>{$user['department']}</td>";
                            $id = $user['librarian_ID'];
                        }
                        ?>
                        <td>
                            <!-- Pass dynamic $id to open modal -->
                            <button onclick="openModal(<?php echo htmlspecialchars(json_encode($user)); ?>, '<?php echo $id; ?>', '<?php echo $user_type; ?>')">Edit</button>
                            <button onclick="deleteUser('<?php echo $id; ?>', '<?php echo $user_type; ?>')">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>


<!-- Modal for Editing -->
<div class="backdrop" id="backdrop"></div>
<div id="editModal" class="modal">
    <div class="modal-header">
        Edit User
        <button class="close-button" onclick="closeModal()">&times;</button>
    </div>
    <div class="modal-body">
        <form id="editForm"></form>
    </div>
    <div class="modal-footer">
        <button onclick="submitEdit()">Save</button>
        <button onclick="closeModal()">Cancel</button>
    </div>
</div>

<script>
    const modal = document.getElementById('editModal');
    const backdrop = document.getElementById('backdrop');
    const form = document.getElementById('editForm');
    let selectedUserId = null;
    let selectedUserType = null;

    function openModal(user, id, userType) {
        selectedUserId = id;
        selectedUserType = userType; // Store the user type correctly
        modal.classList.add('show');
        backdrop.classList.add('show');
        form.innerHTML = ''; // Clear previous fields

        // Populate form based on user data
        for (const key in user) {
            if (key === 'department') {
                // Fetch department options dynamically
                fetch('get_departments.php')
                    .then(response => response.json())
                    .then(departments => {
                        let options = departments
                            .map(department => ` 
                            <option value="${department}" ${department === user[key] ? 'selected' : ''}>
                                ${department}
                            </option>`)
                            .join('');
                        form.innerHTML += `
                        <label>Department</label>
                        <select name="department">
                            ${options}
                        </select>
                    `;
                    });
            } else if (key === 'full_name') {
                // Split full name into first, middle, and last name
                const nameParts = user[key].split(' ');
                const firstName = nameParts[0] || '';
                const middleName = nameParts[1] || '';
                const lastName = nameParts.slice(2).join(' ') || '';

                form.innerHTML += `
                <label>First Name</label>
                <input type="text" name="first_name" value="${firstName}" />
                <label>Middle Name</label>
                <input type="text" name="middle_name" value="${middleName}" />
                <label>Last Name</label>
                <input type="text" name="last_name" value="${lastName}" />
            `;
            } else if (key !== 'user_type' && key !== 'id') {
                form.innerHTML += `
                <label>${capitalizeFirstLetter(key.replace('_', ' '))}</label>
                <input type="text" name="${key}" value="${user[key]}" />
            `;
            }
        }

        // Ensure that userType is passed along with the form submission
        form.innerHTML += `<input type="hidden" name="user_type" value="${userType}" />`;
    }



    // Function to capitalize the first letter of each word in a string
    function capitalizeFirstLetter(string) {
        return string.replace(/\b\w/g, function(char) {
            return char.toUpperCase();
        });
    }

    // Close Modal
    function closeModal() {
        modal.classList.remove('show');
        backdrop.classList.remove('show');
    }

    function submitEdit() {
        const formData = new FormData(form);
        formData.append('user_type', selectedUserType); // User type to be used in backend processing
        formData.append('id', selectedUserId); // Ensure 'selectedUserId' is set correctly on edit button click

        fetch('update_users.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('User updated successfully!');
                    window.location.reload(); // Refresh the page to reflect changes
                } else {
                    alert('Error: ' + data.message);
                }
            });
    }
    function deleteUser(userId, userType) {
    if (confirm("Are you sure you want to delete this user?")) {
        fetch('delete_user.php', {
            method: 'POST',
            body: JSON.stringify({ id: userId, user_type: userType }), // Send the user ID and type
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully!');
                window.location.reload(); // Refresh the page to reflect changes
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete user.');
        });
    }
}

</script>