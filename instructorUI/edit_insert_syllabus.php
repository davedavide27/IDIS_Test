<?php
session_start();

// Check if the user is logged in as an instructor
if (!isset($_SESSION['user_ID']) || $_SESSION['user_type'] != 'instructor') {
    header("Location: ../login.php");
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
    die("Connection failed: " . $conn->connect_error);
}

// Initialize syllabus data
$subject_code = "";
$subject_name = "";
$course_units = "";
$course_description = "";
$prerequisites_corequisites = "";
$contact_hours = "";
$performance_tasks = "";

// Fetch subject code and name from POST data
if (isset($_POST['syllabus_subject_code']) && isset($_POST['syllabus_subject_name'])) {
    $subject_code = $_POST['syllabus_subject_code'];
    $subject_name = $_POST['syllabus_subject_name'];
} elseif (isset($_POST['subject_code']) && isset($_POST['subject_name'])) {
    $subject_code = $_POST['subject_code'];
    $subject_name = $_POST['subject_name'];
}

// Fetch existing syllabus data if it exists
if (!empty($subject_code)) {
    $sql = "SELECT * FROM syllabus WHERE subject_code = ? AND instructor_ID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $subject_code, $instructor_ID);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $course_units = htmlspecialchars($row['course_units']);
                $course_description = htmlspecialchars($row['course_description']);
                $prerequisites_corequisites = htmlspecialchars($row['prerequisites_corequisites']);
                $contact_hours = htmlspecialchars($row['contact_hours']);
                $performance_tasks = htmlspecialchars($row['performance_tasks']);
            }
            $result->free();
        }
        $stmt->close();
    }
}

