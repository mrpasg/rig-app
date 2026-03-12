<?php
include "config.php";

/* -------- FILTERS -------- */

$rig = $_GET['rig'] ?? "";
$date = $_GET['date'] ?? "";
$range = $_GET['range'] ?? "";

$where=[];

if($rig!=""){
$rig=$conn->real_escape_string($rig);
$where[]="rig='$rig'";
}

if($date!=""){
$date=$conn->real_escape_string($date);
$where[]="date='$date'";
}

if($range=="today"){
$where[]="date=CURDATE()";
}

if($range=="yesterday"){
$where[]="date=CURDATE()-INTERVAL 1 DAY";
}

$whereSQL = count($where) ? "WHERE ".implode(" AND ",$where) : "";


/* -------- TABLE DATA -------- */

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


/* -------- PIE CHART DATA -------- */

$chart=$conn->query("
SELECT
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate
FROM rig_daily_log
$whereSQL
");

$data=$chart->fetch_assoc();

$operating_total=$data['operating'] ?? 0;
$standby_total=$data['standby'] ?? 0;
$breakdown_total=$data['breakdown'] ?? 0;
$ilm_total=$data['ilm'] ?? 0;
$zero_total=$data['zero_rate'] ?? 0;

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

<h3>Daily Rig Performance</h3>

<div>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="add_entry.php" class="btn btn-success btn-sm">Add Entry</a>
<a href="report_weekly.php" class="btn btn-primary btn-sm">Weekly Report</a>
<a href="report_monthly.php" class="btn btn-primary btn-sm">Monthly Report</a>

</div>

</div>

<hr>


<!-- FILTER PANEL -->

<div class="card-box">

<form method="GET" class="row g-2">

<div class="col-md-3">

<select name="rig" class="form-select">

<option value="">All Rigs</option>

<option value="PPE-1" <?=$rig=='PPE-1'?'selected':''?>>PPE-1</option>
<option value="PPE-2" <?=$rig=='PPE-2'?'selected':''?>>PPE-2</option>
<option value="PPE-3" <?=$rig=='PPE-3'?'selected':''?>>PPE-3</option>
<option value="PPE-4" <?=$rig=='PPE-4'?'selected':''?>>PPE-4</option>
<option value="PPE-5" <?=$rig=='PPE-5'?'selected':''?>>PPE-5</option>

</select>

</div>

<div class="col-md-3">

<input type="date" name="date" class="form-control" value="<?=$date?>">

</div>

<div class="col-md-2">

<button class="btn btn-success">Filter</button>

</div>

<div class="col-md-2">

<a href="report_daily.php" class="btn btn-secondary">Reset</a>

</div>

</form>

<hr>

<a href="?range=today" class="btn btn-outline-primary btn-sm">Today</a>
<a href="?range=yesterday" class="btn btn-outline-primary btn-sm">Yesterday</a>

</div>


<!-- DATA TABLE -->

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


<!-- PIE CHART -->

<div class="card-box">

<h5>Daily Performance Distribution</h5>

<canvas id="dailyChart"></canvas>

</div>

</div>


<script>

new Chart(document.getElementById('dailyChart'),{

type:'pie',

data:{

labels:[
'Operating Hours',
'Standby',
'Breakdown',
'ILM',
'Zero Rate'
],

datasets:[{

data:[
<?=$operating_total?>,
<?=$standby_total?>,
<?=$breakdown_total?>,
<?=$ilm_total?>,
<?=$zero_total?>
],

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

</script>

</body>
</html>