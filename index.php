<?php
session_start();
include('includes/db.php');

// Fetch latest 5 upcoming events for slider
$slider_events = mysqli_query($conn, "
    SELECT * FROM upcoming_events 
    WHERE event_date >= CURDATE()
    ORDER BY RAND() 
    LIMIT 5
");

// Fetch all upcoming events for cards
$all_events = mysqli_query($conn, "SELECT * FROM upcoming_events ORDER BY event_date ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Globmusk | Corporate Ticketing</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* --- Base --- */
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f8f7ff;color:#333;line-height:1.6;}
a{text-decoration:none;color:inherit;}

/* --- Header --- */
header{display:flex;justify-content:space-between;align-items:center;padding:20px 5%;background:white;position:fixed;width:100%;top:0;z-index:1000;box-shadow:0 2px 15px rgba(0,0,0,0.05);}
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
nav{display:flex;gap:20px;align-items:center;}
nav a{font-weight:500;transition:0.3s;}
nav a:hover{color:#6a0dad;}
.btn{padding:10px 20px;border-radius:25px;border:none;cursor:pointer;font-weight:600;transition:0.3s;}
.login-btn{border:2px solid #6a0dad;color:#6a0dad;background:white;}
.login-btn:hover{background:#6a0dad;color:white;}
.start-btn{background:#6a0dad;color:white;}
.start-btn:hover{opacity:0.9;}

/* Desktop nav */
nav.desktop-nav {
    display: flex;
    gap: 20px;
    align-items: center;
}

@media(max-width:768px){
    nav.desktop-nav {
        display: none;
    }
}

/* --- Hamburger --- */
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
.hamburger.active div:nth-child(1){transform: rotate(45deg) translate(5px,5px);}
.hamburger.active div:nth-child(2){opacity:0;}
.hamburger.active div:nth-child(3){transform: rotate(-45deg) translate(6px,-6px);}
nav.mobile-nav{
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
    transition:0.3s;
}
nav.mobile-nav.active{right:0;}

/* --- Hero --- */
.hero{
    height:90vh;display:flex;justify-content:center;align-items:center;text-align:center;
    background:linear-gradient(135deg, rgba(106,13,173,0.8), rgba(138,43,226,0.8)), url('assets/hero-bg.jpg') no-repeat center/cover;
    color:white;position:relative;
}
.hero h1{font-size:3rem;margin-bottom:20px;}
.hero p{font-size:1.2rem;margin-bottom:30px;}
.hero .btn{font-size:1rem;}

/* --- Slider --- */
.slider-container{position:relative;max-width:1000px;margin:50px auto 0 auto;overflow:hidden;border-radius:15px;}
.slides-wrapper{display:flex;transition:0.5s ease;}
.slide{min-width:100%;box-sizing:border-box;position:relative;overflow:hidden;}
.slide img {
    width: 100%;
    height: 450px;
    object-fit: contain;
    background: #f4f4f4;
}
.overlay{position:absolute;bottom:0;width:100%;padding:20px;background:linear-gradient(to top, rgba(0,0,0,0.8), transparent);color:white;}
.overlay h3{font-size:1.8rem;margin-bottom:8px;}
.overlay p{font-size:1rem;margin-bottom:10px;}
.overlay .prices span{display:inline-block;margin-right:15px;font-size:0.95rem;background:rgba(255,255,255,0.15);padding:5px 10px;border-radius:8px;}
.overlay a .btn{margin-top:10px;padding:10px 20px;border-radius:8px;}

/* Slider navigation arrows */
.arrow{position:absolute;top:50%;transform:translateY(-50%);font-size:2rem;color:white;background:rgba(0,0,0,0.4);padding:10px;border-radius:50%;cursor:pointer;z-index:10;user-select:none;}
.arrow.left{left:15px;}
.arrow.right{right:15px;}
.arrow:hover{background:rgba(0,0,0,0.7);}

/* Dots */
.dots-container{text-align:center;margin-top:20px;}
.dot{height:14px;width:14px;margin:0 6px;background:#bbb;border-radius:50%;display:inline-block;cursor:pointer;transition:0.3s;}
.active-dot{background:#6a0dad;}

/* --- Event Cards --- */
.events-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;padding:40px 5%;}
.event-card{background:white;border-radius:15px;overflow:hidden;box-shadow:0 5px 20px rgba(0,0,0,0.1);transition:transform 0.3s, box-shadow 0.3s;display:flex;flex-direction:column;}
.event-card:hover{transform:translateY(-5px);box-shadow:0 10px 30px rgba(0,0,0,0.15);}
.event-card img{width: 100%; height: 450px; object-fit: contain; background:#f4f4f4;}
.event-card .event-info{padding:20px;display:flex;flex-direction:column;gap:10px;}
.event-card .event-info h3{color:#6a0dad;font-size:1.2rem;}
.event-card .event-info .event-meta{font-size:0.95rem;color:#555;}
.event-card .event-info .event-prices span{display:block;font-size:0.95rem;margin:3px 0;}
.event-card .btn{margin-top:10px;font-size:1rem;background:#6a0dad;color:white;border-radius:25px;cursor:pointer;}

/* --- Features --- */
.features{padding:80px 5%;text-align:center;background:#f4f4f4;}
.feature-box{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:30px;}
.card{background:white;padding:30px;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);}
.card h3{font-size:1.2rem;margin-bottom:10px;color:#6a0dad;}


    /*------whatsapp----*/
/* floating button */
.wa-float-btn {
    width: 60px;
    height: 60px;
    background: #25D366;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

/* chat box */
.wa-body {
    width: 250px;
    background: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 10px;
    display: none;
    box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

.wa-header {
    background: #25D366;
    color: white;
    padding: 10px;
    border-radius: 10px 10px 0 0;
    display: none;
    justify-content: space-between;
    align-items: center;
}

.wa-btn {
    display: block;
    margin-top: 10px;
    background: #25D366;
    color: white;
    text-align: center;
    padding: 10px;
    border-radius: 5px;
    text-decoration: none;
}

/* Animation */
.wa-body, .wa-header {
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(10px);}
    to {opacity: 1; transform: translateY(0);}
}

/* --- Footer --- */
footer{background:#6a0dad;color:white;text-align:center;padding:30px;margin-top:50px;}
footer a{color:white;margin:0 5px;}
footer a:hover{text-decoration:underline;}

/* --- Responsive --- */
@media(max-width:768px){
    .hero h1{font-size:2rem;}
    .hero p{font-size:1rem;}
    .hamburger{display:flex;}
    nav:not(.mobile-nav){display:none;}
}
</style>
</head>
<body>

<header>
    <a href="index.php" class="logo">
    <img src="assets/logo.JPG" alt="Globmusk Logo">
    <span>Globmusk</span>
</a>
    <nav class="desktop-nav">
    <a href="index.php">Home</a>
    <a href="events.php">Events</a>
    <a href="admin/dashboard.php">Admin</a>
    <a href="auth/login.php" class="login-btn btn">Login</a>
    <a href="auth/register.php" class="start-btn btn">Get Started</a>
</nav>
    <div class="hamburger" onclick="toggleHamburger(this)">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <nav class="mobile-nav">
        <a href="index.php">Home</a>
        <a href="events.php">Events</a>
        <a href="admin/dashboard.php">Admin</a>
        <a href="auth/login.php" class="login-btn btn">Login</a>
        <a href="auth/register.php" class="start-btn btn">Get Started</a>
    </nav>
</header>

<section class="hero">
    <div>
        <h1>Discover & Book Amazing Events</h1>
        <p>Fast, Secure & Corporate-Ready Ticketing</p>
        <a href="auth/register.php"><button class="btn start-btn">Get Started</button></a>
        <a href="auth/login.php"><button class="login-btn btn">Login</button></a>
    </div>
</section>

<section class="upcoming">
    <h2 style="
    text-align: center;
    margin: 50px 0 30px 0; /* Optional: top and bottom spacing */
    color: #6a0dad;        /* Optional: make them match your theme */
">Featured Events</h2>
    <div class="slider-container">
        <div class="slides-wrapper">
        <?php if(mysqli_num_rows($slider_events) > 0): ?>
            <?php while($e = mysqli_fetch_assoc($slider_events)): ?>
                <div class="slide">
                    <img src="<?php echo 'uploads/events/'.htmlspecialchars($e['image']); ?>" alt="<?php echo htmlspecialchars($e['title']); ?>">
                    <div class="overlay">
                        <h3><?php echo htmlspecialchars($e['title']); ?></h3>
                        <p><i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($e['location']); ?> | <i class="fa fa-calendar-alt"></i> <?php echo htmlspecialchars($e['event_date']); ?></p>
                       <div class="prices">
    <span>Early Bird: ₦<?php echo number_format($e['early_bird_price']); ?></span>
    <span>Walk-in: ₦<?php echo number_format($e['walk_in_price']); ?></span>
    <span>VIP: ₦<?php echo number_format($e['vip_price']); ?></span>
</div>

<div class="prices">
    <span>Table for 4: ₦<?php echo number_format($e['table_4_price']); ?></span>
    <span>Table for 5: ₦<?php echo number_format($e['table_5_price']); ?></span>
    <span>Table for 6: ₦<?php echo number_format($e['table_6_price']); ?></span>
</div>
                        <a href="auth/login.php"><button class="btn start-btn">Get Event ticket</button></a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No upcoming events yet.</p>
        <?php endif; ?>
        </div>
        <i class="fas fa-chevron-left arrow left" onclick="prevSlide()"></i>
        <i class="fas fa-chevron-right arrow right" onclick="nextSlide()"></i>
    </div>
    <div class="dots-container">
        <?php for($i=0; $i<mysqli_num_rows($slider_events); $i++): ?>
            <span class="dot" onclick="currentSlide(<?php echo $i; ?>)"></span>
        <?php endfor; ?>
    </div>

    <h2 style="
    text-align: center;
    margin: 50px 0 30px 0; /* Optional: top and bottom spacing */
    color: #6a0dad;        /* Optional: make them match your theme */
">All Upcoming Events</h2>
    <div class="events-cards">
        <?php mysqli_data_seek($all_events,0); ?>
        <?php while($e = mysqli_fetch_assoc($all_events)): ?>
            <div class="event-card">
                <img src="<?php echo 'uploads/events/'.htmlspecialchars($e['image']); ?>" alt="<?php echo htmlspecialchars($e['title']); ?>">
                <div class="event-info">
                    <h3><?php echo htmlspecialchars($e['title']); ?></h3>
                    <p class="event-meta"><i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($e['location']); ?> | <i class="fa fa-calendar-alt"></i> <?php echo htmlspecialchars($e['event_date']); ?></p>
                    <div class="event-prices">
    <span>Early Bird: ₦<?php echo number_format($e['early_bird_price']); ?></span>
    <span>Walk-in: ₦<?php echo number_format($e['walk_in_price']); ?></span>
    <span>VIP: ₦<?php echo number_format($e['vip_price']); ?></span>
    <span>Table 4: ₦<?php echo number_format($e['table_4_price']); ?></span>
    <span>Table 5: ₦<?php echo number_format($e['table_5_price']); ?></span>
    <span>Table 6: ₦<?php echo number_format($e['table_6_price']); ?></span>
</div>
                    <a href="auth/login.php"><button class="btn start-btn">Get Event Ticket</button></a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<section class="features">
    <h2>Why Choose Globmusk?</h2>
    <div class="feature-box">
        <div class="card">
            <h3>🎫 Easy Booking</h3>
            <p>Book tickets in seconds from any device.</p>
        </div>
        <div class="card">
            <h3>💳 Secure Payment</h3>
            <p>Fully encrypted payments & corporate-grade security.</p>
        </div>
        <div class="card">
            <h3>📱 QR Tickets</h3>
            <p>Instant QR code tickets for fast entry.</p>
        </div>
    </div>
</section>

<footer>
    <p>© 2026 Globmusk. All rights reserved.</p>
    
</footer>

<script>
/* Hamburger toggle */
function toggleHamburger(el){
    el.classList.toggle('active');
    document.querySelector('nav.mobile-nav').classList.toggle('active');
}

/* Slider functionality */
let slideIndex = 0;
const slidesWrapper = document.querySelector('.slides-wrapper');
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function updateSlider() {
    slidesWrapper.style.transform = `translateX(-${slideIndex * 100}%)`;
    dots.forEach(dot => dot.classList.remove('active-dot'));
    dots[slideIndex].classList.add('active-dot');
}

function nextSlide() {
    slideIndex = (slideIndex + 1) % slides.length;
    updateSlider();
}

function prevSlide() {
    slideIndex = (slideIndex - 1 + slides.length) % slides.length;
    updateSlider();
}

function currentSlide(n) {
    slideIndex = n;
    updateSlider();
}

// Auto-slide
setInterval(nextSlide, 5000);

// Touch swipe support
let startX = 0;
slidesWrapper.addEventListener('touchstart', e => { startX = e.touches[0].clientX; });
slidesWrapper.addEventListener('touchend', e => {
    let endX = e.changedTouches[0].clientX;
    if(endX < startX - 50) nextSlide();
    else if(endX > startX + 50) prevSlide();
});

// Initialize
updateSlider();
</script>

    <div class="wa-widget">
    <div class="wa-header">
        <strong>Globmusk Support</strong>
        <span onclick="toggleWA()" style="cursor:pointer;">✕</span>
    </div>

    <div class="wa-body">
        <p>Hi 👋<br>How can we help you today?</p>

        <a href="https://wa.me/2349166524854?text=Hi%20Globmusk%20team%2C%20I%20need%20help"
           target="_blank"
           class="wa-btn">
            Start Chat on WhatsApp
        </a>
    </div>

    <div class="wa-float-btn" onclick="toggleWA()">
       <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32" fill="white">
    <path d="M19.11 17.47c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.13-.42-2.15-1.34-.79-.7-1.32-1.57-1.47-1.84-.15-.27-.02-.42.12-.56.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.48-.84-2.03-.22-.53-.45-.46-.61-.47h-.52c-.18 0-.48.07-.73.34-.25.27-.95.93-.95 2.26 0 1.33.98 2.61 1.12 2.79.14.18 1.91 2.92 4.63 4.1.65.28 1.16.45 1.56.58.65.21 1.24.18 1.71.11.52-.08 1.6-.65 1.82-1.28.23-.63.23-1.17.16-1.28-.07-.11-.25-.18-.52-.32z"/>
    <path d="M16 3C8.83 3 3 8.83 3 16c0 2.3.6 4.55 1.74 6.53L3 29l6.7-1.7C11.5 28.4 13.72 29 16 29c7.17 0 13-5.83 13-13S23.17 3 16 3zm0 23.5c-2.08 0-4.1-.56-5.86-1.62l-.42-.25-3.98 1.01 1.06-3.88-.27-.4A10.4 10.4 0 0 1 5.5 16C5.5 10.2 10.2 5.5 16 5.5S26.5 10.2 26.5 16 21.8 26.5 16 26.5z"/>
</svg>
    </div>
</div>
</body>
</html>
