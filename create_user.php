<?php

include "auth.php";
include "config.php";

/* ONLY ADMIN CAN CREATE USERS */

if($_SESSION['role']!='admin'){
echo "<h3>Access Denied</h3>";
exit;
}

/* CREATE USER */

if($_SERVER["REQUEST_METHOD"]=="POST"){

$username = $conn->real_escape_string($_POST['username']);
$password = md5($_POST['password']);
$role = $conn->real_escape_string($_POST['role']);

$conn->query("
INSERT INTO users (username,password,role)
VALUES ('$username','$password','$role')
");

echo "<div style='color:green'>User created successfully</div>";

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Create User</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-4">

<h3>Create New User</h3>

<form method="post">

<div class="mb-3">

<label>Username</label>

<input type="text" name="username" class="form-control" required>

</div>

<div class="mb-3">

<label>Password</label>

<input type="password" name="password" class="form-control" required>

</div>

<div class="mb-3">

<label>Role</label>

<select name="role" class="form-control">

<option value="viewer">Viewer</option>
<option value="supervisor">Supervisor</option>
<option value="admin">Admin</option>

</select>

</div>

<button class="btn btn-success">Create User</button>

<a href="dashboard.php" class="btn btn-secondary">Back</a>

</form>

</div>

</body>
</html>