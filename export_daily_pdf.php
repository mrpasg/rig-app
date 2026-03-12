<?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

$chart_image = $_POST['chart_image'] ?? '';

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
ORDER BY date DESC
");


$html = "

<h2 style='text-align:center'>Rig Daily Performance Report</h2>

<table border='1' width='100%' cellpadding='6'>

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

$html.="<br><h3>Daily Performance Distribution</h3>";

$html.="<img src='$chart_image' width='400'>";

}

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("daily_rig_report.pdf");

?>