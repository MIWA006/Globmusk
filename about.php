<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About | Globmusk</title>

    <style>
        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body{
            background:#f8f7ff;
            color:#333;
        }

        header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:15px 8%;
            background:rgba(255,255,255,0.9);
            position:fixed;
            width:100%;
            top:0;
            backdrop-filter:blur(10px);
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            z-index:1000;
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

        .btn{
            padding:10px 18px;
            border-radius:25px;
            border:none;
            cursor:pointer;
            font-weight:600;
        }

        .login-btn{
            background:transparent;
            border:2px solid #6a0dad;
            color:#6a0dad;
        }

        .start-btn{
            background:#6a0dad;
            color:white;
        }

        /* HERO */
        .hero{
            margin-top:80px;
            padding:100px 20px;
            text-align:center;
            background:linear-gradient(to right, #6a0dad, #8a2be2);
            color:white;
        }

        .hero h1{
            font-size:40px;
            margin-bottom:15px;
        }

        .hero p{
            font-size:18px;
        }

        /* CONTENT */
        .section{
            padding:80px 8%;
            text-align:center;
        }

        .section h2{
            color:#6a0dad;
            margin-bottom:20px;
        }

        .features{
            display:flex;
            gap:20px;
            flex-wrap:wrap;
            justify-content:center;
            margin-top:30px;
        }

        .card{
            background:white;
            padding:25px;
            border-radius:15px;
            width:280px;
            box-shadow:0 5px 15px rgba(0,0,0,0.05);
            transition:0.3s;
        }

        .card:hover{
            transform:translateY(-10px);
        }

        /* CTA */
        .cta{
            background:#6a0dad;
            color:white;
            padding:60px 20px;
        }

        .cta h2{
            margin-bottom:20px;
        }

        .cta .btn{
            background:white;
            color:#6a0dad;
        }

        /* FOOTER */
        footer{
            background:#4b0082;
            color:white;
            text-align:center;
            padding:20px;
        }

        @media(max-width:768px){
            .features{
                flex-direction:column;
                align-items:center;
            }
        }
    </style>
</head>
<body>

<header>
    <div class="logo">Globmusk</div>

    <nav>
        <a href="index.php">Home</a>
        <a href="about.php">About</a>
    </nav>

    <div>
        <a href="auth/login.php">
            <button class="btn login-btn">Login</button>
        </a>

        <a href="auth/register.php">
            <button class="btn start-btn">Get Started</button>
        </a>
    </div>
</header>

<!-- HERO -->
<section class="hero">
    <h1>About Globmusk</h1>
    <p>Your trusted platform for seamless event ticket booking.</p>
</section>

<!-- ABOUT TEXT -->
<section class="section">
    <h2>Who We Are</h2>
    <p>
        Globmusk is a modern ticketing platform designed to make event booking simple,
        fast, and secure. Whether it's concerts, conferences, or private events,
        we provide a seamless experience from discovery to entry.
    </p>
</section>

<!-- FEATURES -->
<section class="section">
    <h2>What We Offer</h2>

    <div class="features">
        <div class="card">
            <h3>Easy Booking</h3>
            <p>Quickly browse and book tickets in seconds.</p>
        </div>

        <div class="card">
            <h3>Secure Payments</h3>
            <p>Integrated with Paystack for safe and reliable transactions.</p>
        </div>

        <div class="card">
            <h3>QR Verification</h3>
            <p>Every ticket comes with a QR code for fast entry validation.</p>
        </div>
    </div>
</section>

<!-- MISSION -->
<section class="section">
    <h2>Our Mission</h2>
    <p>
        To revolutionize event access through technology by providing secure,
        efficient, and user-friendly ticketing solutions.
    </p>
</section>

<!-- CTA -->
<section class="cta">
    <h2>Ready to explore events?</h2>

    <a href="auth/register.php">
        <button class="btn">Get Started</button>
    </a>
</section>

<!-- FOOTER -->
<footer>
    <p>© 2026 Globmusk. All rights reserved.</p>
</footer>

</body>
</html>