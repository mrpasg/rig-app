<?php
include "auth.php";
include "config.php";

$id = $_POST['id'];

$date = $_POST['date'];
$rig = $_POST['rig'];

$operating = $_POST['operating'];
$standby = $_POST['standby'];
$breakdown = $_POST['breakdown'];
$ilm = $_POST['ilm'];
$zero = $_POST['zero'];
$reason = $_POST['reason'];

$conn->query("
UPDATE rig_daily_log
SET
date='$date',
rig='$rig',
operating_hours='$operating',
standby_hours='$standby',
breakdown_hours='$breakdown',
ilm_hours='$ilm',
zero_rate_hours='$zero',
reason='$reason'
WHERE id='$id'
");

header("Location: report_daily.php");
exit;
?>
