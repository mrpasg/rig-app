<?php
if($_SESSION['role']!='admin'){
echo "Access denied";
exit;
}
include "auth.php";
include "config.php";

if($_POST){

$username=$_POST['username'];
$password=md5($_POST['password']);
$role=$_POST['role'];

$conn->query("
INSERT INTO users (username,password,role)
VALUES ('$username','$password','$role')
");

echo "User created successfully";

}
?>

<form method="post">

Username
<input type="text" name="username">

Password
<input type="password" name="password">

Role
<select name="role">
<option value="user">User</option>
<option value="admin">Admin</option>
</select>

<button>Create User</button>

</form>
