<?php
include "config.php";

$result = $conn->query("
SELECT 
COALESCE(SUM(operating_hours),0) AS operating,
COALESCE(SUM(standby_hours),0) AS standby,
COALESCE(SUM(breakdown_hours),0) AS breakdown,
COALESCE(SUM(ilm_hours),0) AS ilm,
COALESCE(SUM(zero_rate_hours),0) AS zero_rate,
COUNT(DISTINCT rig) AS rigs
FROM rig_daily_log
");

$data = $result->fetch_assoc();

$rigs = $data['rigs'];
$operating = $data['operating'];
$standby = $data['standby'];
$breakdown = $data['breakdown'];
$ilm = $data['ilm'];
$zero = $data['zero_rate'];

$efficiency = ($rigs > 0) ? ($operating / ($rigs*24))*100 : 0;
?>

<!DOCTYPE html>
<html>
<head>

<title>Rig Operations Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
font-family: Arial;
background:#f5f5f5;
padding:30px;
}

.dashboard-cards{
display:flex;
gap:25px;
margin-bottom:30px;
}

.card-box{
background:white;
border-radius:6px;
padding:20px;
width:200px;
text-align:center;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

.chart-box{
background:white;
padding:25px;
margin-bottom:30px;
box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<h2>Rig Operations Dashboard</h2>

<hr>

<a href="dashboard.php">Dashboard</a> |
<a href="add_entry.php">Add Entry</a> |
<a href="alerts.php">Zero Rate Alerts</a> |
<a href="report_daily.php">Daily Report</a> |
<a href="report_monthly.php">Monthly Report</a> |
<a href="export_excel.php">Export Excel</a>

<hr>

<div class="dashboard-cards">

<div class="card-box">
<h5>Total Rigs</h5>
<h3><?php echo $rigs; ?></h3>
</div>

<div class="card-box">
<h5>Operating Hours</h5>
<h3><?php echo $operating; ?></h3>
</div>

<div class="card-box">
<h5>Zero Rate</h5>
<h3 style="color:red"><?php echo $zero; ?></h3>
</div>

<div class="card-box">
<h5>Efficiency</h5>
<h3><?php echo round($efficiency,2); ?>%</h3>
</div>

</div>

<div class="chart-box">

<canvas id="dailyChart"></canvas>

</div>

<script>

const ctx = document.getElementById('dailyChart');

new Chart(ctx, {

type:'bar',

data:{
labels:['Operating','Standby','Breakdown','ILM','Zero Rate'],

datasets:[{

label:'Hours',

data:[
<?php echo $operating; ?>,
<?php echo $standby; ?>,
<?php echo $breakdown; ?>,
<?php echo $ilm; ?>,
<?php echo $zero; ?>
],

backgroundColor:[
'#4CAF50',
'#2196F3',
'#FF9800',
'#9C27B0',
'#F44336'
]

}]

}

});

</script>

<hr>

<h4>Latest Daily Rig Report</h4>

<table class="table table-bordered table-striped">

<tr>
<th>Date</th>
<th>Rig</th>
<th>Operating</th>
<th>Standby</th>
<th>Breakdown</th>
<th>ILM</th>
<th>Zero Rate</th>
</tr>

<?php

$daily = $conn->query("
SELECT date, rig, operating_hours, standby_hours, breakdown_hours, ilm_hours, zero_rate_hours
FROM rig_daily_log
ORDER BY date DESC
LIMIT 10
");

while($row = $daily->fetch_assoc()){

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

</table>

</body>
</html>