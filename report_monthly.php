<?php
include "config.php";

$result = $conn->query("
SELECT 
MONTH(date) AS month,
SUM(operating_hours) AS operating,
SUM(zero_rate_hours) AS zero
FROM rig_daily_log
GROUP BY MONTH(date)
");
?>

<h2>Monthly Performance</h2>

<table border="1">

<tr>
<th>Month</th>
<th>Operating Hours</th>
<th>Zero Rate Hours</th>
</tr>

<?php
while($row = $result->fetch_assoc()){
echo "<tr>
<td>{$row['month']}</td>
<td>{$row['operating']}</td>
<td style='color:red'>{$row['zero']}</td>
</tr>";
}
?>

</table>
