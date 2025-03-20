<?php
$servername = "sql113.infinityfree.com";
$username = "if0_38471709";
$password = "9kBSwjmaqEy3";
$dbname = "if0_38471709_beyond_session";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
