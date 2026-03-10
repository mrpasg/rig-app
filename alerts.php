<?php
include "config.php";

$result = $conn->query("
SELECT date, rig, zero_rate_hours, reason
FROM rig_daily_log
WHERE zero_rate_hours > 0
ORDER BY date DESC
");
?>

<h2>Zero Rate Alerts</h2>

<table border="1">
<tr>
<th>Date</th>
<th>Rig</th>
<th>Zero Rate Hours</th>
<th>Reason</th>
</tr>

<?php
while($row = $result->fetch_assoc()){
echo "<tr>
<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td style='color:red'>{$row['zero_rate_hours']}</td>
<td>{$row['reason']}</td>
</tr>";
}
?>

</table>
