<?php
// Database Configuration
$servername = "localhost";  // Change if using a remote database
$username = "root";         // Your database username
$password = "";             // Your database password (default is empty in XAMPP)
$dbname = "jasper2";        // Your database name

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Charset to UTF-8 (Recommended for special characters)
$conn->set_charset("utf8");

// echo "Database connected successfully!";
?>
