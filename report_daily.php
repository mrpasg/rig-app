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
zero_rate_hours
FROM rig_daily_log
ORDER BY date DESC
");

$dates=[];
$operating=[];
$zero=[];
?>

<!DOCTYPE html>
<html>

<head>

<title>Daily Rig Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#f4f6f9;
font-family:Arial;
}

.card-box{
background:white;
padding:20px;
border-radius:8px;
box-shadow:0 2px 8px rgba(0,0,0,0.1);
margin-bottom:20px;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Daily Rig Performance</h3>

<hr>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="add_entry.php" class="btn btn-primary btn-sm">Add Entry</a>
<a href="report_monthly.php" class="btn btn-outline-primary btn-sm">Monthly Report</a>
<a href="alerts.php" class="btn btn-danger btn-sm">Zero Rate Alerts</a>

<hr>

<div class="card-box">

<table class="table table-striped table-bordered">

<thead class="table-dark">

<tr>
<th>Date</th>
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

$dates[]=$row['date'];
$operating[]=$row['operating_hours'];
$zero[]=$row['zero_rate_hours'];

echo "<tr>
<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td>{$row['operating_hours']}</td>
<td>{$row['standby_hours']}</td>
<td>{$row['breakdown_hours']}</td>
<td>{$row['ilm_hours']}</td>
<td style='color:red'>{$row['zero_rate_hours']}</td>
</tr>";

}

?>

</tbody>

</table>

</div>

<div class="card-box">

<h5>Daily Performance Chart</h5>

<canvas id="dailyChart"></canvas>

</div>

</div>

<script>

new Chart(document.getElementById('dailyChart'),{

type:'line',

data:{
labels: <?php echo json_encode($dates); ?>,
datasets:[
{
label:'Operating Hours',
data: <?php echo json_encode($operating); ?>,
borderColor:'#4CAF50',
fill:false
},
{
label:'Zero Rate',
data: <?php echo json_encode($zero); ?>,
borderColor:'#F44336',
fill:false
}
]
}

});

</script>

</body>
</html>