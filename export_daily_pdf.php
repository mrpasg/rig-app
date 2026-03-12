?php

require 'vendor/autoload.php';
include "config.php";

use Dompdf\Dompdf;

/* FILTERS */

$rig = $_GET['rig'] ?? "";
$date = $_GET['date'] ?? "";
$range = $_GET['range'] ?? "";

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


/* BUILD HTML */

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

$html .= "
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

$html .= "</table>";



/* GENERATE PDF */

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4','portrait');

$dompdf->render();

$dompdf->stream("daily_rig_report.pdf");

?>
