<?php
error_reporting(E_ALL);
ini_set('display_errors',1);

include "auth.php";
include "config.php";

/* GET RECORD ID */

$id = $_GET['id'] ?? '';

if($id==""){
echo "Invalid Entry";
exit;
}

/* FETCH RECORD */

$result = $conn->query("
SELECT *
FROM rig_daily_log
WHERE id='$id'
");

if($result->num_rows == 0){
echo "Entry not found";
exit;
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>

<head>

<title>Edit Rig Entry</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f6f9;
font-family:Segoe UI;
}

.card-box{
background:white;
padding:25px;
border-radius:10px;
box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

.header-bar{
display:flex;
justify-content:space-between;
align-items:center;
margin-bottom:20px;
}

</style>

</head>

<body>

<div class="container mt-4">

<div class="header-bar">

<h3>Edit Rig Entry</h3>

<div>

<a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
<a href="report_daily.php" class="btn btn-primary btn-sm">Daily Report</a>
<a href="logout.php" class="btn btn-danger btn-sm">Logout</a>

</div>

</div>


<div class="card-box">

<form action="update_entry.php" method="POST">

<input type="hidden" name="id" value="<?=$row['id']?>">


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Date</label>
<input type="date" name="date" class="form-control" value="<?=$row['date']?>" required>
</div>

<div class="col-md-6">
<label class="form-label">Rig</label>
<input type="text" name="rig" class="form-control" value="<?=$row['rig']?>" required>
</div>

</div>


<h5 class="mt-4 mb-3">Rig Hours</h5>


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Operating Hours</label>
<input type="number" step="0.01" name="operating" class="form-control" value="<?=$row['operating_hours']?>">
</div>

<div class="col-md-6">
<label class="form-label">Standby Hours</label>
<input type="number" step="0.01" name="standby" class="form-control" value="<?=$row['standby_hours']?>">
</div>

</div>


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Breakdown Hours</label>
<input type="number" step="0.01" name="breakdown" class="form-control" value="<?=$row['breakdown_hours']?>">
</div>

<div class="col-md-6">
<label class="form-label">ILM Hours</label>
<input type="number" step="0.01" name="ilm" class="form-control" value="<?=$row['ilm_hours']?>">
</div>

</div>


<div class="row mb-3">

<div class="col-md-6">
<label class="form-label">Zero Rate Hours</label>
<input type="number" step="0.01" name="zero" class="form-control" value="<?=$row['zero_rate_hours']?>">
</div>

<div class="col-md-6">
<label class="form-label">Reason</label>
<input type="text" name="reason" class="form-control" value="<?=$row['reason']?>">
</div>

</div>


<div class="row mb-4">

<div class="col-md-6">
<label class="form-label">Status</label>
<input type="text" name="status" class="form-control" value="<?=$row['status']?>">
</div>

</div>


<button type="submit" class="btn btn-success">Update Entry</button>

<a href="report_daily.php" class="btn btn-secondary">Cancel</a>

</form>

</div>

</div>

</body>
</html>