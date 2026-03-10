<?php
include "config.php";

$date = $_POST['date'];
$rig = $_POST['rig'];
$operating = $_POST['operating'];
$standby = $_POST['standby'];
$breakdown = $_POST['breakdown'];
$ilm = $_POST['ilm'];
$zero = $_POST['zero'];
$reason = $_POST['reason'];
$status = $_POST['status'];

$sql = "INSERT INTO rig_daily_log
(date, rig, operating_hours, standby_hours, breakdown_hours, ilm_hours, zero_rate_hours, reason, status)
VALUES
('$date','$rig','$operating','$standby','$breakdown','$ilm','$zero','$reason','$status')";

$conn->query($sql);

header("Location: index.php");

?>
