<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../includes/db.php');

$errors = [];

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $errors[] = "All fields are required.";
    } else {
        $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if(mysqli_num_rows($query) == 1){
            $user = mysqli_fetch_assoc($query);
            if(password_verify($password, $user['password'])){
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                header("Location: ../user/profile.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Email not registered.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Globmusk</title>

<style>
/* ---------------- RESET & BASE ---------------- */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f8f7ff;color:#333;line-height:1.6;}

/* ---------------- HEADER ---------------- */
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 8%;
    background:rgba(255,255,255,0.95);
    position:fixed; top:0; width:100%; z-index:1000;
    backdrop-filter:blur(10px);
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}
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
nav{
    display:flex;
    align-items:center;
    gap:20px;
}
nav a{
    text-decoration:none;
    color:#555;
    font-weight:500;
    transition:0.3s;
}
nav a:hover{color:#6a0dad;}

/* ---------------- HAMBURGER ---------------- */
.hamburger{
    display:none;
    flex-direction:column;
    justify-content:space-between;
    width:30px;
    height:22px;
    cursor:pointer;
    z-index:1100;
}
.hamburger div{
    width:100%;
    height:4px;
    background:#6a0dad;
    border-radius:3px;
    transition:0.4s;
}

/* Hamburger active animation */
.hamburger.active div:nth-child(1){transform: rotate(45deg) translate(5px,5px);}
.hamburger.active div:nth-child(2){opacity:0;}
.hamburger.active div:nth-child(3){transform: rotate(-45deg) translate(6px,-6px);}

/* Mobile nav */
@media(max-width:768px){
    nav{
        position:fixed;
        top:70px;
        right:-250px;
        width:200px;
        background:white;
        flex-direction:column;
        gap:15px;
        padding:20px;
        border-radius:10px 0 0 10px;
        box-shadow:0 5px 15px rgba(0,0,0,0.1);
        transition:0.4s;
    }
    nav.active{right:0;}
    .hamburger{display:flex;}
}

/* ---------------- CONTAINER ---------------- */
.container{
    max-width:400px;
    margin:150px auto;
    padding:30px;
    background:white;
    border-radius:15px;
    box-shadow:0 5px 20px rgba(0,0,0,0.1);
    text-align:center;
}
h2{color:#6a0dad;margin-bottom:20px;}
input{
    width:100%;
    padding:12px;
    margin:8px 0;
    border:1px solid #ccc;
    border-radius:25px;
}
button{
    width:100%;
    padding:12px;
    margin-top:10px;
    background:#6a0dad;
    color:white;
    border:none;
    border-radius:25px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}
button:hover{background:#4b0082;}
.error{color:red;margin:10px 0;text-align:center;}
.success{color:green;margin:10px 0;text-align:center;}

/* ---------------- FOOTER ---------------- */
footer{
    background: linear-gradient(135deg,#6a0dad,#8a2be2);
    color: white;
    text-align: center;
    padding: 40px 20px;
    font-weight: 500;
    margin-top: 40px;
    box-shadow: 0 -5px 15px rgba(0,0,0,0.1);
}
footer p{font-size:16px;margin-bottom:10px;}
footer .footer-links a{
    color:white;
    margin:0 15px;
    text-decoration:none;
    transition:0.3s;
}
footer .footer-links a:hover{color:#f0e;}

/* ---------------- RESPONSIVE ---------------- */
@media(max-width:500px){.container{margin:120px 20px;padding:20px;}}
</style>
</head>
<body>

<header>
     <a href="../index.php" class="logo">
    <img src="../assets/logo.JPG" alt="Globmusk Logo">
    <span>Globmusk</span>
</a>
    <div class="hamburger" onclick="toggleHamburger(this)">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
        <a href="register.php">Register</a>
    </nav>
</header>

<div class="container">
    <h2>Login</h2>

    <?php
    if(!empty($errors)){
        foreach($errors as $e){
            echo "<div class='error'>$e</div>";
        }
    }
    ?>

    <form method="POST">
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>

    <!-- Forgot Password Link Added -->
    <p style="margin-top:10px;">
        <a href="forgot_password.php" style="color:#6a0dad;">Forgot Password?</a>
    </p>

    <p style="margin-top:15px;">Don't have an account? <a href="register.php" style="color:#6a0dad;">Register</a></p>
</div>

<footer>
    <p>© 2026 Globmusk. All rights reserved.</p>
    <div class="footer-links">
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
        <a href="../contact.php">Contact</a>
    </div>
</footer>

<script>
function toggleHamburger(el){
    el.classList.toggle('active');
    document.querySelector('nav').classList.toggle('active');
}
</script>

</body>
</html>