<?php
// Database configuration
$servername = "localhost";  // XAMPP default
$username = "root";         // XAMPP default
$password = "";             // XAMPP default (empty)
$database = "globmusk";     // Name of your database

// Create connection
$conn = mysqli_connect($servername, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Optional: set character set to utf8
mysqli_set_charset($conn, "utf8");
?>