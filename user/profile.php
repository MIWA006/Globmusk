<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include('../includes/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$result = mysqli_query($conn, "SELECT name,email FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);
$user_name = $user['name'];
$user_email = $user['email'];

// Fetch tickets
$tickets_query = mysqli_query($conn, "
    SELECT t.*, e.title, e.event_date
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id='$user_id'
    ORDER BY t.purchase_date DESC
");

// Ticket summary
$summary = ['total'=>0,'paid'=>0,'pending'=>0];
while($ticket = mysqli_fetch_assoc($tickets_query)){
    $summary['total']++;
    if($ticket['payment_status']=='paid') $summary['paid']++;
    else $summary['pending']++;
}
// Re-run query to display tickets
$tickets_query = mysqli_query($conn, "
    SELECT t.*, e.title, e.event_date
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id='$user_id'
    ORDER BY t.purchase_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile | Globmusk</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f4f4f9;color:#333;}
a{text-decoration:none;}

/* ---------------- HEADER ---------------- */
header{
    display:flex;justify-content:space-between;align-items:center;
    padding:15px 8%;
    background:rgba(255,255,255,0.95);
    position:fixed;
    top:0;
    width:100%;
    z-index:1000;
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
    transition:0.4s;
}
nav a{color:#555;font-weight:500;transition:0.3s;}
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
    }
    nav.active{right:0;}
    .hamburger{display:flex;}
}

/* ---------------- CONTAINER ---------------- */
.container{max-width:1100px;margin:140px auto 50px auto;padding:20px;}

/* ---------------- PROFILE CARD ---------------- */
.profile-card{
    display:flex;justify-content:space-between;align-items:center;
    background:white;padding:25px;border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.05);margin-bottom:30px;
}
.profile-info{display:flex;align-items:center;gap:20px;}
.profile-info .avatar{
    width:80px;height:80px;border-radius:50%;background:#6a0dad;color:white;
    display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:bold;
}
.profile-info div h2{color:#6a0dad;margin-bottom:5px;}
.profile-info div p{color:#555;margin:0;}
.logout-btn{
    padding:12px 25px;background:#e74c3c;color:white;
    border:none;border-radius:25px;font-weight:600;cursor:pointer;transition:0.3s;
}
.logout-btn:hover{background:#c0392b;}

/* ---------------- SUMMARY CARDS ---------------- */
.summary-cards{display:flex;flex-wrap:wrap;gap:20px;margin-bottom:30px;}
.summary-card{
    flex:1;min-width:200px;background:#6a0dad;color:white;
    padding:20px;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.05);
    text-align:center;transition:0.3s;
}
.summary-card:hover{transform:translateY(-5px);}
.summary-card h3{font-size:28px;margin-bottom:10px;}
.summary-card p{font-size:16px;opacity:0.9;}

/* ---------------- TICKETS GRID ---------------- */
.tickets-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(280px,1fr));
    gap:20px;
}
.ticket-card{
    background:white;border-radius:15px;padding:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);transition:0.3s;
}
.ticket-card:hover{transform:translateY(-5px);}
.ticket-card h3{color:#6a0dad;margin-bottom:10px;}
.ticket-card p{margin-bottom:5px;}
.ticket-card a{
    display:inline-block;padding:10px 15px;background:#6a0dad;color:white;
    border-radius:25px;text-decoration:none;transition:0.3s;
}
.ticket-card a:hover{background:#4b0082;}

/* ---------------- FOOTER ---------------- */
footer{background:#6a0dad;color:white;text-align:center;padding:20px;margin-top:40px;border-top:3px solid #4b0082;}

/* ---------------- RESPONSIVE ---------------- */
@media(max-width:768px){
    .container{margin:160px 20px;padding:15px;}
    .profile-card{flex-direction:column;align-items:flex-start;gap:15px;}
    .logout-btn{margin-top:10px;}
    .summary-cards{flex-direction:column;}
}
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
        <a href="profile.php">Dashboard</a>
        <a href="events.php">Events</a>
        <a href="tickets.php">My Tickets</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <!-- Profile Card -->
    <div class="profile-card">
        <div class="profile-info">
            <div class="avatar"><?= strtoupper($user_name[0]) ?></div>
            <div>
                <h2><?= htmlspecialchars($user_name) ?></h2>
                <p><?= htmlspecialchars($user_email) ?></p>
            </div>
        </div>
        <form action="../auth/logout.php" method="POST">
            <button class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3><?= $summary['total'] ?></h3>
            <p>Total Tickets</p>
        </div>
        <div class="summary-card">
            <h3><?= $summary['paid'] ?></h3>
            <p>Paid Tickets</p>
        </div>
        <div class="summary-card">
            <h3><?= $summary['pending'] ?></h3>
            <p>Pending Tickets</p>
        </div>
    </div>

    <!-- Tickets Grid -->
    <h2 style="margin-bottom:20px;">My Tickets</h2>
    <div class="tickets-grid">
        <?php if(mysqli_num_rows($tickets_query) > 0): ?>
            <?php while($ticket = mysqli_fetch_assoc($tickets_query)): ?>
                <div class="ticket-card">
                    <h3><?= htmlspecialchars($ticket['title']) ?></h3>
                    <p><strong>Type:</strong> <?= strtoupper($ticket['ticket_type']) ?></p>
                    <p><strong>Seat/Table:</strong> <?= $ticket['seat_no'] ?></p>
                    <p><strong>Event Date:</strong> <?= $ticket['event_date'] ?></p>
                    <p><strong>Purchase Date:</strong> <?= $ticket['purchase_date'] ?></p>
                    <?php if($ticket['payment_status'] === 'paid'): ?>
                        <a href="view_ticket.php?ticket_id=<?= $ticket['id'] ?>">Download Ticket</a>
                    <?php else: ?>
                        <p style="color:#e67e22;font-weight:600;margin-top:10px;">Ticket Pending Approval</p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">You have no tickets yet. Check out <a href="events.php">events</a>!</p>
        <?php endif; ?>
    </div>
</div>

<footer>
    <p>© 2026 Globmusk. All rights reserved.</p>
</footer>

<script>
function toggleHamburger(el){
    el.classList.toggle('active');
    document.querySelector('nav').classList.toggle('active');
}
</script>

</body>
</html>