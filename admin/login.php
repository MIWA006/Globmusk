<?php
session_start();
include('../includes/db.php');

$errors = [];

if(isset($_POST['login'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $errors[] = "All fields are required.";
    } else {
        $query = mysqli_query($conn, "SELECT * FROM admins WHERE email='$email'");

        if(mysqli_num_rows($query) > 0){
            $admin = mysqli_fetch_assoc($query);

            if(password_verify($password, $admin['password'])){
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Admin not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Globmusk</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:linear-gradient(135deg,#6a0dad,#a855f7);
    min-height:100vh;
    display:flex;
    flex-direction:column;
}

/* HEADER */
header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 8%;
    background:white;
    backdrop-filter:blur(10px);
    color:purple;
}

header .logo{
    display:flex;
    align-items:center;
    gap:10px;
    font-size:24px;
    font-weight:bold;
    color:purple;
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

nav a{
    margin-left:20px;
    text-decoration:none;
    color:purple;
    font-weight:500;
}

nav a:hover{
    text-decoration:underline;
}

/* LOGIN BOX */
.container{
    flex:1;
    display:flex;
    justify-content:center;
    align-items:center;
}

.form-box{
    background:white;
    padding:30px;
    border-radius:15px;
    width:100%;
    max-width:400px;
    box-shadow:0 10px 25px rgba(0,0,0,0.1);
    animation:fadeIn 0.5s ease;
}

@keyframes fadeIn{
    from{opacity:0;transform:translateY(20px);}
    to{opacity:1;transform:translateY(0);}
}

h2{
    text-align:center;
    margin-bottom:20px;
    color:#6a0dad;
}

input{
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #ccc;
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

.error{
    color:red;
    text-align:center;
    margin-bottom:10px;
}

/* FOOTER */
footer{
    background:rgba(0,0,0,0.2);
    color:white;
    text-align:center;
    padding:15px;
}
</style>
</head>
<body>

<!-- HEADER -->
<header>
     <a href="index.php" class="logo">
    <img src="../assets/logo.JPG" alt="Globmusk Logo">
    <span>Globmusk</span>
</a>
    <nav>
        <a href="../index.php">Home</a>
        <a href="../about.php">About</a>
    </nav>
</header>

<!-- LOGIN FORM -->
<div class="container">
    <div class="form-box">
        <h2>Admin Login</h2>

        <?php
        if(!empty($errors)){
            foreach($errors as $e){
                echo "<div class='error'>$e</div>";
            }
        }
        ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Admin Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit" name="login">Login</button>
        </form>

        <p style="text-align:center;margin-top:10px;">
            Admin access only
        </p>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <p>© 2026 Globmusk Admin Panel</p>
</footer>

</body>
</html>