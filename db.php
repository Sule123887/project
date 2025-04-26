<?php
$conn = new mysqli("localhost", "root", "", "webproject");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
