<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="stylesheet" href="settings.css">
    <link rel="stylesheet" href="admin_ui.css">

    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            overflow: auto;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .modal.open {
            display: block;
            opacity: 1;
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            overflow-y: auto;
            transform: scale(0.9);
            transition: transform 0.3s ease;
        }

        .modal.open .modal-content {
            transform: scale(1);
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }
    </style>

    <script>
        // Open the modal with animation
        function openModal() {
            var modal = document.getElementById("settingsModal");
            modal.style.display = "block";
            setTimeout(function() {
                modal.classList.add("open");
            }, 10);
        }

        // Close the modal with animation
        function closeModal() {
            var modal = document.getElementById("settingsModal");
            modal.classList.remove("open");
            setTimeout(function() {
                modal.style.display = "none";
            }, 300);
        }
    </script>
</head>

<body>
    <div class="containerOfSide">
        <h3>System Settings</h3>
        <div class="nav-container">
            <nav class="navigation-tabs">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="user_tables/dean_table.php?user_type=student_table">User Management</a></li>
                    <li><a href="course.php">Courses</a></li>

                </ul>
            </nav>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content-box">
        <div class="content-container">
            <h1>Update System Settings</h1>

            <button onclick="openModal()">Open Settings Form</button>

            <!-- Modal -->
            <div id="settingsModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2>Update System Settings</h2>



                    <?php
                    // Database connection
                    $conn = new mysqli('localhost', 'root', '', 'ids_database');

                    // Check connection
                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Handle form submission
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        // Escape text-based settings
                        $header_text = $conn->real_escape_string($_POST['header_text']);
                        $footer_text = $conn->real_escape_string($_POST['footer']);
                        $header_link = $conn->real_escape_string($_POST['header_link']);
                        $college_name = $conn->real_escape_string($_POST['college_name']);
                        $college_details = $conn->real_escape_string($_POST['college_details']);

                        // Function to handle image uploads and replace previous images
                        function uploadImage($fieldName, $settingName)
                        {
                            global $conn;

                            // Relative path to the uploads directory
                            $uploadDir = 'uploads/';  // Adjusted for relative path

                            // Check if a new image is uploaded
                            if (isset($_FILES[$fieldName]) && $_FILES[$fieldName]['error'] === UPLOAD_ERR_OK) {
                                // Fetch the previous image path from the database
                                $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_name = '$settingName'");
                                $previousImage = $result->fetch_assoc()['setting_value'];

                                // If there's an old image, delete it from the server
                                if ($previousImage && file_exists($previousImage)) {
                                    unlink($previousImage);  // Delete the old image
                                }

                                // Generate a unique image file name and construct the relative path
                                $newImagePath = $uploadDir . uniqid() . '_' . basename($_FILES[$fieldName]['name']);

                                // Move the uploaded file to the target directory
                                if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $newImagePath)) {
                                    // Update the database with the new image path (relative)
                                    $conn->query("UPDATE system_settings SET setting_value = '$newImagePath' WHERE setting_name = '$settingName'");
                                } else {
                                    echo "Error uploading $fieldName.";
                                }
                            }
                        }


                        // Handle image uploads for header, accreditation, and footer logos
                        uploadImage('header_logo', 'header_logo_left');
                        uploadImage('accreditation_logo', 'header_logo_right');
                        uploadImage('footer_logo', 'footer_logo');

                        // Update text settings
                        $conn->query("UPDATE system_settings SET setting_value = '$header_text' WHERE setting_name = 'header_text'");
                        $conn->query("UPDATE system_settings SET setting_value = '$footer_text' WHERE setting_name = 'footer'");
                        $conn->query("UPDATE system_settings SET setting_value = '$header_link' WHERE setting_name = 'header_link'");
                        $conn->query("UPDATE system_settings SET setting_value = '$college_name' WHERE setting_name = 'college_name'");
                        $conn->query("UPDATE system_settings SET setting_value = '$college_details' WHERE setting_name = 'college_details'");

                        echo "<p>Settings updated successfully!</p>";

                        // Display submitted data
                        echo "<h3>Submitted Data</h3>";
                        echo "<p><strong>Header Text:</strong> $header_text</p>";
                        echo "<p><strong>Footer Text:</strong> $footer_text</p>";
                        echo "<p><strong>Header Link:</strong> $header_link</p>";
                        echo "<p><strong>College Name:</strong> $college_name</p>";
                        echo "<p><strong>College Details:</strong> $college_details</p>";
                    }

                    // Fetch current settings from the database
                    $settings = [];
                    $result = $conn->query("SELECT setting_name, setting_value FROM system_settings");
                    while ($row = $result->fetch_assoc()) {
                        $settings[$row['setting_name']] = $row['setting_value'];
                    }

                    // Assign values from fetched settings
                    $header_text = $settings['header_text'] ?? '';
                    $footer_text = $settings['footer'] ?? '';
                    $header_link = $settings['header_link'] ?? '';
                    $college_name = $settings['college_name'] ?? '';
                    $college_details = $settings['college_details'] ?? '';
                    $header_logo_left = $settings['header_logo_left'] ?? null;
                    $header_logo_right = $settings['header_logo_right'] ?? null;
                    $footer_logo = $settings['footer_logo'] ?? null;
                    ?>

                    <!-- Form for Settings -->
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Header Section -->
                        <div class="header-section">
                            <h2>Header Section</h2>
                            <div class="logo-container">
                                <div class="logo-left">
                                    <label for="header_logo">Upload Header Left Logo:</label>
                                    <input type="file" name="header_logo" id="header_logo">
                                    <div class="image-preview">
                                        <?php if ($header_logo_left) echo '<img src="' . $header_logo_left . '" alt="Header Left Logo">'; ?>
                                    </div>
                                </div>

                                <div class="logo-right">
                                    <label for="accreditation_logo">Upload Accreditation Logo:</label>
                                    <input type="file" name="accreditation_logo" id="accreditation_logo">
                                    <div class="image-preview">
                                        <?php if ($header_logo_right) echo '<img src="' . $header_logo_right . '" alt="Accreditation Logo">'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="header-info">
                                <div class="header-text">
                                    <label for="header_text">Header Text:</label>
                                    <input type="text" name="header_text" value="<?= $header_text ?>" required>
                                </div>
                                <div class="header-link">
                                    <label for="header_link">Header Link:</label>
                                    <input type="text" name="header_link" value="<?= $header_link ?>" required>
                                </div>
                            </div>
                        </div>


                        <!-- College Info Section -->
                        <div class="college-section">
                            <h2>College Info Section</h2>
                            <div class="college-info">
                                <label for="college_name">College Name:</label>
                                <input type="text" name="college_name" value="<?= $college_name ?>" required>

                                <label for="college_details">College Details:</label>
                                <textarea name="college_details" rows="5" required><?= $college_details ?></textarea>
                            </div>
                        </div>

                        <!-- Footer Section -->
                        <div class="footer-section">
                            <h2>Footer Section</h2>
                            <div class="footer-logo">
                                <label for="footer_logo">Upload Footer Logo:</label>
                                <input type="file" name="footer_logo" id="footer_logo">
                                <div class="image-preview">
                                    <?php if ($footer_logo) echo '<img src="' . $footer_logo . '" alt="Footer Logo">'; ?>
                                </div>
                            </div>
                            <div class="footer-text">
                                <label for="footer">Footer Text:</label>
                                <input type="text" name="footer" value="<?= $footer_text ?>" required>
                            </div>
                        </div>

                        <button type="submit">Submit Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>