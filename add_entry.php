<?php
include "config.php";
?>

<!DOCTYPE html>
<html>

<head>

<title>Add Rig Entry</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Arial;
}

.form-card{
background:white;
padding:30px;
border-radius:8px;
box-shadow:0 3px 10px rgba(0,0,0,0.1);
}

</style>

</head>

<body>

<div class="container mt-4">

<h3>Rig Daily Entry</h3>

<hr>

<a href="dashboard.php" class="btn btn-secondary btn-sm">⬅ Back to Dashboard</a>

<a href="report_daily.php" class="btn btn-outline-primary btn-sm">Daily Report</a>

<a href="report_monthly.php" class="btn btn-outline-primary btn-sm">Monthly Report</a>

<a href="alerts.php" class="btn btn-outline-danger btn-sm">Zero Rate Alerts</a>

<hr>

<div class="form-card">

<form action="save_entry.php" method="POST">

<div class="row">

<div class="col-md-4 mb-3">
<label>Date</label>
<input type="date" name="date" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
<label>Rig</label>
<input type="text" name="rig" class="form-control" placeholder="Rig Name" required>
</div>

<div class="col-md-4 mb-3">
<label>Operating Hours</label>
<input type="number" step="0.1" name="operating" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Standby Hours</label>
<input type="number" step="0.1" name="standby" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Breakdown Hours</label>
<input type="number" step="0.1" name="breakdown" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>ILM Hours</label>
<input type="number" step="0.1" name="ilm" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Zero Rate Hours</label>
<input type="number" step="0.1" name="zero" class="form-control">
</div>

<div class="col-md-8 mb-3">
<label>Reason (if Zero Rate)</label>
<input type="text" name="reason" class="form-control">
</div>

<div class="col-md-4 mb-3">
<label>Status</label>
<select name="status" class="form-control">

<option value="Running">Running</option>
<option value="Standby">Standby</option>
<option value="Breakdown">Breakdown</option>

</select>
</div>

</div>

<hr>

<button type="submit" class="btn btn-success">Save Entry</button>

<a href="dashboard.php" class="btn btn-secondary">Cancel</a>

</form>

</div>

</div>

</body>
</html>