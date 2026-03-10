<?php
include "config.php";

$result = $conn->query("
SELECT 
date,
rig,
operating_hours,
standby_hours,
breakdown_hours,
ilm_hours,
zero_rate_hours,
reason
FROM rig_daily_log
ORDER BY date DESC
");
?>

<h2>Daily Rig Report</h2>

<hr>

<a href="dashboard.php">Dashboard</a> |
<a href="add_entry.php">Add Entry</a> |
<a href="alerts.php">Zero Rate Alerts</a> |
<a href="report_monthly.php">Monthly Report</a> |
<a href="export_excel.php">Export Excel</a>

<hr>

<table border="1" cellpadding="8">

<tr>
<th>Date</th>
<th>Rig</th>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
<th>Reason</th>
</tr>

<?php

while($row = $result->fetch_assoc()){

echo "<tr>
<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td>{$row['operating_hours']}</td>
<td>{$row['standby_hours']}</td>
<td>{$row['breakdown_hours']}</td>
<td>{$row['ilm_hours']}</td>
<td style='color:red'>{$row['zero_rate_hours']}</td>
<td>{$row['reason']}</td>
</tr>";

}

?>

</table>
