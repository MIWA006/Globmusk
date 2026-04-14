<?php
session_start();
include('../includes/db.php');


// SECRET KEY
$SECRET_ADMIN_KEY = "GLOBMUSK@2026";

$errors = [];

if(isset($_POST['register'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $admin_key = $_POST['admin_key'];

    // VALIDATION
    if(empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($admin_key)){
        $errors[] = "All fields are required.";
    }

    if($password !== $confirm_password){
        $errors[] = "Passwords do not match.";
    }

    if($admin_key !== $SECRET_ADMIN_KEY){
        $errors[] = "Invalid admin secret key.";
    }

    // CHECK EXISTING ADMIN
    $check = mysqli_query($conn, "SELECT * FROM admins WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $errors[] = "Admin already exists.";
    }

    // INSERT INTO DB
    if(empty($errors)){
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        mysqli_query($conn, "INSERT INTO admins (name,email,password)
        VALUES ('$name','$email','$hashed_password')");

        // REDIRECT TO LOGIN
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Register | Globmusk</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f8f7ff;
    color:#333;
}

/* HEADER */
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 8%;
    background:white;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

.logo{
    font-size:24px;
    font-weight:bold;
    color:#6a0dad;
}

nav a{
    margin-left:20px;
    text-decoration:none;
    color:#555;
    font-weight:500;
}

nav a:hover{
    color:#6a0dad;
}

/* FORM */
.container{
    max-width:420px;
    margin:80px auto;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}

h2{
    text-align:center;
    color:#6a0dad;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
    transition:0.3s;
}

input:focus{
    border-color:#6a0dad;
    outline:none;
}

button{
    width:100%;
    padding:12px;
    background:#6a0dad;
    color:white;
    border:none;
    border-radius:25px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

button:hover{
    background:#4b0082;
}

/* ERROR */
.error{
    color:red;
    text-align:center;
    margin-bottom:10px;
}

/* FOOTER */
footer{
    margin-top:40px;
    background:#6a0dad;
    color:white;
    text-align:center;
    padding:20px;
}
</style>
</head>
<body>

<!-- HEADER -->
<header>
    <div class="logo">Globmusk</div>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
        <a href="login.php">Admin Login</a>
    </nav>
</header>

<!-- FORM -->
<div class="container">
    <h2>Admin Register</h2>

    <?php
    if(!empty($errors)){
        foreach($errors as $e){
            echo "<div class='error'>$e</div>";
        }
    }
    ?>

    <form method="POST">
        <input type="text" name="name" placeholder="Admin Name" required>
        <input type="email" name="email" placeholder="Admin Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <input type="text" name="admin_key" placeholder="Secret Admin Key" required>

        <button type="submit" name="register">Register</button>
    </form>

    <p style="text-align:center;margin-top:10px;">
        Already an admin? <a href="login.php">Login here</a>
    </p>
</div>

<!-- FOOTER -->
<footer>
    <p>© 2026 Globmusk Admin System</p>
</footer>

</body>
</html>