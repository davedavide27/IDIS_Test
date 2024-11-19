<?php
// Database connection (adjust as needed for your environment)
$conn = new mysqli('localhost', 'root', '', 'ids_database');

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch settings from system_settings table
$result = $conn->query("SELECT setting_name, setting_value FROM system_settings");

$settings = [];
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_name']] = $row['setting_value'];
}

// Set default values if settings are not found in the database
$college_name = isset($settings['college_name']) ? $settings['college_name'] : 'Default College Name';
$college_details = isset($settings['college_details']) ? $settings['college_details'] : 'Default College Details';
$contact_details = isset($settings['footer']) ? $settings['footer'] : 'Default Footer Text';
$header_link = isset($settings['header_link']) ? $settings['header_link'] : 'https://www.smcc.com';

// Use the file paths from the database (relative paths like 'uploads/...')
$header_logo_left = isset($settings['header_logo_left']) ? $settings['header_logo_left'] : 'uploads/default_logo_left.png';
$header_logo_right = isset($settings['header_logo_right']) ? $settings['header_logo_right'] : 'uploads/default_logo_right.png';
$footer_logo = isset($settings['footer_logo']) ? $settings['footer_logo'] : 'uploads/default_footer_logo.png';

// Set text values
$header_text = isset($settings['header_text']) ? $settings['header_text'] : 'Default Header Text';

// Close the database connection
$conn->close();
