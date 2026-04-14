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

// Fetch all tickets for this user
$tickets_query = mysqli_query($conn, "
    SELECT t.*, e.title, e.event_date, e.description, e.location
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.user_id='$user_id'
    ORDER BY t.purchase_date DESC
");

// ----------------- SUMMARY DATA -----------------
$summary_sql = "
    SELECT 
        COUNT(*) AS total_tickets,
        SUM(CASE WHEN payment_status='pending' THEN 1 ELSE 0 END) AS pending_tickets,
        SUM(CASE WHEN payment_status='paid' THEN 1 ELSE 0 END) AS paid_tickets,
        SUM(CASE WHEN payment_status='paid' THEN amount ELSE 0 END) AS total_spent
    FROM tickets
    WHERE user_id='$user_id'
";

$summary_query = mysqli_query($conn, $summary_sql);

if(!$summary_query){
    die("Summary query failed: " . mysqli_error($conn));
}

$summary = mysqli_fetch_assoc($summary_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Tickets | Globmusk</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f4f4f9;color:#333;min-height:100vh;display:flex;flex-direction:column;}
a{text-decoration:none;}
.container{max-width:1100px;margin:120px auto 50px auto;padding:20px;flex:1;}
header{display:flex;justify-content:space-between;align-items:center;padding:15px 8%;background:#fff;position:fixed;top:0;width:100%;z-index:1000;box-shadow:0 2px 10px rgba(0,0,0,0.05);}
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
nav{display:flex;align-items:center;gap:20px;}
nav a{color:#555;font-weight:500;transition:0.3s;}
nav a:hover{color:#6a0dad;}
.hamburger{display:none;flex-direction:column;justify-content:space-between;width:30px;height:22px;cursor:pointer;z-index:1100;}
.hamburger div{width:100%;height:4px;background:#6a0dad;border-radius:3px;transition:0.4s;}
.hamburger.active div:nth-child(1){transform: rotate(45deg) translate(5px,5px);}
.hamburger.active div:nth-child(2){opacity:0;}
.hamburger.active div:nth-child(3){transform: rotate(-45deg) translate(6px,-6px);}
@media(max-width:768px){nav{position:fixed;top:70px;right:-250px;width:200px;background:white;flex-direction:column;gap:15px;padding:20px;border-radius:10px 0 0 10px;box-shadow:0 5px 15px rgba(0,0,0,0.1);transition:0.4s;}nav.active{right:0;}.hamburger{display:flex;}}

h2{text-align:center;color:#6a0dad;margin-bottom:30px;font-size:28px;}

/* ---------------- SUMMARY CARDS ---------------- */
.summary-cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:20px;
    margin-bottom:40px;
}
.summary-card{
    background:white;
    border-radius:12px;
    padding:25px;
    text-align:center;
    box-shadow:0 5px 18px rgba(0,0,0,0.08);
    transition:0.3s;
}
.summary-card:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(0,0,0,0.12);}
.summary-card h3{font-size:18px;color:#555;margin-bottom:10px;}
.summary-card p{font-size:22px;font-weight:600;color:#6a0dad;}

/* ---------------- TICKET CARDS ---------------- */
/* ---------------- TICKET CARDS ---------------- */
.tickets-grid{
    display:grid;
    grid-template-columns:repeat(2, 1fr); /* 2 columns on desktop */
    gap:25px;
}
@media(max-width:768px){
    .tickets-grid{
        grid-template-columns:1fr; /* 1 column on mobile */
    }
}
.ticket-card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 5px 18px rgba(0,0,0,0.08);display:flex;flex-direction:column;justify-content:space-between;transition:0.3s;min-height:360px;}
.ticket-card:hover{transform:translateY(-5px);box-shadow:0 10px 25px rgba(0,0,0,0.12);}
.ticket-card h3{color:#6a0dad;text-align:center;margin-bottom:15px;font-size:22px;}
.ticket-info{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;font-size:15px;}
.ticket-info div{padding:6px 8px;background:#f8f9fb;border-radius:8px;}
.ticket-status{font-weight:600;color:#333;text-align:center;margin:10px 0;}
.ticket-card a, .ticket-card .pending{display:block;text-align:center;padding:12px 0;border-radius:25px;font-weight:600;margin-top:10px;}
.ticket-card a{background:#6a0dad;color:white;text-decoration:none;transition:0.3s;}
.ticket-card a:hover{background:#4b0082;}
.ticket-card .pending{background:#f39c12;color:white;}

/* ---------------- FOOTER ---------------- */
footer{background:#6a0dad;color:white;text-align:center;padding:40px 20px;font-weight:500;}
footer p{font-size:16px;margin-bottom:10px;}
footer .footer-links a{color:white;margin:0 15px;text-decoration:none;transition:0.3s;}
footer .footer-links a:hover{color:#f0e;}

/* ---------------- RESPONSIVE ---------------- */
@media(max-width:768px){
    .container{margin:140px 20px;padding:15px;}
    .tickets-grid{grid-template-columns:1fr;gap:20px;}
    .ticket-info{grid-template-columns:1fr;}
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
        <div></div><div></div><div></div>
    </div>
    <nav>
        <a href="profile.php">Profile</a>
        <a href="events.php">Events</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>My Tickets</h2>

    <!-- ---------------- SUMMARY DASHBOARD ---------------- -->
    <div class="summary-cards">
        <div class="summary-card">
            <h3>Total Tickets</h3>
            <p><?= $summary['total_tickets'] ?></p>
        </div>
        <div class="summary-card">
            <h3>Pending Tickets</h3>
            <p><?= $summary['pending_tickets'] ?></p>
        </div>
        <div class="summary-card">
            <h3>Paid Tickets</h3>
            <p><?= $summary['paid_tickets'] ?></p>
        </div>
        <div class="summary-card">
            <h3>Total Spent</h3>
           <p>₦<?= number_format((float)($summary['total_spent'] ?? 0), 2) ?></p>
        </div>
    </div>

    <!-- ---------------- TICKET GRID ---------------- -->
    <div class="tickets-grid">
    <?php if(mysqli_num_rows($tickets_query) > 0): ?>
        <?php while($ticket = mysqli_fetch_assoc($tickets_query)): ?>
            <div class="ticket-card">
                <h3><?= htmlspecialchars($ticket['title']) ?></h3>

                <div class="ticket-info">
                    <div><strong>Type:</strong> <?= strtoupper($ticket['ticket_type']) ?></div>
                    <div><strong>Amount:</strong> ₦<?= number_format($ticket['amount'],2) ?></div>
                    <div><strong>Event Date:</strong> <?= $ticket['event_date'] ?></div>
                    <div><strong>Location:</strong> <?= htmlspecialchars($ticket['location']) ?></div>
                    <div style="grid-column:1/-1;"><strong>Description:</strong> <?= htmlspecialchars($ticket['description']) ?></div>
                    <div><strong>Purchase Date:</strong> <?= $ticket['purchase_date'] ?></div>
                    <div class="ticket-status"><strong>Status:</strong> <?= ucfirst($ticket['payment_status']) ?></div>
                </div>

                <?php if($ticket['payment_status'] === 'paid'): ?>
    <a href="view_ticket.php?ticket_id=<?= $ticket['id'] ?>">View / Download Ticket</a>

<?php elseif($ticket['payment_status'] === 'rejected'): ?>
    <p style="background:red;color:white;padding:10px;border-radius:20px;text-align:center;">
        ❌ Ticket Rejected
    </p>

<?php else: ?>
    <p class="pending">Ticket Pending Approval</p>
<?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;">You have no tickets yet. Check out <a href="events.php" style="color:#6a0dad;">events</a>!</p>
    <?php endif; ?>
    </div>
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