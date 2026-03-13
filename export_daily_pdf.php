```php
<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

/* ---------------- LOGO BASE64 ---------------- */

$logo_path = __DIR__ . "/logo.png";
$logo_base64 = "";

if(file_exists($logo_path)){
    $logo_data = base64_encode(file_get_contents($logo_path));
    $logo_base64 = "data:image/png;base64," . $logo_data;
}

/* ---------------- REPORT DATE ---------------- */

$report_date = date("d-M-Y");
$report_time = date("H:i:s");

/* ---------------- RECEIVE FILTERS ---------------- */

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

/* ---------------- RECEIVE CHART IMAGE ---------------- */

$chart_image = $_POST['chart_image'] ?? "";

/* ---------------- SUMMARY DATA ---------------- */

$summary=$conn->query("
SELECT
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate,
COUNT(DISTINCT rig) rigs
FROM rig_daily_log
$whereSQL
")->fetch_assoc();

$operating = $summary['operating'] ?? 0;
$standby = $summary['standby'] ?? 0;
$breakdown = $summary['breakdown'] ?? 0;
$ilm = $summary['ilm'] ?? 0;
$zero = $summary['zero_rate'] ?? 0;
$rigs = $summary['rigs'] ?? 0;

/* ---------------- CALCULATE DAYS ---------------- */

$days = 1;

$d=$conn->query("
SELECT 
MIN(date) start_date,
MAX(date) end_date
FROM rig_daily_log
$whereSQL
");

if($d && $row=$d->fetch_assoc()){

if($row['start_date'] && $row['end_date']){

$start = strtotime($row['start_date']);
$end   = strtotime($row['end_date']);

$days = (($end-$start)/86400)+1;

}
}

/* ---------------- FLEET EFFICIENCY ---------------- */

$total_available_hours = $rigs * 24 * $days;

$efficiency = ($total_available_hours>0)
? ($operating / $total_available_hours)*100
: 0;

$efficiency = round($efficiency,1);

/* ---------------- DAILY TABLE ---------------- */

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

/* ---------------- BUILD HTML ---------------- */

$html="

<style>

body{
font-family:Arial;
}

.header{
text-align:center;
margin-bottom:10px;
}

.reportinfo{
font-size:12px;
margin-bottom:15px;
}

.summary{
margin-top:10px;
border-collapse:collapse;
}

.summary td{
padding:6px 12px;
border:1px solid #ddd;
}

.table{
border-collapse:collapse;
width:100%;
margin-top:20px;
}

.table th{
background:#0b3d6d;
color:white;
padding:8px;
}

.table td{
padding:6px;
border:1px solid #ddd;
text-align:center;
}

.footer{
margin-top:30px;
font-size:11px;
text-align:center;
color:#777;
}

</style>


<div class='header'>

<img src='$logo_base64' height='70'>

<h2>Rig Operations Daily Report</h2>

</div>

<div class='reportinfo'>

<b>Report Date:</b> $report_date <br>
<b>Generated Time:</b> $report_time

</div>


<h3>Fleet Summary</h3>

<table class='summary'>

<tr>
<td><b>Total Rigs</b></td>
<td>$rigs</td>

<td><b>Fleet Efficiency</b></td>
<td>$efficiency %</td>
</tr>

<tr>
<td><b>Operating Hours</b></td>
<td>$operating</td>

<td><b>Standby</b></td>
<td>$standby</td>
</tr>

<tr>
<td><b>Breakdown</b></td>
<td>$breakdown</td>

<td><b>ILM</b></td>
<td>$ilm</td>
</tr>

<tr>
<td><b>Zero Rate</b></td>
<td>$zero</td>

<td></td>
<td></td>
</tr>

</table>
";

/* ---------------- ADD PIE CHART ---------------- */

if($chart_image){

$html.="

<h3>Fleet Performance Distribution</h3>

<div style='text-align:center'>
<img src='$chart_image' width='420'>
</div>

";
}

/* ---------------- DAILY TABLE ---------------- */

$html.="

<h3>Rig Daily Performance</h3>

<table class='table'>

<tr>
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

$html.="</table>

<div class='footer'>

KRISS DRILLING PVT. LTD. — Rig Monitoring System

</div>
";

/* ---------------- GENERATE PDF ---------------- */

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("rig_daily_report.pdf");

?>
```
