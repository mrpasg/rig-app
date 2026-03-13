<?php
include "config.php";

/* FILTERS */

$rig = $_GET['rig'] ?? "";
$week = $_GET['week'] ?? date("Y-m-d");

$monday = date('Y-m-d', strtotime('monday this week', strtotime($week)));
$sunday = date('Y-m-d', strtotime('sunday this week', strtotime($week)));

$where = [];
$where[] = "date BETWEEN '$monday' AND '$sunday'";

if($rig!=""){
$rig=$conn->real_escape_string($rig);
$where[]="rig='$rig'";
}

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

$operating=$summary['operating'] ?? 0;
$standby=$summary['standby'] ?? 0;
$breakdown=$summary['breakdown'] ?? 0;
$ilm=$summary['ilm'] ?? 0;
$zero=$summary['zero_rate'] ?? 0;
$rigs=$summary['rigs'] ?? 0;

$efficiency=($rigs>0)?($operating/($rigs*24*7))*100:0;

/* TREND */

$trend=$conn->query("
SELECT date,
SUM(operating_hours) operating,
SUM(standby_hours) standby,
SUM(breakdown_hours) breakdown,
SUM(ilm_hours) ilm,
SUM(zero_rate_hours) zero_rate
FROM rig_daily_log
$whereSQL
GROUP BY date
ORDER BY date
");

$dates=[];$oper=[];$stand=[];$break=[];$ilmv=[];$zeroa=[];

while($r=$trend->fetch_assoc()){
$dates[]=$r['date'];
$oper[]=$r['operating'];
$stand[]=$r['standby'];
$break[]=$r['breakdown'];
$ilmv[]=$r['ilm'];
$zeroa[]=$r['zero_rate'];
}

/* RIG PERFORMANCE */

$rigPerf=$conn->query("
SELECT rig,SUM(operating_hours) hours
FROM rig_daily_log
$whereSQL
GROUP BY rig
");

$rigNames=[];$rigHours=[];

while($row=$rigPerf->fetch_assoc()){
$rigNames[]=$row['rig'];
$rigHours[]=$row['hours'];
}

/* TABLE */

$table=$conn->query("
SELECT *
FROM rig_daily_log
$whereSQL
ORDER BY date DESC
");

?>

<!DOCTYPE html>
<html>

<head>

<title>Weekly Rig Report</title>

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

.summary-card h4{
margin:5px 0;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Weekly Rig Performance</h3>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="report_daily.php" class="btn btn-primary btn-sm">Daily</a>
<a href="report_monthly.php" class="btn btn-primary btn-sm">Monthly</a>

<hr>

<div class="card-box">

<form method="GET" class="row g-2">

<div class="col-md-3">

<select name="rig" class="form-select">

<option value="">All Rigs</option>

<option value="PPE-1">PPE-1</option>
<option value="PPE-2">PPE-2</option>
<option value="PPE-3">PPE-3</option>
<option value="PPE-4">PPE-4</option>
<option value="PPE-5">PPE-5</option>

</select>

</div>

<div class="col-md-3">

<input type="date" name="week" class="form-control" value="<?=$week?>">

</div>

<div class="col-md-2">

<button class="btn btn-success">Filter</button>

</div>

</form>

<hr>

<b>Week:</b> <?=$monday?> → <?=$sunday?>

</div>


<!-- SUMMARY -->

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


<!-- TREND -->

<div class="card-box">

<h5>Weekly Operational Trend</h5>

<canvas id="trendChart"></canvas>

</div>


<!-- DOWNTIME PIE -->

<div class="card-box">

<h5>Downtime Cause Analysis</h5>

<canvas id="downtimeChart"></canvas>

</div>


<!-- RIG PERFORMANCE -->

<div class="card-box">

<h5>Rig Performance Comparison</h5>

<canvas id="rigChart"></canvas>

</div>


<!-- EXPORT -->

<form method="POST" action="export_weekly_pdf.php">

<input type="hidden" name="monday" value="<?=$monday?>">
<input type="hidden" name="sunday" value="<?=$sunday?>">
<input type="hidden" name="rig" value="<?=$rig?>">

<button class="btn btn-danger">Export Weekly PDF</button>

</form>


<!-- TABLE -->

<div class="card-box">

<h5>Weekly Rig Log</h5>

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
while($row=$table->fetch_assoc()){
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

/* TREND */

new Chart(document.getElementById('trendChart'),{

type:'line',

data:{
labels: <?=json_encode($dates)?>,
datasets:[
{label:'Operating',data:<?=json_encode($oper)?>,borderColor:'#28a745'},
{label:'Standby',data:<?=json_encode($stand)?>,borderColor:'#ffc107'},
{label:'Breakdown',data:<?=json_encode($break)?>,borderColor:'#dc3545'},
{label:'ILM',data:<?=json_encode($ilmv)?>,borderColor:'#6f42c1'},
{label:'Zero Rate',data:<?=json_encode($zeroa)?>,borderColor:'#000'}
]
}

});


/* DOWNTIME PIE */

new Chart(document.getElementById('downtimeChart'),{

type:'pie',

data:{
labels:['Standby','Breakdown','ILM','Zero Rate'],
datasets:[{
data:[<?=$standby?>,<?=$breakdown?>,<?=$ilm?>,<?=$zero?>],
backgroundColor:['#ffc107','#dc3545','#6f42c1','#000']
}]
}

});


/* RIG COMPARISON */

new Chart(document.getElementById('rigChart'),{

type:'bar',

data:{
labels: <?=json_encode($rigNames)?>,
datasets:[{
label:'Operating Hours',
data: <?=json_encode($rigHours)?>,
backgroundColor:'#0d6efd'
}]
}

});

</script>

</body>
</html>