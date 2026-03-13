<?php
include "auth.php";
include "config.php";

/* MONTH FILTER */

$month = $_GET['month'] ?? date('Y-m');

$start = date("Y-m-01", strtotime($month));
$end   = date("Y-m-t", strtotime($month));

/* SUMMARY */

$summary=$conn->query("
SELECT
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate,
COUNT(DISTINCT rig) rigs
FROM rig_daily_log
WHERE date BETWEEN '$start' AND '$end'
")->fetch_assoc();

$operating=$summary['operating'] ?? 0;
$standby=$summary['standby'] ?? 0;
$breakdown=$summary['breakdown'] ?? 0;
$ilm=$summary['ilm'] ?? 0;
$zero=$summary['zero_rate'] ?? 0;
$rigs=$summary['rigs'] ?? 0;

$days=date('t',strtotime($month));

$efficiency = ($rigs>0)?($operating/($rigs*24*$days))*100:0;

/* TABLE DATA */

$result=$conn->query("
SELECT *
FROM rig_daily_log
WHERE date BETWEEN '$start' AND '$end'
ORDER BY date DESC
");

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
font-family:Segoe UI;
}

.card-box{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.summary-card{
text-align:center;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Monthly Rig Performance</h3>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="report_daily.php" class="btn btn-primary btn-sm">Daily</a>
<a href="report_weekly.php" class="btn btn-primary btn-sm">Weekly</a>
<a href="logout.php" class="btn btn-danger btn-sm">Logout</a>

<hr>


<div class="card-box">

<form method="GET">

<input type="month" name="month" value="<?=$month?>" class="form-control w-25 d-inline">

<button class="btn btn-success btn-sm">Filter</button>

</form>

</div>


<div class="row">

<div class="col-md-2">
<div class="card-box summary-card">
<h6>Operating</h6>
<h4><?=$operating?></h4>
</div>
</div>

<div class="col-md-2">
<div class="card-box summary-card">
<h6>Standby</h6>
<h4><?=$standby?></h4>
</div>
</div>

<div class="col-md-2">
<div class="card-box summary-card">
<h6>Breakdown</h6>
<h4><?=$breakdown?></h4>
</div>
</div>

<div class="col-md-2">
<div class="card-box summary-card">
<h6>ILM</h6>
<h4><?=$ilm?></h4>
</div>
</div>

<div class="col-md-2">
<div class="card-box summary-card">
<h6>Zero Rate</h6>
<h4 style="color:red"><?=$zero?></h4>
</div>
</div>

<div class="col-md-2">
<div class="card-box summary-card">
<h6>Efficiency</h6>
<h4><?=round($efficiency,1)?>%</h4>
</div>
</div>

</div>


<div class="card-box">

<h5>Fleet Performance Distribution</h5>

<canvas id="chart"></canvas>

<form method="POST" action="export_monthly_pdf.php" class="mt-3">

<input type="hidden" name="month" value="<?=$month?>">
<input type="hidden" name="chart_image" id="chart_image">

<button type="submit" class="btn btn-danger">
Export Monthly PDF
</button>

</form>

</div>


<div class="card-box">

<h5>Rig Monthly Performance</h5>

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

while($row=$result->fetch_assoc()){

echo "<tr>

<td>{$row['date']}</td>
<td>{$row['rig']}</td>
<td>{$row['operating_hours']}</td>
<td>{$row['standby_hours']}</td>
<td>{$row['breakdown_hours']}</td>
<td>{$row['ilm_hours']}</td>
<td>{$row['zero_rate_hours']}</td>

</tr>";

}

?>

</table>

</div>

</div>


<script>

var chart = new Chart(document.getElementById('chart'),{

type:'pie',

data:{
labels:['Operating','Standby','Breakdown','ILM','Zero Rate'],

datasets:[{
data:[<?=$operating?>,<?=$standby?>,<?=$breakdown?>,<?=$ilm?>,<?=$zero?>],
backgroundColor:[
'#4CAF50',
'#FFC107',
'#F44336',
'#9C27B0',
'#000000'
]
}]
}

});


document.querySelector("form").addEventListener("submit",function(){

document.getElementById("chart_image").value =
chart.toBase64Image();

});

</script>

</body>
</html>