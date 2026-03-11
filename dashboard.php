<?php
include "config.php";

/* ---------- FILTERS ---------- */

$where=[];
$rig = $_GET['rig'] ?? '';
$range = $_GET['range'] ?? '';

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

$whereSQL="";
if(count($where)>0)
$whereSQL="WHERE ".implode(" AND ",$where);


/* ---------- SUMMARY ---------- */

$summary=$conn->query("
SELECT
COALESCE(SUM(operating_hours),0) operating,
COALESCE(SUM(standby_hours),0) standby,
COALESCE(SUM(breakdown_hours),0) breakdown,
COALESCE(SUM(ilm_hours),0) ilm,
COALESCE(SUM(zero_rate_hours),0) zero_rate,
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

$efficiency = ($rigs>0)? ($operating/($rigs*24))*100 : 0;
if($efficiency>100) $efficiency=100;


/* ---------- STATUS BOARD ---------- */

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
".($rig!=""?"WHERE r1.rig='$rig'":"")."
");


/* ---------- ALERTS ---------- */

function getAlerts($conn,$column,$rig){
return $conn->query("
SELECT rig,$column,date
FROM rig_daily_log
WHERE $column>0
".($rig!=""?"AND rig='$rig'":"")."
ORDER BY date DESC
LIMIT 5
");
}

$zeroAlerts=getAlerts($conn,"zero_rate_hours",$rig);
$standbyAlerts=getAlerts($conn,"standby_hours",$rig);
$breakdownAlerts=getAlerts($conn,"breakdown_hours",$rig);
$ilmAlerts=getAlerts($conn,"ilm_hours",$rig);


/* ---------- TREND ---------- */

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


/* ---------- RIG COMPARISON ---------- */

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

<title>Rig Monitoring Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
background:#f4f6f9;
font-family:Arial;
}

.main{
margin-left:240px;
padding:20px;
}

.card-box{
background:white;
padding:20px;
border-radius:10px;
box-shadow:0 4px 10px rgba(0,0,0,0.1);
margin-bottom:20px;
}

.status-running{background:#28a745;color:white;padding:5px 10px;border-radius:6px;}
.status-standby{background:#ffc107;padding:5px 10px;border-radius:6px;}
.status-breakdown{background:#dc3545;color:white;padding:5px 10px;border-radius:6px;}

</style>

</head>

<body>

<?php include "header.php"; ?>
<?php include "sidebar.php"; ?>


<div class="main">

<h3>Rig Operations Dashboard</h3>

<div class="mb-3">

<a href="add_entry.php" class="btn btn-success">+ Add Entry</a>
<a href="report_daily.php" class="btn btn-primary">Daily Report</a>
<a href="report_monthly.php" class="btn btn-primary">Monthly Report</a>

</div>


<form method="GET" class="row g-2 mb-3">

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
<option value="today" <?=$range=='today'?'selected':''?>>Today</option>
<option value="week" <?=$range=='week'?'selected':''?>>This Week</option>
<option value="month" <?=$range=='month'?'selected':''?>>This Month</option>

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

<h4><?=round($efficiency,1)?>%</h4>

</div>

</div>

</div>


<div class="row">

<?php
function renderAlert($title,$result,$field,$color){
echo "<div class='col-md-3'>
<div class='card-box'>
<h6>$title</h6>
<table class='table table-sm'>
<tr><th>Rig</th><th>Hours</th><th>Date</th></tr>";

while($r=$result->fetch_assoc()){
echo "<tr>
<td>{$r['rig']}</td>
<td style='color:$color'>{$r[$field]}</td>
<td>{$r['date']}</td>
</tr>";
}

echo "</table></div></div>";
}

renderAlert("Zero Rate Alerts",$zeroAlerts,"zero_rate_hours","red");
renderAlert("Standby Alerts",$standbyAlerts,"standby_hours","#ffc107");
renderAlert("Breakdown Alerts",$breakdownAlerts,"breakdown_hours","#dc3545");
renderAlert("ILM Alerts",$ilmAlerts,"ilm_hours","#6f42c1");
?>

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
data:[<?=$efficiency?>,100-<?=$efficiency?>],
backgroundColor:['#28a745','#e0e0e0']
}]
},

options:{cutout:'70%',plugins:{legend:{display:false}}}

});


/* Operational Trend */

new Chart(document.getElementById('perfChart'),{

type:'line',

data:{
labels: <?=json_encode($dates)?>,

datasets:[

{label:'Operating',data: <?=json_encode($oper)?>,borderColor:'#28a745',tension:0.3},
{label:'Standby',data: <?=json_encode($standby)?>,borderColor:'#ffc107',tension:0.3},
{label:'Breakdown',data: <?=json_encode($breakdown)?>,borderColor:'#dc3545',tension:0.3},
{label:'ILM',data: <?=json_encode($ilm)?>,borderColor:'#6f42c1',tension:0.3},
{label:'Zero Rate',data: <?=json_encode($zero)?>,borderColor:'#000',tension:0.3}

]

}

});


/* Rig Comparison */

new Chart(document.getElementById('rigChart'),{

type:'bar',

data:{
labels: <?=json_encode($rigNames)?>,
datasets:[{
label:'Operating Hours',
data: <?=json_encode($rigHours)?>,
backgroundColor:'#007bff'
}]
}

});


/* Downtime Analysis */

new Chart(document.getElementById('downtimeChart'),{

type:'pie',

data:{
labels:['Standby','Breakdown','ILM'],
datasets:[{
data:[<?=$standby_total?>,<?=$breakdown_total?>,<?=$ilm_total?>],
backgroundColor:['#ffc107','#dc3545','#6f42c1']
}]
}

});

</script>

</body>
</html>