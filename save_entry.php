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


/* HOURS VALIDATION */

$total_hours = $operating + $standby + $breakdown + $ilm + $zero;

if($total_hours > 24){

echo "<h3 style='color:red'>Error: Total hours cannot exceed 24 hours</h3>";
exit;

}


/* DUPLICATE CHECK */

$check = $conn->query("
SELECT id
FROM rig_daily_log
WHERE rig='$rig' AND date='$date'
");

if($check->num_rows > 0){

echo "<h3 style='color:red'>Error: Entry already exists for this rig and date</h3>";
exit;

}


/* INSERT DATA */

$sql = "
INSERT INTO rig_daily_log
(date,rig,operating_hours,standby_hours,breakdown_hours,ilm_hours,zero_rate_hours,reason,status)
VALUES
('$date','$rig','$operating','$standby','$breakdown','$ilm','$zero','$reason','$status')
";

if($conn->query($sql)){

header("Location: dashboard.php");

}else{

echo "Error: ".$conn->error;

}

?>