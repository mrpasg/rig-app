<!DOCTYPE html>
<html>
<head>
<title>Rig Daily Entry</title>
</head>

<body>

<h2>Rig Daily Activity Entry</h2>

<form action="save_entry.php" method="POST">

Date:
<input type="date" name="date" required><br><br>

Rig:
<select name="rig">
<option>PPE-1</option>
<option>PPE-2</option>
<option>PPE-3</option>
<option>PPE-4</option>
<option>PPE-5</option>
</select><br><br>

Operating Hours
<input type="number" step="0.1" name="operating"><br><br>

Standby Hours
<input type="number" step="0.1" name="standby"><br><br>

Breakdown Hours
<input type="number" step="0.1" name="breakdown"><br><br>

ILM Hours
<input type="number" step="0.1" name="ilm"><br><br>

Zero Rate Hours
<input type="number" step="0.1" name="zero"><br><br>

Reason
<textarea name="reason"></textarea><br><br>

Status
<select name="status">
<option>Open</option>
<option>Closed</option>
</select><br><br>

<button type="submit">Save Entry</button>

</form>

</body>
</html>
