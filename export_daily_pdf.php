<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

/* RECEIVE FILTERS */

$rig = $_POST['rig'] ?? "";
$date = $_POST['date'] ?? "";
$range = $_POST['range'] ?? "";

$where=[];

if($rig!=""){
$where[]="rig='$rig'";
}

if($date!=""){
$where[]="date='$date'";
}

if($range=="today"){
$where[]="date=CURDATE()";
}

if($range=="yesterday"){
$where[]="date=CURDATE()-INTERVAL 1 DAY";
}

$whereSQL = count($where) ? "WHERE ".implode(" AND ",$where) : "";

/* RECEIVE CHART */

$chart_image = $_POST['chart_image'] ?? "";

/* QUERY */

$result=$conn->query("
SELECT
date,
rig,
operating_hours,
standby_hours,
breakdown_hours,
ilm_hours,
zero_rate_hours
FROM rig_daily_log
$whereSQL
ORDER BY date DESC
");


$html = "

<h2 style='text-align:center'>Rig Daily Performance Report</h2>

<table border='1' width='100%' cellpadding='6' cellspacing='0'>

<tr style='background:#eee'>
<th>Date</th>
<th>Rig</th>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
</tr>
";

while($row=$result->fetch_assoc()){

$html.="
<tr>
<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td>{$row['operating_hours']}</td>
<td>{$row['standby_hours']}</td>
<td>{$row['breakdown_hours']}</td>
<td>{$row['ilm_hours']}</td>
<td>{$row['zero_rate_hours']}</td>
</tr>
";

}

$html.="</table>";

if($chart_image){

$html.="

<br><br>

<h3 style='text-align:center'>Daily Performance Distribution</h3>

<div style='text-align:center'>
<img src='$chart_image' width='420'>
</div>

";

}

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("daily_rig_report.pdf");

?>