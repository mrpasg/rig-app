<?php

include "config.php";

/* RECEIVE FORM DATA */

$date = $_POST['date'] ?? '';
$rig = $_POST['rig'] ?? '';

$operating = $_POST['operating'] ?? 0;
$standby = $_POST['standby'] ?? 0;
$breakdown = $_POST['breakdown'] ?? 0;
$ilm = $_POST['ilm'] ?? 0;
$zero = $_POST['zero'] ?? 0;

$reason = $_POST['reason'] ?? '';
$status = $_POST['status'] ?? '';

/* SANITIZE INPUT */

$date = $conn->real_escape_string($date);
$rig = $conn->real_escape_string($rig);
$reason = $conn->real_escape_string($reason);
$status = $conn->real_escape_string($status);

/* PREVENT NEGATIVE VALUES */

$operating = max(0, $operating);
$standby = max(0, $standby);
$breakdown = max(0, $breakdown);
$ilm = max(0, $ilm);
$zero = max(0, $zero);


/* HOURS VALIDATION */

$total_hours = $operating + $standby + $breakdown + $ilm + $zero;

if($total_hours > 24){

die("<h3 style='color:red'>Error: Total hours cannot exceed 24 hours</h3>");

}


/* DUPLICATE CHECK */

$check = $conn->query("
SELECT id
FROM rig_daily_log
WHERE rig='$rig' AND date='$date'
");

if($check->num_rows > 0){

die("<h3 style='color:red'>Error: Entry already exists for this rig and date</h3>");

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
exit;

}else{

echo "Database Error: ".$conn->error;

}

?>