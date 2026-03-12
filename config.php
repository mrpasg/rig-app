<?php

$conn = new mysqli("localhost", "root", "Padanisa@6987!", "rig_operations");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
