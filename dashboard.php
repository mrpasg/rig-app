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
COALESCE(SUM(operating_hours),0) operating,
COALESCE(SUM(standby_hours),0) standby,
COALESCE(SUM(breakdown_hours),0) breakdown,
COALESCE(SUM(ilm_hours),0) ilm,
COALESCE(SUM(zero_rate_hours),0) zero_rate,
COUNT(DISTINCT rig) rigs
FROM rig_daily_log
$whereSQL
")->fetch_assoc();

$rigs=$summary['rigs'];
$operating=$summary['operating'];
$standby_total=$summary['standby'];
$breakdown_total=$summary['breakdown'];
$ilm_total=$summary['ilm'];
$zero_total=$summary['zero_rate'];

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


/* ALERTS */

$alerts=$conn->query("
SELECT rig,zero_rate_hours,date
FROM rig_daily_log
WHERE zero_rate_hours>0
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

$dates=[];
$oper=[];
$standby=[];
$breakdown=[];
$ilm=[];
$zero_arr=[];

while($r=$perf->fetch_assoc()){

$dates[]=$r['d'];
$oper[]=$r['operating'];
$standby[]=$r['standby'];
$breakdown[]=$r['breakdown'];
$ilm[]=$r['ilm'];
$zero_arr[]=$r['zero_rate'];

}


/* RIG PERFORMANCE */

$rigPerf=$conn->query("
SELECT rig,SUM(operating_hours) total_operating
FROM rig_daily_log
$whereSQL
GROUP BY rig
");

$rigNames=[];
$rigHours=[];

while($row=$rigPerf->fetch_assoc()){
$rigNames[]=$row['rig'];
$rigHours[]=$row['total_operating'];
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Rig Operations Dashboard</title>

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

.status-running{background:#28a745;color:white;padding:4px;border-radius:4px;}
.status-standby{background:#ffc107;padding:4px;border-radius:4px;}
.status-breakdown{background:#dc3545;color:white;padding:4px;border-radius:4px;}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Rig Operations Dashboard</h3>

<hr>

<a href="add_entry.php" class="btn btn-primary btn-sm">Add Entry</a>
<a href="report_daily.php" class="btn btn-outline-primary btn-sm">Daily Report</a>
<a href="report_monthly.php" class="btn btn-outline-primary btn-sm">Monthly Report</a>

<hr>

<form method="GET" class="row g-2 mb-3">

<div class="col-md-3">

<select name="rig" class="form-select" onchange="this.form.submit()">

<option value="">All Rigs</option>

<option value="PPE-1" <?php if(isset($_GET['rig']) && $_GET['rig']=="PPE-1") echo "selected"; ?>>PPE-1</option>
<option value="PPE-2" <?php if(isset($_GET['rig']) && $_GET['rig']=="PPE-2") echo "selected"; ?>>PPE-2</option>
<option value="PPE-3" <?php if(isset($_GET['rig']) && $_GET['rig']=="PPE-3") echo "selected"; ?>>PPE-3</option>
<option value="PPE-4" <?php if(isset($_GET['rig']) && $_GET['rig']=="PPE-4") echo "selected"; ?>>PPE-4</option>
<option value="PPE-5" <?php if(isset($_GET['rig']) && $_GET['rig']=="PPE-5") echo "selected"; ?>>PPE-5</option>

</select>

</div>

<div class="col-md-3">

<select name="range" class="form-select" onchange="this.form.submit()">

<option value="">All Time</option>
<option value="today" <?php if(isset($_GET['range']) && $_GET['range']=="today") echo "selected"; ?>>Today</option>
<option value="week" <?php if(isset($_GET['range']) && $_GET['range']=="week") echo "selected"; ?>>This Week</option>
<option value="month" <?php if(isset($_GET['range']) && $_GET['range']=="month") echo "selected"; ?>>This Month</option>

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

<div class="col-md-4">

<div class="card-box">

<h5>Downtime Alerts</h5>

<table class="table table-striped">

<tr>
<th>Rig</th>
<th>Zero Rate</th>
<th>Date</th>
</tr>

<?php

while($row=$alerts->fetch_assoc()){

echo "<tr>
<td>{$row['rig']}</td>
<td style='color:red'>{$row['zero_rate_hours']}</td>
<td>{$row['date']}</td>
</tr>";

}

?>

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

</div>

<script>

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


new Chart(document.getElementById('perfChart'),{

type:'line',

data:{
labels: <?php echo json_encode($dates); ?>,

datasets:[

{
label:'Operating',
data: <?php echo json_encode($oper); ?>,
borderColor:'#28a745',
fill:false
},

{
label:'Standby',
data: <?php echo json_encode($standby); ?>,
borderColor:'#ffc107',
fill:false
},

{
label:'Breakdown',
data: <?php echo json_encode($breakdown); ?>,
borderColor:'#dc3545',
fill:false
},

{
label:'ILM',
data: <?php echo json_encode($ilm); ?>,
borderColor:'#6f42c1',
fill:false
},

{
label:'Zero Rate',
data: <?php echo json_encode($zero_arr); ?>,
borderColor:'#000000',
fill:false
}

]

}

});


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

</script>

</body>
</html>