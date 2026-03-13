<?php
include "auth.php";
include "config.php";

$result = $conn->query("
SELECT date, rig, zero_rate_hours, reason
FROM rig_daily_log
WHERE zero_rate_hours > 0
ORDER BY date DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<title>Zero Rate Alerts</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Segoe UI;
}

.card-box{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.header-bar{
display:flex;
justify-content:space-between;
align-items:center;
}

</style>

</head>

<body>

<div class="container mt-4">

<div class="header-bar">

<h3>Zero Rate Alerts</h3>

<div>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="add_entry.php" class="btn btn-success btn-sm">Add Entry</a>
<a href="report_daily.php" class="btn btn-primary btn-sm">Daily Report</a>
<a href="report_weekly.php" class="btn btn-primary btn-sm">Weekly Report</a>
<a href="report_monthly.php" class="btn btn-primary btn-sm">Monthly Report</a>
<a href="logout.php" class="btn btn-danger btn-sm">Logout</a>

</div>

</div>

<hr>

<div class="card-box">

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>
<th>Date</th>
<th>Rig</th>
<th>Zero Rate Hours</th>
<th>Reason</th>
</tr>

</thead>

<tbody>

<?php
while($row = $result->fetch_assoc()){
echo "<tr>
<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td style='color:red;font-weight:bold'>{$row['zero_rate_hours']}</td>
<td>{$row['reason']}</td>
</tr>";
}
?>

</tbody>

</table>

</div>

</div>

</body>
</html>