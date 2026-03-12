<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

/* LOGO */

$logo_path = __DIR__."/logo.png";
$logo_base64="";

if(file_exists($logo_path)){
$logo_base64="data:image/png;base64,".base64_encode(file_get_contents($logo_path));
}

$month=$_POST['month'];
$chart_image=$_POST['chart_image'];

/* DATE RANGE */

$start = date("Y-m-01", strtotime($month));
$end   = date("Y-m-t", strtotime($month));

$report_date=date("d-M-Y");
$report_time=date("H:i:s");

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
WHERE date BETWEEN '$start' AND '$end'
")->fetch_assoc();

$operating=$summary['operating'];
$standby=$summary['standby'];
$breakdown=$summary['breakdown'];
$ilm=$summary['ilm'];
$zero=$summary['zero_rate'];
$rigs=$summary['rigs'];

$days=date('t',strtotime($month));
$efficiency = ($rigs>0)?($operating/($rigs*24*$days))*100:0;

/* TABLE */

$result=$conn->query("
SELECT *
FROM rig_daily_log
WHERE date BETWEEN '$start' AND '$end'
ORDER BY date DESC
");


$html="

<style>

body{font-family:Arial}

.summary td{
border:1px solid #ccc;
padding:8px 12px;
}

.table{
border-collapse:collapse;
width:100%;
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

<div style='text-align:center'>

<img src='$logo_base64' height='60'>

<h2>Rig Operations Monthly Report</h2>

<b>Month:</b> $month<br>

<b>Report Date:</b> $report_date<br>

<b>Generated Time:</b> $report_time

</div>

<h3>Fleet Summary</h3>

<table class='summary'>

<tr>

<td><b>Total Rigs</b></td>
<td>$rigs</td>

<td><b>Fleet Efficiency</b></td>
<td>".round($efficiency,1)." %</td>

</tr>

<tr>

<td><b>Operating</b></td>
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
";

if($chart_image){

$html.="

<h3>Fleet Performance Distribution</h3>

<div style='text-align:center'>

<img src='$chart_image' width='420'>

</div>

";

}

$html.="

<h3>Rig Monthly Performance</h3>

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

</tr>";

}

$html.="</table>";

$dompdf=new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("monthly_rig_report.pdf");

?>
