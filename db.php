<?php
// db_connect.php - Simple MySQLi connection file

// Database configuration
$servername = "localhost";
$username = "root"; 
$password = ""; 
$database = "mybooklibrary";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// No closing PHP tag to prevent accidental output