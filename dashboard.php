<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

include "config.php";

/* ---------------- FILTER ---------------- */

$rig = $_GET['rig'] ?? "";
$range = $_GET['range'] ?? "";

$where=[];

if($range=="today")
$where[]="date = CURDATE()";

if($range=="week")
$where[]="YEARWEEK(date,1)=YEARWEEK(CURDATE(),1)";

if($range=="month")
$where[]="MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE())";

if($rig!=""){
$rig=$conn->real_escape_string($rig);
$where[]="rig='$rig'";
}

$whereSQL = count($where) ? "WHERE ".implode(" AND ",$where) : "";


/* ---------------- SUMMARY ---------------- */

$q=$conn->query("
SELECT
COALESCE(SUM(operating_hours),0) operating,
COALESCE(SUM(standby_hours),0) standby,
COALESCE(SUM(breakdown_hours),0) breakdown,
COALESCE(SUM(ilm_hours),0) ilm,
COALESCE(SUM(zero_rate_hours),0) zero_rate,
COUNT(DISTINCT rig) rigs
FROM rig_daily_log
$whereSQL
");

$summary=$q?$q->fetch_assoc():[
'operating'=>0,
'standby'=>0,
'breakdown'=>0,
'ilm'=>0,
'zero_rate'=>0,
'rigs'=>0
];

$operating=$summary['operating'];
$standby_total=$summary['standby'];
$breakdown_total=$summary['breakdown'];
$ilm_total=$summary['ilm'];
$zero_total=$summary['zero_rate'];
$rigs=$summary['rigs'];

$efficiency = ($rigs>0)?($operating/($rigs*24))*100:0;
if($efficiency>100) $efficiency=100;
if($efficiency<0) $efficiency=0;


/* ---------------- STATUS ---------------- */

$status=$conn->query("
SELECT r1.rig,r1.status
FROM rig_daily_log r1
INNER JOIN (
SELECT rig,MAX(date) maxdate
FROM rig_daily_log
$whereSQL
GROUP BY rig
) r2
ON r1.rig=r2.rig AND r1.date=r2.maxdate
");


/* ---------------- TREND ---------------- */

$trend=$conn->query("
SELECT DATE(date) d,
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm
FROM rig_daily_log
$whereSQL
GROUP BY d
ORDER BY d
");

$dates=[];
$oper=[];
$standby=[];
$breakdown=[];
$ilm=[];

while($trend && $r=$trend->fetch_assoc()){

$dates[]=$r['d'];
$oper[]=$r['operating'];
$standby[]=$r['standby'];
$breakdown[]=$r['breakdown'];
$ilm[]=$r['ilm'];

}


/* ---------------- RIG PERFORMANCE ---------------- */

$rigPerf=$conn->query("
SELECT rig,SUM(operating_hours) hours
FROM rig_daily_log
$whereSQL
GROUP BY rig
");

$rigNames=[];
$rigHours=[];

while($rigPerf && $row=$rigPerf->fetch_assoc()){
$rigNames[]=$row['rig'];
$rigHours[]=$row['hours'];
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Oilfield Rig Monitoring Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
margin:0;
font-family:Segoe UI;
background:#f3f5f9;
}

.topbar{
height:60px;
background:#0b3d6d;
color:white;
display:flex;
align-items:center;
padding:0 20px;
font-weight:600;
}

.sidebar{
width:220px;
height:100vh;
background:#1e293b;
position:fixed;
top:60px;
left:0;
padding-top:20px;
}

.sidebar a{
display:block;
color:#cbd5e1;
padding:12px 20px;
text-decoration:none;
}

.sidebar a:hover{
background:#0ea5e9;
color:white;
}

.main{
margin-left:220px;
margin-top:60px;
padding:25px;
}

.card-box{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
margin-bottom:20px;
}

.summary{
text-align:center;
}

.summary h3{
margin:5px 0;
}

.status-running{
background:#22c55e;
color:white;
padding:4px 10px;
border-radius:6px;
}

.status-standby{
background:#facc15;
padding:4px 10px;
border-radius:6px;
}

.status-breakdown{
background:#ef4444;
color:white;
padding:4px 10px;
border-radius:6px;
}

</style>

</head>

<body>

<div class="topbar">

<img src="logo.png" height="35" style="margin-right:10px">

Rig Operations Monitoring System

</div>

<div class="sidebar">

<a href="dashboard.php">Dashboard</a>
<a href="add_entry.php">Add Entry</a>
<a href="report_daily.php">Daily Report</a>
<a href="report_monthly.php">Monthly Report</a>
<a href="alerts.php">Alerts</a>

</div>


<div class="main">

<h3>Rig Monitoring Dashboard</h3>

<div class="mb-3">

<a href="add_entry.php" class="btn btn-success">Add Entry</a>
<a href="report_daily.php" class="btn btn-primary">Daily Report</a>
<a href="report_monthly.php" class="btn btn-primary">Monthly Report</a>

</div>


<form method="GET" class="row g-2 mb-4">

<div class="col-md-3">

<select name="rig" class="form-select" onchange="this.form.submit()">

<option value="">All Rigs</option>

<option value="PPE-1" <?=$rig=='PPE-1'?'selected':''?>>PPE-1</option>
<option value="PPE-2" <?=$rig=='PPE-2'?'selected':''?>>PPE-2</option>
<option value="PPE-3" <?=$rig=='PPE-3'?'selected':''?>>PPE-3</option>
<option value="PPE-4" <?=$rig=='PPE-4'?'selected':''?>>PPE-4</option>
<option value="PPE-5" <?=$rig=='PPE-5'?'selected':''?>>PPE-5</option>

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

<div class="col-md-3">
<div class="card-box summary">
<h6>Total Rigs</h6>
<h3><?=$rigs?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card-box summary">
<h6>Operating Hours</h6>
<h3><?=$operating?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card-box summary">
<h6>Zero Rate</h6>
<h3 style="color:red"><?=$zero_total?></h3>
</div>
</div>

<div class="col-md-3">
<div class="card-box summary">
<h6>Efficiency</h6>
<h3><?=round($efficiency,1)?>%</h3>
</div>
</div>

</div>


<div class="row">

<div class="col-md-4">

<div class="card-box">

<h5>Rig Status Board</h5>

<table class="table">

<tr>
<th>Rig</th>
<th>Status</th>
</tr>

<?php

while($status && $row=$status->fetch_assoc()){

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


<div class="col-md-8">

<div class="card-box">

<h5>Operational Trend</h5>

<canvas id="trendChart"></canvas>

</div>

</div>

</div>


<div class="row">

<div class="col-md-6">

<div class="card-box">

<h5>Rig Performance Comparison</h5>

<canvas id="rigChart"></canvas>

</div>

</div>

<div class="col-md-6">

<div class="card-box">

<h5>Downtime Cause Analysis</h5>

<canvas id="downtimeChart"></canvas>

</div>

</div>

</div>

</div>


<script>

new Chart(document.getElementById('trendChart'),{

type:'line',

data:{
labels: <?=json_encode($dates)?>,
datasets:[
{label:'Operating',data:<?=json_encode($oper)?>,borderColor:'#22c55e',tension:0.3},
{label:'Standby',data:<?=json_encode($standby)?>,borderColor:'#facc15',tension:0.3},
{label:'Breakdown',data:<?=json_encode($breakdown)?>,borderColor:'#ef4444',tension:0.3},
{label:'ILM',data:<?=json_encode($ilm)?>,borderColor:'#9333ea',tension:0.3}
]
}

});


new Chart(document.getElementById('rigChart'),{

type:'bar',

data:{
labels: <?=json_encode($rigNames)?>,
datasets:[{
label:'Operating Hours',
data: <?=json_encode($rigHours)?>,
backgroundColor:'#3b82f6'
}]
}

});


new Chart(document.getElementById('downtimeChart'),{

type:'pie',

data:{
labels:['Standby','Breakdown','ILM'],
datasets:[{
data:[<?=$standby_total?>,<?=$breakdown_total?>,<?=$ilm_total?>],
backgroundColor:['#facc15','#ef4444','#9333ea']
}]
}

});

</script>

</body>
</html>