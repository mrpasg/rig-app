<?php
include "config.php";

$result=$conn->query("
SELECT
YEARWEEK(date,1) week,
rig,
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate
FROM rig_daily_log
GROUP BY week,rig
ORDER BY week DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Weekly Rig Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Segoe UI;
}

.card-box{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Weekly Rig Report</h3>

<hr>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="add_entry.php" class="btn btn-success btn-sm">Add Entry</a>
<a href="report_daily.php" class="btn btn-primary btn-sm">Daily Report</a>
<a href="report_monthly.php" class="btn btn-primary btn-sm">Monthly Report</a>

<hr>

<div class="card-box">

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>
<th>Week</th>
<th>Rig</th>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
</tr>

</thead>

<tbody>

<?php
while($row=$result->fetch_assoc()){

echo "<tr>
<td>{$row['week']}</td>
<td>{$row['rig']}</td>
<td>{$row['operating']}</td>
<td>{$row['standby']}</td>
<td>{$row['breakdown']}</td>
<td>{$row['ilm']}</td>
<td style='color:red'>{$row['zero_rate']}</td>
</tr>";

}
?>

</tbody>

</table>

</div>

</div>

</body>
</html>
