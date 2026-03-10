<?php

$conn = new mysqli("localhost", "root", "Zw3cc@25001", "rig_operations");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>
