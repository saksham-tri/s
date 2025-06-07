<?php
$host = "localhost"; // Change if needed
$user = "root";      // Your DB username
$pass = "";          // Your DB password
$db   = "myproduct"; // Your database name

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
