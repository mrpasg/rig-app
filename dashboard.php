```php
<?php
include "config.php";

/* FILTER BUILD */

$where=[];

if(isset($_GET['range'])){

if($_GET['range']=="today")
$where[]="date = CURDATE()";

if($_GET['range']=="week")
$where[]="YEARWEEK(date)=YEARWEEK(CURDATE())";

if($_GET['range']=="month")
$where[]="MONTH(date)=MONTH(CURDATE())";

}

if(isset($_GET['rig']) && $_GET['rig']!=""){
$rig=$conn->real_escape_string($_GET['rig']);
$where[]="rig='$rig'";
}

$whereSQL="";
if(count($where)>0)
$whereSQL="WHERE ".implode(" AND ",$where);


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
$whereSQL
")->fetch_assoc();

$operating=$summary['operating'];
$standby_total=$summary['standby'];
$breakdown_total=$summary['breakdown'];
$ilm_total=$summary['ilm'];
$zero_total=$summary['zero_rate'];
$rigs=$summary['rigs'];

$efficiency=($rigs>0)?($operating/($rigs*24))*100:0;


/* STATUS BOARD */

$status=$conn->query("
SELECT r1.rig,r1.status
FROM rig_daily_log r1
INNER JOIN
(
SELECT rig,MAX(date) maxdate
FROM rig_daily_log
GROUP BY rig
) r2
ON r1.rig=r2.rig AND r1.date=r2.maxdate
".(isset($rig)?"WHERE r1.rig='$rig'":"")."
");


/* ALERT PANELS */

$zeroAlerts=$conn->query("
SELECT rig,zero_rate_hours,date
FROM rig_daily_log
WHERE zero_rate_hours>0
".(isset($rig)?"AND rig='$rig'":"")."
ORDER BY date DESC
LIMIT 5
");

$standbyAlerts=$conn->query("
SELECT rig,standby_hours,date
FROM rig_daily_log
WHERE standby_hours>0
".(isset($rig)?"AND rig='$rig'":"")."
ORDER BY date DESC
LIMIT 5
");

$breakdownAlerts=$conn->query("
SELECT rig,breakdown_hours,date
FROM rig_daily_log
WHERE breakdown_hours>0
".(isset($rig)?"AND rig='$rig'":"")."
ORDER BY date DESC
LIMIT 5
");

$ilmAlerts=$conn->query("
SELECT rig,ilm_hours,date
FROM rig_daily_log
WHERE ilm_hours>0
".(isset($rig)?"AND rig='$rig'":"")."
ORDER BY date DESC
LIMIT 5
");


/* PERFORMANCE TREND */

$perf=$conn->query("
SELECT DATE(date) d,
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate
FROM rig_daily_log
$whereSQL
GROUP BY d
ORDER BY d
");

$dates=[];$oper=[];$standby=[];$breakdown=[];$ilm=[];$zero=[];

while($r=$perf->fetch_assoc()){

$dates[]=$r['d'];
$oper[]=$r['operating'];
$standby[]=$r['standby'];
$breakdown[]=$r['breakdown'];
$ilm[]=$r['ilm'];
$zero[]=$r['zero_rate'];

}


/* RIG PERFORMANCE */

$rigPerf=$conn->query("
SELECT rig,SUM(operating_hours) total_operating
FROM rig_daily_log
$whereSQL
GROUP BY rig
");

$rigNames=[];$rigHours=[];

while($row=$rigPerf->fetch_assoc()){
$rigNames[]=$row['rig'];
$rigHours[]=$row['total_operating'];
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Rig Operations Dashboard</title>

<link rel="stylesheet" href="style.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include "header.php"; ?>
<?php include "sidebar.php"; ?>

<div class="main">

<h3>Rig Operations Dashboard</h3>

<form method="GET" class="row g-2 mb-3">

<div class="col-md-3">

<select name="rig" class="form-select" onchange="this.form.submit()">

<option value="">All Rigs</option>

<option value="PPE-1">PPE-1</option>
<option value="PPE-2">PPE-2</option>
<option value="PPE-3">PPE-3</option>
<option value="PPE-4">PPE-4</option>
<option value="PPE-5">PPE-5</option>

</select>

</div>

<div class="col-md-3">

<select name="range" class="form-select" onchange="this.form.submit()">

<option value="">All Time</option>
<option value="today">Today</option>
<option value="week">This Week</option>
<option value="month">This Month</option>

</select>

</div>

</form>


<div class="row">

<div class="col-md-4">

<div class="card-box">

<h5>Rig Status Board</h5>

<table class="table table-bordered">

<tr>
<th>Rig</th>
<th>Status</th>
</tr>

<?php

while($row=$status->fetch_assoc()){

$cls='';

if($row['status']=="Running") $cls="status-running";
if($row['status']=="Standby") $cls="status-standby";
if($row['status']=="Breakdown") $cls="status-breakdown";

echo "<tr>
<td>{$row['rig']}</td>
<td><span class='$cls'>{$row['status']}</span></td>
</tr>";

}

?>

</table>

</div>

</div>

<div class="col-md-4">

<div class="card-box text-center">

<h5>Rig Efficiency</h5>

<canvas id="effGauge"></canvas>

<h4><?php echo round($efficiency,1); ?>%</h4>

</div>

</div>

</div>


<div class="row">

<div class="col-md-3">
<div class="card-box">
<h6>Zero Rate Alerts</h6>
<table class="table table-sm">
<tr><th>Rig</th><th>Zero</th><th>Date</th></tr>

<?php while($r=$zeroAlerts->fetch_assoc()){
echo "<tr>
<td>{$r['rig']}</td>
<td style='color:red'>{$r['zero_rate_hours']}</td>
<td>{$r['date']}</td>
</tr>";
} ?>

</table>
</div>
</div>

<div class="col-md-3">
<div class="card-box">
<h6>Standby Alerts</h6>
<table class="table table-sm">
<tr><th>Rig</th><th>Standby</th><th>Date</th></tr>

<?php while($r=$standbyAlerts->fetch_assoc()){
echo "<tr>
<td>{$r['rig']}</td>
<td style='color:#ffc107'>{$r['standby_hours']}</td>
<td>{$r['date']}</td>
</tr>";
} ?>

</table>
</div>
</div>

<div class="col-md-3">
<div class="card-box">
<h6>Breakdown Alerts</h6>
<table class="table table-sm">
<tr><th>Rig</th><th>Breakdown</th><th>Date</th></tr>

<?php while($r=$breakdownAlerts->fetch_assoc()){
echo "<tr>
<td>{$r['rig']}</td>
<td style='color:#dc3545'>{$r['breakdown_hours']}</td>
<td>{$r['date']}</td>
</tr>";
} ?>

</table>
</div>
</div>

<div class="col-md-3">
<div class="card-box">
<h6>ILM Alerts</h6>
<table class="table table-sm">
<tr><th>Rig</th><th>ILM</th><th>Date</th></tr>

<?php while($r=$ilmAlerts->fetch_assoc()){
echo "<tr>
<td>{$r['rig']}</td>
<td style='color:#6f42c1'>{$r['ilm_hours']}</td>
<td>{$r['date']}</td>
</tr>";
} ?>

</table>
</div>
</div>

</div>


<div class="card-box">
<h5>Operational Trend</h5>
<canvas id="perfChart"></canvas>
</div>

<div class="card-box">
<h5>Rig Performance Comparison</h5>
<canvas id="rigChart"></canvas>
</div>

<div class="card-box">
<h5>Downtime Cause Analysis</h5>
<canvas id="downtimeChart"></canvas>
</div>

</div>


<script>

/* Efficiency Gauge */

new Chart(document.getElementById('effGauge'),{

type:'doughnut',

data:{
labels:['Efficiency','Remaining'],
datasets:[{
data:[<?php echo $efficiency ?>,100-<?php echo $efficiency ?>],
backgroundColor:['#28a745','#e0e0e0']
}]
},

options:{cutout:'70%',plugins:{legend:{display:false}}}

});


/* Operational Trend */

new Chart(document.getElementById('perfChart'),{

type:'line',

data:{
labels: <?php echo json_encode($dates); ?>,

datasets:[

{label:'Operating',data: <?php echo json_encode($oper); ?>,borderColor:'#28a745'},
{label:'Standby',data: <?php echo json_encode($standby); ?>,borderColor:'#ffc107'},
{label:'Breakdown',data: <?php echo json_encode($breakdown); ?>,borderColor:'#dc3545'},
{label:'ILM',data: <?php echo json_encode($ilm); ?>,borderColor:'#6f42c1'},
{label:'Zero Rate',data: <?php echo json_encode($zero); ?>,borderColor:'#000'}

]

}

});


/* Rig Comparison */

new Chart(document.getElementById('rigChart'),{

type:'bar',

data:{
labels: <?php echo json_encode($rigNames); ?>,
datasets:[{
label:'Operating Hours',
data: <?php echo json_encode($rigHours); ?>,
backgroundColor:'#007bff'
}]
}

});


/* Downtime Pie */

new Chart(document.getElementById('downtimeChart'),{

type:'pie',

data:{
labels:['Standby','Breakdown','ILM'],
datasets:[{
data:[
<?php echo $standby_total ?>,
<?php echo $breakdown_total ?>,
<?php echo $ilm_total ?>
],
backgroundColor:['#ffc107','#dc3545','#6f42c1']
}]
}

});

</script>

</body>
</html>
```
