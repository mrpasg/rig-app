<?php
include "auth.php";
include "config.php";

$id=$_GET['id'];

$q=$conn->query("SELECT * FROM rig_daily_log WHERE id='$id'");
$row=$q->fetch_assoc();

if(isset($_POST['update'])){

$operating=$_POST['operating'];
$standby=$_POST['standby'];
$breakdown=$_POST['breakdown'];
$ilm=$_POST['ilm'];
$zero=$_POST['zero'];

$conn->query("
UPDATE rig_daily_log SET
operating_hours='$operating',
standby_hours='$standby',
breakdown_hours='$breakdown',
ilm_hours='$ilm',
zero_rate_hours='$zero'
WHERE id='$id'
");

header("Location: report_daily.php");
exit;

}
?>

<form method="POST">

Operating
<input type="number" name="operating" value="<?=$row['operating_hours']?>">

Standby
<input type="number" name="standby" value="<?=$row['standby_hours']?>">

Breakdown
<input type="number" name="breakdown" value="<?=$row['breakdown_hours']?>">

ILM
<input type="number" name="ilm" value="<?=$row['ilm_hours']?>">

Zero
<input type="number" name="zero" value="<?=$row['zero_rate_hours']?>">

<button name="update">Update</button>

</form>
