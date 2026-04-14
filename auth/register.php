<?php
session_start();
include('../includes/db.php');

// Autoload PHPMailer classes
require '../lib/phpmailer/src/Exception.php';
require '../lib/phpmailer/src/PHPMailer.php';
require '../lib/phpmailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];

if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Password check
    if($password !== $confirm){
        $errors[] = "Passwords do not match!";
    }

    // Check for existing email
    $check = mysqli_query($conn,"SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $errors[] = "Email already exists!";
    }

    if(empty($errors)){
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $insert = mysqli_query($conn,"INSERT INTO users (name,email,password) VALUES ('$name','$email','$hashed')");

        if($insert){
            // ✅ Send welcome email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.example.com'; // Replace with your SMTP host
                $mail->SMTPAuth = true;
                $mail->Username = 'your@email.com'; // Replace with your SMTP username
                $mail->Password = 'yourpassword'; // Replace with your SMTP password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('no-reply@globmusk.com', 'Globmusk');
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to Globmusk';
                $mail->Body    = "Hi $name, <br><br>Thank you for registering with Globmusk.";

                $mail->send();
            } catch (Exception $e) {
                // Optional: log $mail->ErrorInfo
            }

            // ✅ Redirect to login.php after registration
            header("Location: login.php?registered=1");
            exit();
        } else {
            $errors[] = "Database error: ".mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register | Globmusk</title>
<style>
    *{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    body{background:#f8f7ff;color:#333;}
    header{display:flex;justify-content:space-between;align-items:center;padding:15px 8%;background:rgba(255,255,255,0.9);position:fixed;width:100%;top:0;backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);z-index:1000;}
    
    header .logo{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:24px;
    font-weight:bold;
    color:#6a0dad;
}

/* Logo image */
header .logo img{
    height:40px;
    width:auto;
    object-fit:contain;
}

/* Optional: hover effect */
header .logo:hover img{
    transform:scale(1.05);
    transition:0.3s;
}
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
   <a href="../index.php" class="logo">
    <img src="../assets/logo.JPG" alt="Globmusk Logo">
    <span>Globmusk</span>
</a>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
    </nav>
</header>

<div class="container">
    <h2>Register</h2>

    <?php
        if(!empty($errors)){
            foreach($errors as $e){
                echo "<div class='error'>$e</div>";
            }
        }
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit" name="register">Register</button>
    </form>
</div>

<footer>
    <p>© 2026 Globmusk. All rights reserved.</p>
</footer>

</body>
</html>