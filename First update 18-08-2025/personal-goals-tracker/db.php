<?php
$host = "localhost";
$user = "root";   // change if needed
$pass = "";       // your DB password
$dbname = "goal_tracker";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
