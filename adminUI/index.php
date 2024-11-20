<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Admin</title>
    <link rel="stylesheet" href="admin_ui.css">
    
</head>

<body>
    <!-- Sidebar -->
    <div class="containerOfSide">
        <h3>System Admin</h3>
        <div class="nav-container">
            <nav class="navigation-tabs">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="user_tables/users.php?user_type=student_table">User Management</a></li>
                    <li><a href="system_settings.php">System Settings</a></li>
                    <li><a href="#announcements">Announcements</a></li>
                    <li><a href="#logs">Logs</a></li>
                </ul>
                <ul>
                    <form action="../logout.php" method="post">
                        <button class="logout-btn" type="submit">Logout</button>
                    </form>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <main class="content-box">
        <div class="content-container">
            <?php
            // Database connection
            $conn = new mysqli('localhost', 'root', '', 'ids_database');

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Queries to count users for each role
            $roles = [
                'Students' => "SELECT COUNT(*) AS count FROM student",
                'Instructors' => "SELECT COUNT(*) AS count FROM instructor",
                'Deans' => "SELECT COUNT(*) AS count FROM dean",
                'Chair Programs' => "SELECT COUNT(*) AS count FROM program_chair",
                'Total Subjects' => "SELECT COUNT(*) AS count FROM subject",
                'Vice Presidents' => "SELECT COUNT(*) AS count FROM vp",
                'EDPs' => "SELECT COUNT(*) AS count FROM edp",
            ];

            foreach ($roles as $role => $query) {
                $result = $conn->query($query);
                $count = $result->fetch_assoc()['count'];
                echo "<section>
                        <h2>{$role}</h2>
                        <p>{$count} " . ($role === 'Total Subjects' ? 'subjects' : 'users') . "</p>
                      </section>";
            }
            ?>
        </div>
    </main>


</body>

</html>