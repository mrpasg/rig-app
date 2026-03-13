<?php
session_start();
include "config.php";

$error = "";

if(isset($_POST['login'])){

$username = $conn->real_escape_string($_POST['username']);
$password = md5($_POST['password']);

$result = $conn->query("
SELECT * FROM users
WHERE username='$username'
AND password='$password'
");

if($result->num_rows > 0){

$user = $result->fetch_assoc();

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header("Location: dashboard.php");
exit;

}else{

$error = "Invalid username or password";

}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5" style="max-width:400px">

<h3 class="mb-3">Rig Monitoring Login</h3>

<form method="POST">

<input type="text" name="username" class="form-control mb-2" placeholder="Username" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button type="submit" name="login" class="btn btn-primary w-100">Login</button>

<?php
if($error!=""){
echo "<p class='text-danger mt-2'>$error</p>";
}
?>

</form>

</div>

</body>
</html>