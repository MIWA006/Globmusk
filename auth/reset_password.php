<?php
session_start();
include('../includes/db.php');

$errors = [];
$success = [];

if(!isset($_GET['token'])){
    die("Invalid request");
}

$token = mysqli_real_escape_string($conn, $_GET['token']);
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE reset_token='$token' AND token_expiry > NOW()");

if(mysqli_num_rows($user_query) == 0){
    die("Token expired or invalid");
}

$user = mysqli_fetch_assoc($user_query);

if(isset($_POST['reset_password'])){
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if($password !== $confirm){
        $errors[] = "Passwords do not match!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed', reset_token=NULL, token_expiry=NULL WHERE id=".$user['id']);
        $success[] = "Password reset successfully! <a href='login.php'>Login</a>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password | Globmusk</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f8f7ff;color:#333;}
header{display:flex;justify-content:space-between;align-items:center;padding:15px 8%;background:rgba(255,255,255,0.9);position:fixed;width:100%;top:0;backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);z-index:1000;}
.logo{font-size:24px;font-weight:bold;color:#6a0dad;}
nav a{margin-left:20px;text-decoration:none;color:#555;font-weight:500;}
nav a:hover{color:#6a0dad;}
footer{background:#6a0dad;color:white;text-align:center;padding:20px;margin-top:40px;}
.container{max-width:450px;margin:150px auto;padding:30px;background:white;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);}
h2{text-align:center;margin-bottom:20px;color:#6a0dad;}
input{width:100%;padding:12px;margin:8px 0;border:1px solid #ccc;border-radius:8px;}
button{width:100%;padding:12px;margin-top:10px;background:#6a0dad;color:white;border:none;border-radius:25px;font-weight:600;cursor:pointer;transition:0.3s;}
button:hover{background:#4b0082;}
.error{color:red;margin:10px 0;text-align:center;}
.success{color:green;margin:10px 0;text-align:center;}
a{color:#4b0082;text-decoration:none;}
a:hover{color:#6a0dad;}
@media(max-width:500px){.container{margin:120px 20px;padding:20px;}}
</style>
</head>
<body>

<header>
    <div class="logo">Globmusk</div>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
    </nav>
</header>

<div class="container">
<h2>Reset Password</h2>

<?php
foreach($errors as $e){ echo "<div class='error'>$e</div>"; }
foreach($success as $s){ echo "<div class='success'>$s</div>"; }
?>

<form method="POST">
    <input type="password" name="password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit" name="reset_password">Reset Password</button>
</form>
</div>

<footer>
<p>© 2026 Globmusk. All rights reserved.</p>
</footer>
</body>
</html>