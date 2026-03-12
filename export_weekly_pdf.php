<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

/* LOGO */

$logo = __DIR__."/logo.png";
$logo_base64="";

if(file_exists($logo)){
$logo_base64="data:image/png;base64,".base64_encode(file_get_contents($logo));
}

/* REPORT INFO */

$report_date=date("d-M-Y");
$report_time=date("H:i:s");

/* FILTER */

$monday=$_POST['monday'];
$sunday=$_POST['sunday'];
$rig=$_POST['rig'];

$where="WHERE date BETWEEN '$monday' AND '$sunday'";

if($rig!=""){
$where.=" AND rig='$rig'";
}

/* SUMMARY */

$summary=$conn->query("
SELECT
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate,
COUNT(DISTINCT rig) rigs
FROM rig_daily_log
$where
")->fetch_assoc();

$operating=$summary['operating']??0;
$standby=$summary['standby']??0;
$breakdown=$summary['breakdown']??0;
$ilm=$summary['ilm']??0;
$zero=$summary['zero_rate']??0;
$rigs=$summary['rigs']??0;

$efficiency=($rigs>0)?($operating/($rigs*24*7))*100:0;
$efficiency=round($efficiency,1);

/* TABLE DATA */

$result=$conn->query("
SELECT *
FROM rig_daily_log
$where
ORDER BY date DESC
");

/* HTML */

$html="

<style>

body{
font-family:Arial;
}

.header{
text-align:center;
margin-bottom:20px;
}

.summary{
border-collapse:collapse;
margin-top:10px;
}

.summary td{
border:1px solid #ccc;
padding:8px 14px;
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
border:1px solid #ccc;
padding:6px;
text-align:center;
}

</style>


<div class='header'>

<img src='$logo_base64' height='60'>

<h2>Rig Operations Weekly Report</h2>

<b>Week:</b> $monday → $sunday<br>

<b>Report Date:</b> $report_date<br>

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
</tr>

</table>


<h3>Rig Weekly Performance</h3>

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

/* TABLE LOOP */

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

/* PDF */

$dompdf=new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("weekly_rig_report.pdf");

?>