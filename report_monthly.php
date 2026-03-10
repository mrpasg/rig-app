<?php
include "config.php";

$result = $conn->query("
SELECT 
DATE_FORMAT(date,'%Y-%m') AS month,
SUM(operating_hours) AS operating,
SUM(standby_hours) AS standby,
SUM(breakdown_hours) AS breakdown,
SUM(ilm_hours) AS ilm,
SUM(zero_rate_hours) AS zero_rate
FROM rig_daily_log
GROUP BY DATE_FORMAT(date,'%Y-%m')
ORDER BY month DESC
");

$months=[];
$operating=[];
$zero=[];
?>

<!DOCTYPE html>
<html>

<head>

<title>Monthly Rig Report</title>

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

<h3>Monthly Rig Performance</h3>

<hr>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="add_entry.php" class="btn btn-primary btn-sm">Add Entry</a>
<a href="report_daily.php" class="btn btn-outline-primary btn-sm">Daily Report</a>
<a href="alerts.php" class="btn btn-danger btn-sm">Zero Rate Alerts</a>

<hr>

<div class="card-box">

<table class="table table-striped table-bordered">

<thead class="table-dark">

<tr>
<th>Month</th>
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

$months[]=$row['month'];
$operating[]=$row['operating'];
$zero[]=$row['zero_rate'];

echo "<tr>
<td>{$row['month']}</td>
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

<div class="card-box">

<h5>Monthly Performance Chart</h5>

<canvas id="monthlyChart"></canvas>

</div>

</div>

<script>

new Chart(document.getElementById('monthlyChart'),{

type:'bar',

data:{
labels: <?php echo json_encode($months); ?>,
datasets:[
{
label:'Operating Hours',
data: <?php echo json_encode($operating); ?>,
backgroundColor:'#4CAF50'
},
{
label:'Zero Rate',
data: <?php echo json_encode($zero); ?>,
backgroundColor:'#F44336'
}
]
}

});

</script>

</body>
</html>