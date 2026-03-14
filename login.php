<?php
session_start();
include "config.php";

if(isset($_POST['login'])){

$username = $_POST['username'];
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
$error="Invalid username or password";
}

}
?>

<!DOCTYPE html>
<html>

<head>

<title>Rig Monitoring System</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{

height:100vh;
margin:0;
font-family:Segoe UI;

background-image:url('oil_bg.jpg');
background-size:cover;
background-position:center;
background-repeat:no-repeat;

display:flex;
align-items:center;
justify-content:center;

}

/* dark overlay */

body:before{
content:"";
position:absolute;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.65);
}

/* login card */

.login-card{

position:relative;
width:380px;
background:white;
padding:35px;
border-radius:12px;
box-shadow:0 10px 25px rgba(0,0,0,0.4);

}

/* logo */

.logo{

text-align:center;
margin-bottom:15px;

}

.logo img{

height:60px;

}

.system-title{

text-align:center;
font-weight:600;
margin-bottom:20px;
color:#0b3d6d;

}

.footer{

text-align:center;
margin-top:15px;
font-size:12px;
color:#888;

}

</style>

</head>

<body>

<div class="login-card">

<div class="logo">

<img src="logo.png">

</div>

<div class="system-title">

Rig Monitoring System

</div>

<form method="POST">

<input type="text" name="username" class="form-control mb-3" placeholder="Username" required>

<input type="password" name="password" class="form-control mb-3" placeholder="Password" required>

<button name="login" class="btn btn-primary w-100">

Login

</button>

<?php if(isset($error)){ ?>

<div class="text-danger mt-2 text-center">

<?=$error?>

</div>

<?php } ?>

</form>

<div class="footer">

KRISS DRILLING PVT. LTD.

</div>

</div>

</body>

</html>