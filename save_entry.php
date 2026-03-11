<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

/* RECEIVE FORM DATA */

$date = $_POST['date'] ?? '';
$rig = $_POST['rig'] ?? '';

/* DATE VALIDATION (NO FUTURE DATE) */

$today = date('Y-m-d');

if($date > $today){

echo "<h3 style='color:red'>Error: Future dates are not allowed.</h3>";
echo "<br><a href='add_entry.php'>Go Back</a>";
exit;

}

/* HOURS INPUT */

$operating = $_POST['operating'] ?? 0;
$standby = $_POST['standby'] ?? 0;
$breakdown = $_POST['breakdown'] ?? 0;
$ilm = $_POST['ilm'] ?? 0;
$zero = $_POST['zero'] ?? 0;

$reason = $_POST['reason'] ?? '';
$status = $_POST['status'] ?? '';

/* CONVERT TO NUMBERS */

$operating = floatval($operating);
$standby = floatval($standby);
$breakdown = floatval($breakdown);
$ilm = floatval($ilm);
$zero = floatval($zero);

/* TOTAL HOURS CHECK */

$total_hours = $operating + $standby + $breakdown + $ilm + $zero;

if($total_hours > 24){

echo "<h3 style='color:red'>Error: Total hours cannot exceed 24 hours.</h3>";
echo "<br><a href='add_entry.php'>Go Back</a>";
exit;

}

/* DUPLICATE CHECK */

$check = $conn->query("
SELECT id
FROM rig_daily_log
WHERE rig='$rig' AND date='$date'
");

if($check->num_rows > 0){

echo "<h3 style='color:red'>Error: Entry already exists for this rig and date.</h3>";
echo "<br><a href='add_entry.php'>Go Back</a>";
exit;

}

/* INSERT DATA */

try{

$sql = "
INSERT INTO rig_daily_log
(date,rig,operating_hours,standby_hours,breakdown_hours,ilm_hours,zero_rate_hours,reason,status)
VALUES
('$date','$rig','$operating','$standby','$breakdown','$ilm','$zero','$reason','$status')
";

$conn->query($sql);

header("Location: dashboard.php");
exit;

}

catch(mysqli_sql_exception $e){

echo "<h3 style='color:red'>Database Error</h3>";

if(strpos($e->getMessage(),'hours_limit') !== false){
echo "Total hours cannot exceed 24.";
}

elseif(strpos($e->getMessage(),'unique_rig_date') !== false){
echo "Duplicate entry for this rig and date.";
}

else{
echo $e->getMessage();
}

echo "<br><br><a href='add_entry.php'>Go Back</a>";

}

?>