<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "auth.php";
include "config.php";

if($_SESSION['role']!="admin" && $_SESSION['role']!="supervisor"){
echo "<h3 style='color:red'>Access Denied</h3>";
exit;
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Rig Daily Entry</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Arial;
}

.form-card{
background:white;
padding:30px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.1);
}

.total-box{
font-weight:bold;
color:#0d6efd;
font-size:18px;
}

.remaining-box{
font-weight:bold;
color:#198754;
font-size:16px;
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Rig Daily Entry</h3>

<hr>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="report_daily.php" class="btn btn-outline-primary btn-sm">Daily Report</a>
<a href="report_monthly.php" class="btn btn-outline-primary btn-sm">Monthly Report</a>

<hr>

<div class="form-card">

<form action="save_entry.php" method="POST" oninput="calculateTotal()">

<div class="row">

<div class="col-md-4 mb-3">
<label>Date</label>
<input type="date" name="date"
class="form-control"
value="<?php echo date('Y-m-d'); ?>"
max="<?php echo date('Y-m-d'); ?>"
required>
</div>

<div class="col-md-4 mb-3">
<label>Rig</label>
<select name="rig" class="form-control" required>

<option value="">Select Rig</option>
<option value="PPE-1">PPE-1</option>
<option value="PPE-2">PPE-2</option>
<option value="PPE-3">PPE-3</option>
<option value="PPE-4">PPE-4</option>
<option value="PPE-5">PPE-5</option>

</select>
</div>

<div class="col-md-4 mb-3">
<label>Status</label>
<select name="status" id="status" class="form-control">

<option value="Running">Running</option>
<option value="Standby">Standby</option>
<option value="Breakdown">Breakdown</option>

</select>
</div>

</div>

<hr>

<h5>Operational Hours</h5>

<div class="row">

<div class="col-md-3 mb-3">
<label>Operating</label>
<input type="number" step="0.01" name="operating"
id="operating" class="form-control" min="0" max="24">
</div>

<div class="col-md-3 mb-3">
<label>Standby</label>
<input type="number" step="0.01" name="standby"
id="standby" class="form-control" min="0" max="24">
</div>

<div class="col-md-3 mb-3">
<label>Breakdown</label>
<input type="number" step="0.01" name="breakdown"
id="breakdown" class="form-control" min="0" max="24">
</div>

<div class="col-md-3 mb-3">
<label>ILM</label>
<input type="number" step="0.01" name="ilm"
id="ilm" class="form-control" min="0" max="24">
</div>

<div class="col-md-3 mb-3">
<label>Zero Rate</label>
<input type="number" step="0.01" name="zero"
id="zero" class="form-control" min="0" max="24">
</div>

<div class="col-md-9 mb-3">
<label>Reason (if Zero Rate)</label>
<input type="text" name="reason" class="form-control">
</div>

</div>

<hr>

<div class="total-box">
Total Hours: <span id="total">0</span> / 24
</div>

<div class="remaining-box">
Remaining Hours: <span id="remaining">24</span>
</div>

<hr>

<button type="submit" class="btn btn-success">
Save Entry
</button>

<a href="dashboard.php" class="btn btn-secondary">
Cancel
</a>

</form>

</div>

</div>


<script>

function calculateTotal(){

let operating = parseFloat(document.getElementById("operating").value) || 0;
let standby = parseFloat(document.getElementById("standby").value) || 0;
let breakdown = parseFloat(document.getElementById("breakdown").value) || 0;
let ilm = parseFloat(document.getElementById("ilm").value) || 0;
let zero = parseFloat(document.getElementById("zero").value) || 0;

let total = operating + standby + breakdown + ilm + zero;

let totalDisplay = document.getElementById("total");
let remainingDisplay = document.getElementById("remaining");

totalDisplay.innerText = total.toFixed(2);

let remaining = Math.max(0, 24 - total);
remainingDisplay.innerText = remaining.toFixed(2);

/* color change */

if(total > 24){
totalDisplay.style.color = "red";
remainingDisplay.style.color = "red";
}else{
totalDisplay.style.color = "#0d6efd";
remainingDisplay.style.color = "#198754";
}

/* auto status */

let status = document.getElementById("status");

if(operating > 0){
status.value = "Running";
}
else if(breakdown > 0){
status.value = "Breakdown";
}
else if(standby > 0){
status.value = "Standby";
}

}


document.addEventListener("DOMContentLoaded", function(){

document.querySelector("form").addEventListener("submit", function(e){

let operating = parseFloat(document.getElementById("operating").value) || 0;
let standby = parseFloat(document.getElementById("standby").value) || 0;
let breakdown = parseFloat(document.getElementById("breakdown").value) || 0;
let ilm = parseFloat(document.getElementById("ilm").value) || 0;
let zero = parseFloat(document.getElementById("zero").value) || 0;

let total = operating + standby + breakdown + ilm + zero;

if(total > 24){

alert("Total hours cannot exceed 24 hours.");
e.preventDefault();

}

});

});

</script>

</body>
</html>