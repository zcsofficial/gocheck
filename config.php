<?php
// config.php

// Database configuration
$host = "localhost";
$username = "adnan"; // Replace with your MySQL username
$password = "Adnan@66202"; // Replace with your MySQL password
$database = "gocheck"; // Replace with your database name

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>