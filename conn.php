<?php
$host = "localhost";
$user = "root";
$password = ""; // password
$dbname = "gaatech"; // my DB

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