// Handle the form submission for saving or updating the syllabus
if (isset($_POST['save_syllabus'])) {
    $conn->begin_transaction(); // Begin transaction for atomicity

    try {
        // Check if syllabus exists
        $sqlCheckSyllabus = "SELECT * FROM syllabus WHERE subject_code = ? AND instructor_ID = ?";
        $stmtCheckSyllabus = $conn->prepare($sqlCheckSyllabus);
        $stmtCheckSyllabus->bind_param("si", $subject_code, $instructor_ID);
        $stmtCheckSyllabus->execute();
        $resultSyllabus = $stmtCheckSyllabus->get_result();

        if ($resultSyllabus->num_rows > 0) {
            // Update existing syllabus
            $sqlUpdateSyllabus = "
                UPDATE syllabus 
                SET course_units = ?, course_description = ?, prerequisites_corequisites = ?, contact_hours = ?, performance_tasks = ? 
                WHERE subject_code = ? AND instructor_ID = ?";
            $stmtUpdateSyllabus = $conn->prepare($sqlUpdateSyllabus);
            $stmtUpdateSyllabus->bind_param(
                "ssssssi",
                $_POST['course_units'],
                $_POST['course_description'],
                $_POST['prerequisites_corequisites'],
                $_POST['contact_hours'],
                $_POST['performance_tasks'],
                $subject_code,
                $instructor_ID
            );
            $stmtUpdateSyllabus->execute();
        } else {
            // Insert new syllabus
            $sqlInsertSyllabus = "
                INSERT INTO syllabus (subject_code, instructor_ID, course_units, course_description, prerequisites_corequisites, contact_hours, performance_tasks)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtInsertSyllabus = $conn->prepare($sqlInsertSyllabus);
            $stmtInsertSyllabus->bind_param(
                "sisssss",
                $subject_code,
                $instructor_ID,
                $_POST['course_units'],
                $_POST['course_description'],
                $_POST['prerequisites_corequisites'],
                $_POST['contact_hours'],
                $_POST['performance_tasks']
            );
            $stmtInsertSyllabus->execute();
        }

        // Clear existing context data
        $stmtClearContext = $conn->prepare("DELETE FROM context WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearContext->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearContext->execute();

        // Insert or update context data
        $sections = ['prelim', 'midterm', 'semifinal', 'final'];
        $stmtInsertContext = $conn->prepare("
            INSERT INTO context (instructor_id, subject_code, section, hours, ilo, topics, institutional_values, teaching_activities, resources, assessment_tasks, course_map)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmtInsertContext->bind_param(
            "issssssssss",
            $instructor_ID,
            $subject_code,
            $section,
            $hours,
            $ilo,
            $topics,
            $institutional_values,
            $teaching_activities,
            $resources,
            $assessment_tasks,
            $course_map
        );

        foreach ($sections as $section) {
            $section = strtoupper($section); // e.g., PRELIM, MIDTERM, etc.

            if (isset($_POST[$section . '_hours']) && is_array($_POST[$section . '_hours'])) {
                foreach ($_POST[$section . '_hours'] as $index => $hour) {
                    $hours = !empty($_POST[$section . '_hours'][$index]) ? $_POST[$section . '_hours'][$index] : null;
                    $ilo = !empty($_POST[$section . '_ilo'][$index]) ? $_POST[$section . '_ilo'][$index] : null;
                    $topics = !empty($_POST[$section . '_topics'][$index]) ? $_POST[$section . '_topics'][$index] : null;
                    $institutional_values = !empty($_POST[$section . '_institutional_values'][$index]) ? $_POST[$section . '_institutional_values'][$index] : null;
                    $teaching_activities = !empty($_POST[$section . '_teaching_activities'][$index]) ? $_POST[$section . '_teaching_activities'][$index] : null;
                    $resources = !empty($_POST[$section . '_resources'][$index]) ? $_POST[$section . '_resources'][$index] : null;
                    $assessment_tasks = !empty($_POST[$section . '_assessment'][$index]) ? $_POST[$section . '_assessment'][$index] : null;
                    $course_map = !empty($_POST[$section . '_course_map'][$index]) ? $_POST[$section . '_course_map'][$index] : null;

                    if ($hours || $ilo || $topics || $institutional_values || $teaching_activities || $resources || $assessment_tasks || $course_map) {
                        $stmtInsertContext->execute();
                    }
                }
            }
        }

        // Clear existing PILO-GILO mappings for the subject
        // Clear existing PILO-GILO mappings for the subject
        $stmtClearPiloGiloMappings = $conn->prepare("DELETE FROM pilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearPiloGiloMappings->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearPiloGiloMappings->execute();

        // Insert new PILO-GILO mappings
        if (isset($_POST['pilo']) && is_array($_POST['pilo']) && isset($_POST['gilo']) && is_array($_POST['gilo'])) {
            $piloValues = $_POST['pilo'];
            $giloValues = $_POST['gilo'];
            $currentPiloIndex = 0;

            foreach ($piloValues as $piloIndex => $piloValue) {
                // Fetch the GILO values corresponding to the current PILO
                while (isset($giloValues[$currentPiloIndex])) {
                    $giloValue = $giloValues[$currentPiloIndex];

                    if (!empty($piloValue) && !empty($giloValue)) {
                        // Insert PILO and corresponding GILO
                        $stmtPiloGilo = $conn->prepare("INSERT INTO pilo_gilo_map (subject_code, instructor_ID, pilo, gilo) VALUES (?, ?, ?, ?)");
                        $stmtPiloGilo->bind_param("siss", $subject_code, $instructor_ID, $piloValue, $giloValue);
                        if (!$stmtPiloGilo->execute()) {
                            throw new Exception("Failed to insert PILO-GILO mapping: " . $stmtPiloGilo->error);
                        }
                    }
                    $currentPiloIndex++;
                }
            }
        }

        // Clear existing CILO-GILO mappings for the subject
        $stmtClearCiloGiloMappings = $conn->prepare("DELETE FROM cilo_gilo_map WHERE subject_code = ? AND instructor_ID = ?");
        $stmtClearCiloGiloMappings->bind_param("si", $subject_code, $instructor_ID);
        $stmtClearCiloGiloMappings->execute();

        // Insert new CILO-GILO mappings
        if (isset($_POST['cilo_description']) && isset($_POST['gilo1']) && isset($_POST['gilo2'])) {
            $cilo_descriptions = $_POST['cilo_description'];
            $gilo1_values = $_POST['gilo1'];
            $gilo2_values = $_POST['gilo2'];

            for ($i = 0; $i < count($cilo_descriptions); $i++) {
                if (!empty($cilo_descriptions[$i])) {
                    $cilo_description = $cilo_descriptions[$i];
                    $gilo1 = $gilo1_values[$i];
                    $gilo2 = $gilo2_values[$i];

                    $stmtCiloGilo = $conn->prepare("INSERT INTO cilo_gilo_map (subject_code, instructor_ID, cilo_description, gilo1, gilo2) VALUES (?, ?, ?, ?, ?)");
                    $stmtCiloGilo->bind_param("sisss", $subject_code, $instructor_ID, $cilo_description, $gilo1, $gilo2);
                    if (!$stmtCiloGilo->execute()) {
                        throw new Exception("Failed to insert CILO-GILO mapping: " . $stmtCiloGilo->error);
                    }
                }
            }
        }

        // Commit the transaction
        if ($conn->commit()) {
            echo "<script>alert('Syllabus submitted successfully!'); window.location.href='index.php';</script>";
        } else {
            throw new Exception("Transaction commit failed.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.location.href='index.php';</script>";
    }
}

// Close the connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Syllabus</title>
    <link rel="stylesheet" href="../syllabus.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        // Auto resize textarea
        document.addEventListener('DOMContentLoaded', () => {
            const textareas = document.querySelectorAll('.autoResizeTextarea');

            const resizeTextarea = (textarea) => {
                textarea.style.height = 'auto';
                textarea.style.height = `${textarea.scrollHeight}px`;
            };
            textareas.forEach(textarea => {
                textarea.addEventListener('input', () => resizeTextarea(textarea));
                resizeTextarea(textarea);
            });
        });
    </script>

    <script>
        var piloValue = <?php echo $pilo_json; ?>;
        var giloArray = <?php echo $gilo_json; ?>;

        // Call the populatePiloGiloRows function after the page has loaded
        window.onload = function() {
            if (piloValue !== "" && giloArray.length > 0) {
                populatePiloGiloRows(piloValue, giloArray);
            }
        };
    </script>

    <?php
    // Fetch subject code and name from POST data
    $subject_code = '';
    $subject_name = '';
    if (isset($_POST['syllabus_subject_code']) && isset($_POST['syllabus_subject_name'])) {
        $subject_code = $_POST['syllabus_subject_code'];
        $subject_name = $_POST['syllabus_subject_name'];
    } elseif (isset($_POST['subject_code']) && isset($_POST['subject_name'])) {
        $subject_code = $_POST['subject_code'];
        $subject_name = $_POST['subject_name'];
    }
    ?>

</head>

<body>
    <?php
    // Check if success message exists
    if (isset($_SESSION['success_message'])) {
        $message = $_SESSION['success_message'];
        // Unset the session message after displaying it once
        unset($_SESSION['success_message']);
    ?>
        <script>
            // Show SweetAlert and redirect after clicking OK
            window.onload = function() {
                Swal.fire({
                    title: 'localhost says',
                    text: '<?php echo $message; ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = 'index.php'; // Redirect to index.php
                });
            };
        </script>
    <?php
    }
    ?>
    <!-- Header Section -->
    <div class="divHeader">
        <div class="headContents">
            <img src="../smcclogo.jfif" alt="SMCC Logo" class="logo">
        </div>
        <div class="headContents" style="text-align: center; font-size: 21px;">
            <div style="font-size: x-large; color: rgba(28, 6, 80, 0.877); font-family: Calambria;">Saint Michael College of Caraga</div>
            <div style="font-size: medium;">
                <div style="font-family: Bookman Old Style;">Brgy. 4, Nasipit, Agusan del Norte, Philippines</div>
                <div style="font-family: Calambria;">&emsp;Tel. Nos. +63 085 343-3251 / +63 085 283-3113 Fax No. +63 085 808-0892 &emsp;</div>
                <div style="font-family: Bookman Old Style;"><a href="https://www.smccnasipit.edu.ph/">www.smccnasipit.edu.ph</a></div>
            </div>
        </div>
        <div class="headContents">
            <img src="ISO&PAB.png" alt="Accreditation Logos" class="logo">
        </div>
    </div>


    <!-- Main Content Section -->
    <div class="container">
        <h2>Edit Syllabus</h2>
        <!-- Display messages if any -->
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<p style="color: green;">' . $_SESSION['success_message'] . '</p>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<p style="color: red;">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        if (isset($_SESSION['info_message'])) {
            echo '<p style="color: blue;">' . $_SESSION['info_message'] . '</p>';
            unset($_SESSION['info_message']);
        }
        ?>

        <!-- Check if success message exists in the session -->
        <?php if (isset($_SESSION['success_message'])) : ?>
            <script>
                showSuccessAndRedirect(); // Show alert and redirect
            </script>
            <?php unset($_SESSION['success_message']); // Clear success message 
            ?>
        <?php endif; ?>

        <form action="" method="POST">
            <input type="hidden" name="subject_code" value="<?php echo htmlspecialchars($subject_code); ?>">
            <input type="hidden" name="subject_name" value="<?php echo htmlspecialchars($subject_name); ?>">

            <!-- Read-Only Vision, Mission Section -->
            <h3>I. School's Vision, Mission, Goal, Objectives, Michaelinian Identity</h3>

            <h4>Vision</h4>
            <p>Saint Michael College of Caraga envisions to a university by 2035 and upholds spiritual formation and excellence in teaching, service, and, research.</p>

            <h4>Mission</h4>
            <p>As such, SMCC commits itself:</p>
            <ul>
                <li>SMCC shall provide spiritual formation and learning culture that will ensure the students with an excellent and rewarding learning experience that transforms lives, abound spirituality, develop skills, and prepare future leaders.</li>
                <li>SMCC shall engage in dynamic, innovative, and interdisciplinary research to advance and achieve institutional initiatives.</li>
                <li>SMCC shall commit to serve the diverse and local communities by fostering innovations through service-learning that enhances reciprocal community partnerships for spiritual and social development.</li>
            </ul>

            <h4>Goal</h4>
            <p>Uphold Culture of Excellence in the Areas of Spiritual Formation, Instruction, Research, and Extension, thus producing Graduates that are Globally Competent, Spiritually Embodied, and Socially Responsible.</p>

            <h4>General Objectives</h4>
            <ul>
                <li>To integrate positive and evangelical values in all areas and design Christian formation programs that are effective and responsive to the psycho-spiritual needs of the learners, parents, and personnel.</li>
                <li>To enhance consistently the curriculum and cultivate teachers’ effectiveness to promote quality instruction.</li>
                <li>To continue upgrading facilities and services for the satisfaction of the clientele.</li>
                <li>To intensify the curriculum-based and institutional research that is dynamic, innovative, and interdisciplinary.</li>
            </ul>

            <h4>Michaelinian Identity</h4>
            <p>Secured by Saint Michael the Archangel's Sword of Bravery and Victory, the Michaelinians of today and tomorrow are:</p>
            <ul>
                <li><b>S</b> - ocially Responsible</li>
                <li><b>M</b> - issionaries of Christian Values</li>
                <li><b>C</b> - ommitted Individuals</li>
                <li><b>C</b> - ompetent in their Chosen Fields of Endeavor</li>
            </ul>

            <h3>II. Graduate Intended Learning Outcomes (GILO)</h3>
            <ul>
                <li>Demonstrate social responsibilities.</li>
                <li>Become missionaries of Christian values.</li>
                <li>Uphold unconditional commitment to life and to action.</li>
                <li>Exude competence in one’s chosen fields of endeavor.</li>
            </ul>

            <h4>PROGRAM MAPPING</h4>
            <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

            <table id="piloGiloTable">
                <tr>
                    <th>Program Intended Learning Outcomes (PILOs) <br><br> After completion of the program, the student must be able to:</th>
                    <th>Graduate Intended Learning Outcomes (GILOs)</th>
                    <th><button type="button" class="button add-row-button" onclick="addPiloGiloRow()">+</button></th>
                </tr>
                <!-- Template row -->
                <tr class="piloGiloRow">
                    <td>
                        <textarea name="pilo[]" placeholder="Enter PILO"></textarea>
                    </td>
                    <td>
                        <select name="gilo[]">
                            <option value="I">I</option>
                            <option value="D">D</option>
                            <option value="P">P</option>
                        </select>
                    </td>
                    <td><button type="button" class="button remove-row-button" onclick="removePiloGiloRow(this)" disabled>-</button></td>
                </tr>
            </table>

            <script>
                // Function to populate existing data into the table
                function populatePiloGiloRows(piloValue, giloArray) {
                    var firstRow = document.querySelector('.piloGiloRow');
                    var table = document.getElementById('piloGiloTable');

                    // Set the first row's PILO and first GILO values
                    firstRow.querySelector('textarea[name="pilo[]"]').value = piloValue;
                    firstRow.querySelector('select[name="gilo[]"]').value = giloArray[0];

                    // If there are more GILO values, create additional rows without adding the PILO field
                    for (var i = 1; i < giloArray.length; i++) {
                        var newRow = firstRow.cloneNode(true);

                        // Remove the PILO field for subsequent rows (leave blank)
                        newRow.querySelector('textarea[name="pilo[]"]').remove();

                        // Set the GILO field for the new row
                        newRow.querySelector('select[name="gilo[]"]').value = giloArray[i];

                        // Enable the remove button for the new row
                        newRow.querySelector('button.remove-row-button').disabled = false;

                        // Append the new row to the table
                        table.appendChild(newRow);
                    }
                }

                // Add new PILO-GILO row
                function addPiloGiloRow() {
                    var firstRow = document.querySelector('.piloGiloRow');
                    var newRow = firstRow.cloneNode(true);

                    // Remove the PILO field for new rows
                    newRow.querySelector('textarea[name="pilo[]"]').remove();

                    // Reset GILO field (select) for the new row
                    newRow.querySelector('select[name="gilo[]"]').selectedIndex = 0;

                    // Enable the remove button for the new row
                    newRow.querySelector('button.remove-row-button').disabled = false;

                    // Set up the remove button with the correct function
                    newRow.querySelector('button.remove-row-button').setAttribute('onclick', 'removePiloGiloRow(this)');

                    // Append the new row to the table
                    document.getElementById('piloGiloTable').appendChild(newRow);
                }

                // Remove a PILO-GILO row, ensuring the first row cannot be deleted
                function removePiloGiloRow(button) {
                    var table = document.getElementById('piloGiloTable');
                    var rows = table.getElementsByClassName('piloGiloRow');

                    // Check if the row is the first one
                    if (button.closest('.piloGiloRow') === rows[0]) {
                        alert('The first row cannot be deleted.');
                    } else {
                        // Remove the row if it is not the first one
                        button.closest('.piloGiloRow').remove();
                    }
                }

                // Fetch the PILO and GILO data when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    // Use PHP to pass the subject code from server-side to client-side
                    var subjectCode = '<?php echo $subject_code; ?>'; // Use dynamic subject code from PHP

                    if (subjectCode) {
                        fetch('fetch_cilo_pilo.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                if (data.pilo && data.gilo && data.gilo.length > 0) {
                                    // Display only the first PILO and multiple GILOs
                                    populatePiloGiloRows(data.pilo[0], data.gilo); // Display only the first PILO
                                } else {
                                    console.warn('No data found for the specified subject code.');
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });
            </script>


            <!-- Course Information Section -->
            <h4>Course Information</h4>
            <ul>
                <li>Course Code: <input type="text" name="course_code" value="<?php echo htmlspecialchars($subject_code); ?>" readonly></li>
                <li>Course Name: <input type="text" name="course" value="<?php echo htmlspecialchars($subject_name); ?>" readonly></li>
                <li>Course Units: <input type="text" name="course_units" value="<?php echo htmlspecialchars($course_units); ?>" placeholder="Enter Course Units"></li>
                <li>Course Description: <textarea name="course_description" class="autoResizeTextarea" placeholder="Enter Course Description"><?php echo htmlspecialchars($course_description); ?></textarea></li>
                <li>Prerequisites: <input type="text" name="prerequisites_corequisites" value="<?php echo htmlspecialchars($prerequisites_corequisites); ?>" placeholder="Enter Prerequisites"></li>
                <li>Contact Hours: <input type="text" name="contact_hours" value="<?php echo htmlspecialchars($contact_hours); ?>" placeholder="Enter Contact Hours"></li>
            </ul>

            <!-- Course Intended Learning Outcomes Section -->
            <h3>Course Intended Learning Outcomes (CILO)</h3>
            <h4>COURSE MAPPING</h4>
            <p><b>I</b> – Introduce <b>D</b> – Demonstrate skills with Supervision <b>P</b> – Practice skills without Supervision</p>

            <table id="ciloGiloTable">
                <tr>
                    <th>Course Intended Learning Outcomes (CILOs)<br><br>
                        After completion of the program, the student must be able to:</th>
                    <th>GILOs</th>
                    <th>GILOs</th>
                    <th><button type="button" class="button add-row-button" onclick="addCiloGiloRow()">+</button></th>
                </tr>
                <tr class="ciloGiloRow">
                    <td><textarea name="cilo_description[]" class="autoResizeTextarea"></textarea></td>
                    <td>
                        <select name="gilo1[]">
                            <option value="I">I</option>
                            <option value="D">D</option>
                            <option value="P">P</option>
                        </select>
                    </td>
                    <td>
                        <select name="gilo2[]">
                            <option value="I">I</option>
                            <option value="D">D</option>
                            <option value="P">P</option>
                        </select>
                    </td>
                    <td><button type="button" class="button remove-row-button" onclick="removeCiloGiloRow(this)" disabled>-</button></td>
                </tr>
            </table>
            <script>
                // Function to populate existing data into the table
                function populateCiloGiloRows(ciloData) {
                    const table = document.getElementById('ciloGiloTable');
                    const firstRow = document.querySelector('.ciloGiloRow');

                    // Set the first row with the first CILO and GILO values
                    firstRow.querySelector('textarea[name="cilo_description[]"]').value = ciloData.cilo[0];
                    firstRow.querySelector('select[name="gilo1[]"]').value = ciloData.cilo_gilo1[0];
                    firstRow.querySelector('select[name="gilo2[]"]').value = ciloData.cilo_gilo2[0];

                    // Add additional rows for the remaining CILOs and GILOs
                    for (let i = 1; i < ciloData.cilo.length; i++) {
                        const newRow = firstRow.cloneNode(true);

                        // Set the CILO and GILO values for each new row
                        newRow.querySelector('textarea[name="cilo_description[]"]').value = ciloData.cilo[i];
                        newRow.querySelector('select[name="gilo1[]"]').value = ciloData.cilo_gilo1[i];
                        newRow.querySelector('select[name="gilo2[]"]').value = ciloData.cilo_gilo2[i];

                        // Enable the remove button for additional rows
                        newRow.querySelector('button.remove-row-button').disabled = false;

                        // Append the new row to the table
                        table.appendChild(newRow);
                    }
                }

                // Add new row for CILO-GILO mapping
                function addCiloGiloRow() {
                    const lastRow = document.querySelector('.ciloGiloRow');
                    const newRow = lastRow.cloneNode(true);

                    // Clear the textarea and reset the select inputs
                    newRow.querySelector('textarea[name="cilo_description[]"]').value = '';
                    newRow.querySelector('select[name="gilo1[]"]').selectedIndex = 0;
                    newRow.querySelector('select[name="gilo2[]"]').selectedIndex = 0;

                    // Enable the remove button for new rows
                    newRow.querySelector('button.remove-row-button').disabled = false;

                    // Append the new row to the table
                    document.getElementById('ciloGiloTable').appendChild(newRow);
                }

                // Remove a CILO-GILO row, ensuring at least 1 row remains
                function removeCiloGiloRow(button) {
                    const table = document.getElementById('ciloGiloTable');
                    const rows = table.getElementsByClassName('ciloGiloRow');

                    if (rows.length > 1) {
                        button.closest('.ciloGiloRow').remove();
                    } else {
                        alert('At least one row must remain.');
                    }
                }

                // Fetch the CILO and GILO data when the page loads
                document.addEventListener('DOMContentLoaded', function() {
                    // Use PHP to pass the subject code from server-side to client-side
                    const subjectCode = '<?php echo $subject_code; ?>'; // Use dynamic subject code from PHP

                    if (subjectCode) {
                        fetch('fetch_cilo_pilo.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                if (data.cilo && data.cilo.length > 0) {
                                    populateCiloGiloRows(data); // Populate with CILO-GILO data
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });
            </script>


            <h4>Context</h4>
            <table id="contextTable">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Hours</th>
                        <th>Intended Learning Outcomes (ILO) / Competency(ies)</th>
                        <th>Topics</th>
                        <th>Institutional Values</th>
                        <th>Teaching and Learning Activities</th>
                        <th>Resources/References</th>
                        <th>Assessment Tasks</th>
                        <th>Course Map</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="prelimSection" class="section-prelim">
                    <tr>
                        <td>PRELIM</td>
                        <td><textarea name="PRELIM_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="PRELIM_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('prelimSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="midtermSection" class="section-midterm">
                    <tr>
                        <td>MIDTERM</td>
                        <td><textarea name="MIDTERM_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="MIDTERM_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('midtermSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="semifinalSection" class="section-semifinal">
                    <tr>
                        <td>SEMI FINAL</td>
                        <td><textarea name="SEMIFINAL_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="SEMIFINAL_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('semifinalSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
                <tbody id="finalSection" class="section-final">
                    <tr>
                        <td>FINAL</td>
                        <td><textarea name="FINAL_hours[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_ilo[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_topics[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_institutional_values[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_teaching_activities[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_resources[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_assessment[]" class="autoResizeTextarea"></textarea></td>
                        <td><textarea name="FINAL_course_map[]" class="autoResizeTextarea"></textarea></td>
                        <td>
                            <button type="button" class="button add-row-button" onclick="addRow('finalSection')">+</button>
                            <button type="button" class="button remove-row-button" onclick="removeRow(this)">-</button>
                        </td>
                    </tr>
                </tbody>
            </table>


            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Fetch the subject code from the backend (passed from PHP to JavaScript)
                    const subjectCode = '<?php echo $subject_code; ?>'; // Dynamic PHP variable

                    // Fetch context data for the given subject code
                    if (subjectCode) {
                        fetch('fetch_context.php?subject_code=' + encodeURIComponent(subjectCode))
                            .then(response => response.json())
                            .then(data => {
                                if (Array.isArray(data) && data.length > 0) {
                                    populateContextTable(data); // Populate the table with the fetched context data
                                } else {
                                    console.warn('No context data found for subject code:', subjectCode);
                                }
                            })
                            .catch(error => {
                                console.error('Error fetching context data:', error);
                            });
                    } else {
                        console.warn('No subject code provided.');
                    }
                });

                // Function to populate the context table with the fetched data
                function populateContextTable(contextData) {
                    // Iterate over the fetched data
                    contextData.forEach(row => {
                        // Check if the row contains any valid data
                        if (row.hours || row.ilo || row.topics || row.institutional_values || row.teaching_activities || row.resources || row.assessment_tasks || row.course_map) {
                            // Determine the correct section based on the `section` field from the data (e.g., 'PRELIM', 'MIDTERM')
                            const sectionId = row.section.toLowerCase() + 'Section'; // e.g., 'prelimSection'
                            const sectionBody = document.getElementById(sectionId);

                            if (sectionBody) {
                                // Clone the first row to use as a template for each additional row
                                const newRow = sectionBody.querySelector('tr').cloneNode(true);

                                // Populate the cloned row with the data from the current row
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_hours[]"]').value = row.hours || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_ilo[]"]').value = row.ilo || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_topics[]"]').value = row.topics || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_institutional_values[]"]').value = row.institutional_values || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_teaching_activities[]"]').value = row.teaching_activities || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_resources[]"]').value = row.resources || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_assessment[]"]').value = row.assessment_tasks || '';
                                newRow.querySelector('textarea[name="' + row.section.toUpperCase() + '_course_map[]"]').value = row.course_map || '';

                                // Append the populated row to the section body
                                sectionBody.appendChild(newRow);
                            }
                        }
                    });
                }

                // Function to add a new row in the corresponding section
                function addRow(sectionId) {
                    const tableBody = document.getElementById(sectionId);
                    const lastRow = tableBody.querySelector('tr:last-child'); // Get the last row in the section
                    const newRow = lastRow.cloneNode(true); // Clone the last row

                    // Clear the values in the cloned row's textareas
                    const textareas = newRow.getElementsByTagName('textarea');
                    for (let i = 0; i < textareas.length; i++) {
                        textareas[i].value = ''; // Clear the textarea value
                    }

                    // Append the new row to the section
                    tableBody.appendChild(newRow);
                }

                // Function to remove a row from the corresponding section
                function removeRow(button) {
                    const tableBody = button.closest('tbody'); // Get the section (tbody)
                    if (tableBody.rows.length > 1) {
                        button.closest('tr').remove(); // Remove the row if there is more than one row
                    } else {
                        alert("At least one row must remain.");
                    }
                }
            </script>


            <!-- Performance Tasks Section -->
            <h4>Performance Tasks</h4>
            <textarea name="performance_tasks" class="autoResizeTextarea" placeholder="Enter Performance Tasks"><?php echo htmlspecialchars($performance_tasks); ?></textarea>


            <!-- Submit Button -->
            <button class="button" type="submit" name="save_syllabus">Submit Syllabus</button>

            <!-- Back Button -->
            <button class="back-button" type="button" onclick="window.location.href='index.php';">Back</button>
        </form>
    </div>

</body>
<script>
    function checkFormValues() {
        const subjectCode = document.getElementById('syllabus_subject_code').value;
        const subjectName = document.getElementById('syllabus_subject_name').value;

        // Log the values to verify if they are correct
        console.log("Form Submitted - Subject Code:", subjectCode);
        console.log("Form Submitted - Subject Name:", subjectName);

        // Returning true to allow form submission to continue
        return true;
    }
</script>

</html>