<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../adminUI/course_table/course_table.css">
</head>

<body>
    <div class=" containerOfAll">
        <a href="../index.php">
            <button class="back-button">Back</button></a>
        <h3>Course Management</h3>

        <!-- Navigation Bar -->
        <div class="nav-container">
            <nav class="navigation-tabs">
                <ul>
                    <li><a href="../adminUI/course.php">Department 1</a></li>
                    <li><a href="">Department 2</a></li>
                </ul>
            </nav>
        </div>

        <div class="create-btn">
            <button><a href="../adminUI/course_table/create_subject.php">Create New Subject</a></button>
        </div>

        <!-- Content Sections -->


        <!-- User Table -->
        <table>
            <thead>
                <tr>
                    <th>Course Code</th>
                    <th>Subject Name</th>
                    <th>Instructor</th>
                    <th>Date Added</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>

                    <td>
                        <div class="action-buttons">
                            <button>Edit</button>
                            <button class="delete">Delete</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <div class="action-buttons">
                            <button>Edit</button>
                            <button class="delete">Delete</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <div class="action-buttons">
                            <button>Edit</button>
                            <button class="delete">Delete</button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>
                        <div class="action-buttons">
                            <button>Edit</button>
                            <button class="delete">Delete</button>
                        </div>
                    </td>
                </tr>
            </tbody>

            <div class="filter-container">
                <label for="year-filter">Year:</label>
                <select id="year-filter">
                    <option value="">Select Year</option>
                    <option value="">2023-2024</option>
                    <option value="">2024-2025</option>
                    <option value="">2025-2026</option>
                </select>

                <label for="semester-filter">Semester:</label>
                <select id="semester-filter">
                    <option value="">Select Semester</option>
                    <option value="">First Semistral</option>
                    <option value="">Second Semistral</option>
                </select>

                <label for="semester-filter">Year Level:</label>
                <select id="semester-filter">
                    <option value="">I Year</option>
                    <option value="">II Year</option>
                    <option value="">III Year</option>
                    <option value="">IV Year</option>
                </select>
            </div>
    </div>
    </div>
    </table>
    <div id="pagination-controls">
        <button id="prev-btn" disabled>Previous</button>
        <span id="current-page">Page 1</span>
        <button id="next-btn">Next</button>
    </div>
</body>

</html>