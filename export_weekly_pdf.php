<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

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
SUM(zero_rate_hours) zero_rate
FROM rig_daily_log
$where
")->fetch_assoc();

$result=$conn->query("
SELECT *
FROM rig_daily_log
$where
ORDER BY date DESC
");

$html="<h2>Weekly Rig Operations Report</h2>";

$html.="<p><b>Week:</b> $monday → $sunday</p>";

$html.="<table border='1' width='100%' cellpadding='6'>
<tr>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
</tr>

<tr>
<td>{$summary['operating']}</td>
<td>{$summary['standby']}</td>
<td>{$summary['breakdown']}</td>
<td>{$summary['ilm']}</td>
<td>{$summary['zero_rate']}</td>
</tr>

</table>";

$html.="<br><h3>Weekly Rig Log</h3>";

$html.="<table border='1' width='100%' cellpadding='6'>
<tr>
<th>Date</th>
<th>Rig</th>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
</tr>";

while($row=$result->fetch_assoc()){

$html.="<tr>
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

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("weekly_rig_report.pdf");

?>
