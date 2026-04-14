<?php
session_start();
include('../includes/db.php');

// PHPMailer
require '../Lib/phpmailer/src/PHPMailer.php';
require '../Lib/phpmailer/src/SMTP.php';
require '../Lib/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];
$success = [];

if(isset($_POST['send_reset'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $user_query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($user_query) == 0){
        $errors[] = "Email not registered!";
    } else {
        $user = mysqli_fetch_assoc($user_query);
        $token = bin2hex(random_bytes(50));
        $expiry = date("Y-m-d H:i:s", strtotime('+1 hour'));

        mysqli_query($conn, "UPDATE users SET reset_token='$token', token_expiry='$expiry' WHERE email='$email'");

        // Send Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'your@email.com'; // Replace with your SMTP username
            $mail->Password = 'yourpassword';   // Replace with your SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your@email.com', 'Globmusk');
            $mail->addAddress($email, $user['name']);
            $mail->isHTML(true);
            $mail->Subject = 'Globmusk Password Reset';
            $mail->Body = "Hi {$user['name']},<br><br>
            Click the link below to reset your password:<br>
            <a href='http://localhost/globmusk/auth/reset_password.php?token=$token'>Reset Password</a><br><br>
            This link will expire in 1 hour.";

            $mail->send();
            $success[] = "Reset link sent to your email!";
        } catch (Exception $e) {
            $errors[] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password | Globmusk</title>
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
<h2>Forgot Password</h2>

<?php
foreach($errors as $e){ echo "<div class='error'>$e</div>"; }
foreach($success as $s){ echo "<div class='success'>$s</div>"; }
?>

<form method="POST">
    <input type="email" name="email" placeholder="Enter your registered email" required>
    <button type="submit" name="send_reset">Send Reset Link</button>
</form>
</div>

<footer>
<p>© 2026 Globmusk. All rights reserved.</p>
</footer>
</body>
</html>