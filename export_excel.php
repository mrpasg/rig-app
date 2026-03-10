<?php
include "config.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rig_report.xls");

echo "Date\tRig\tOperating\tStandby\tBreakdown\tILM\tZero Rate\n";

$result = $conn->query("SELECT * FROM rig_daily_log");

while($row = $result->fetch_assoc()){

echo $row['date']."\t".
$row['rig']."\t".
$row['operating_hours']."\t".
$row['standby_hours']."\t".
$row['breakdown_hours']."\t".
$row['ilm_hours']."\t".
$row['zero_rate_hours']."\n";

}
?>
