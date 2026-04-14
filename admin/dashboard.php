<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

$scanned_tickets = mysqli_query($conn, "
SELECT t.*, e.title, u.name AS user_name
FROM tickets t
JOIN events e ON t.event_id = e.id
JOIN users u ON t.user_id = u.id
WHERE t.scan_status='used'
ORDER BY t.scanned_at DESC
");

/* APPROVE PAYMENT */
/* =========================
   HANDLE ACTIONS FIRST
========================= */

// ✅ REJECT
if(isset($_POST['reject_id'])){

    $id = intval($_POST['reject_id']);

    mysqli_query($conn, "
        UPDATE tickets 
        SET payment_status='rejected' 
        WHERE id='$id'
    ");

    header("Location: dashboard.php");
    exit();
}


// ✅ APPROVE
if(isset($_POST['approve_id'])){

    $id = intval($_POST['approve_id']);

    $ticket = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM tickets WHERE id='$id'
    "));

    if($ticket){

        $event_id = $ticket['event_id'];
        $type = $ticket['ticket_type'];

        mysqli_query($conn, "
            UPDATE tickets 
            SET payment_status='paid' 
            WHERE id='$id'
        ");

        if($type == 'table_4'){
            mysqli_query($conn,"UPDATE events SET table_4_sold = table_4_sold + 1 WHERE id='$event_id'");
        }

        if($type == 'table_5'){
            mysqli_query($conn,"UPDATE events SET table_5_sold = table_5_sold + 1 WHERE id='$event_id'");
        }

        if($type == 'table_6'){
            mysqli_query($conn,"UPDATE events SET table_6_sold = table_6_sold + 1 WHERE id='$event_id'");
        }
    }

    header("Location: dashboard.php");
    exit();
}

/* EVENTS */
$events = mysqli_query($conn, "SELECT * FROM events");

/* PENDING */
$pending_tickets = mysqli_query($conn, "
SELECT t.*, e.title, u.name AS user_name, u.email AS user_email
FROM tickets t
JOIN events e ON t.event_id = e.id
JOIN users u ON t.user_id = u.id
WHERE t.payment_status='pending'
ORDER BY t.id DESC
");

/* STATS */
$stats = [
    'early_bird'=>0,
    'walk_in'=>0,
    'vip'=>0,
    'table_4'=>0,
    'table_5'=>0,
    'table_6'=>0
];

$total_revenue = 0;
$total_sold = 0;

$stats_query = mysqli_query($conn,"
SELECT ticket_type, COUNT(*) as total, SUM(amount) as revenue
FROM tickets
WHERE payment_status='paid'
GROUP BY ticket_type
");

while($row = mysqli_fetch_assoc($stats_query)){
    $type = $row['ticket_type'];

    if(isset($stats[$type])){
        $stats[$type] = $row['total'];
    }

    $total_revenue += $row['revenue'] ?? 0;
    $total_sold += $row['total'];
}

$total_events = mysqli_num_rows($events);
$total_pending = mysqli_num_rows($pending_tickets);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Globmusk Admin</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: 'Segoe UI', sans-serif;
}

body{
    background:#0a0f1f;
    color:#e5e7eb;
}

/* TOPBAR */
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:15px 20px;
    background:rgba(17,24,39,0.95);
    backdrop-filter:blur(10px);
    position:fixed;
    width:100%;
    top:0;
    z-index:1000;
    border-bottom:1px solid rgba(255,255,255,0.05);
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:700;
    color:#a855f7;
}

.logo img{height:35px}

/* HAMBURGER */
#hamburger{
    font-size:26px;
    cursor:pointer;
    color:#a855f7;
}

/* SIDEBAR */
.sidebar{
    width:240px;
    background:#111827;
    height:100vh;
    position:fixed;
    top:60px;
    left:0;
    padding:20px;
    transition:0.3s;
    border-right:1px solid rgba(255,255,255,0.05);
}

.sidebar a{
    display:block;
    padding:12px;
    margin-bottom:5px;
    color:#cbd5e1;
    text-decoration:none;
    border-radius:8px;
    transition:0.2s;
}

.sidebar a:hover{
    background:#1f2937;
    color:#fff;
}

/* MAIN */
.main{
    margin-left:240px;
    padding:90px 20px 30px;
}

/* CARDS (SAAS STYLE LIKE APPROVED PAGE) */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
}

.card{
    background:#111827;
    padding:18px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,0.05);
    box-shadow:0 10px 25px rgba(0,0,0,0.25);
}

.card h4{
    font-size:12px;
    color:#9ca3af;
}

.card p{
    font-size:22px;
    margin-top:6px;
    color:#a855f7;
    font-weight:bold;
}

/* TABLE */
.table-box{
    background:#111827;
    margin-top:25px;
    border-radius:14px;
    overflow-x:auto;
    border:1px solid rgba(255,255,255,0.05);
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
}

table{
    width:100%;
    border-collapse:collapse;
    min-width:700px;
}

th{
    background:#a855f7;
    color:white;
    padding:14px;
}

td{
    padding:14px;
    text-align:center;
    color:#d1d5db;
}

tr:hover{
    background:rgba(168,85,247,0.08);
}

/* TYPE BADGES */
.type{
    padding:5px 10px;
    border-radius:20px;
    font-size:12px;
    color:white;
}

.early_bird{background:#22c55e;}
.walk_in{background:#3b82f6;}
.vip{background:#f59e0b;}
.table_4{background:#8b5cf6;}
.table_5{background:#ec4899;}
.table_6{background:#ef4444;}

/* BUTTON BASE */
.btn{
    border:none;
    padding:8px 14px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:all 0.25s ease;
    display:flex;
    align-items:center;
    gap:5px;
}

/* APPROVE BUTTON */
.approve{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:white;
    box-shadow:0 4px 12px rgba(34,197,94,0.3);
}

.approve:hover{
    transform:translateY(-2px) scale(1.05);
    box-shadow:0 6px 16px rgba(34,197,94,0.5);
}

/* REJECT BUTTON */
.reject{
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:white;
    box-shadow:0 4px 12px rgba(239,68,68,0.3);
}

.reject:hover{
    transform:translateY(-2px) scale(1.05);
    box-shadow:0 6px 16px rgba(239,68,68,0.5);
}

/* CLICK EFFECT */
.btn:active{
    transform:scale(0.95);
}

/* CHARTS */
.chart-container{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
    margin-top:25px;
}

.chart-box{
    background:#111827;
    padding:20px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,0.05);
    height:320px;
}

/* MOBILE */
@media(max-width:768px){
    .sidebar{
        left:-260px;
    }

    .sidebar.active{
        left:0;
    }

    .main{
        margin-left:0;
    }

    .chart-container{
        grid-template-columns:1fr;
    }
}

</style>
</head>

<body>

<!-- TOP BAR -->
<div class="topbar">
    <div style="display:flex;align-items:center;gap:15px;">
        <span id="hamburger">&#9776;</span>

        <div class="logo">
            <img src="../assets/logo.JPG">
            <span>Globmusk</span>
        </div>
    </div>

    <span><?= $total_pending ?> Pending</span>
</div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <a href="#">Dashboard</a>
    <a href="upcoming_events.php">Manage Events</a>
    <a href="approved_ticket.php">Approved Tickets</a>
    <a href="../auth/logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

<!-- CARDS -->
<div class="cards">
    <div class="card"><h4>Total Events</h4><p><?= $total_events ?></p></div>
    <div class="card"><h4>Pending</h4><p><?= $total_pending ?></p></div>
    <div class="card"><h4>Sold</h4><p><?= $total_sold ?></p></div>
    <div class="card"><h4>Revenue</h4><p>₦<?= number_format($total_revenue,2) ?></p></div>
</div>

<!-- TABLE -->
<div class="table-box">
 <h2 style="
    text-align: center;
    margin: 50px 0 30px 0; /* Optional: top and bottom spacing */
    color: #6a0dad;        /* Optional: make them match your theme */
">PENDING BANK TRANSFERS</h2>
<div style="margin:20px 0;text-align:center;">
    <input 
        type="text" 
        id="search" 
        placeholder="Search by name, email or event..." 
        style="
            width:90%;
            max-width:500px;
            padding:12px;
            border-radius:10px;
            border:none;
            outline:none;
            background:#1f2937;
            color:#fff;
        ">
</div>
<table>
<tr class="row">
<th>Name</th>
<th>Email</th>
<th>Event</th>
<th>Type</th>
<th>Amount</th>
<th>Receipt</th>
<th>Action</th>
</tr>

<?php while($t = mysqli_fetch_assoc($pending_tickets)): ?>
<tr class="row">
<td><?= $t['user_name'] ?></td>
<td><?= $t['user_email'] ?></td>
<td><?= $t['title'] ?></td>

<td>
    <span class="type <?= $t['ticket_type'] ?>">
        <?= $t['ticket_type'] ?>
    </span>
</td>

<td>₦<?= number_format($t['amount'],2) ?></td>

<!-- ✅ RECEIPT COLUMN -->
<td>
    <?php if(!empty($t['receipt'])): ?>
        <a href="../uploads/receipts/<?= $t['receipt'] ?>" target="_blank"
   style="
       background:#22c55e;
       color:white;
       padding:6px 12px;
       border-radius:6px;
       text-decoration:none;
       font-size:12px;
   ">
   View
</a>
    <?php else: ?>
        <span style="color:#ef4444;">No receipt</span>
    <?php endif; ?>
</td>

<!-- ✅ ACTION COLUMN -->
<td style="display:flex;gap:8px;justify-content:center;flex-wrap:wrap;">

    <!-- APPROVE -->
    <form method="POST">
        <input type="hidden" name="approve_id" value="<?= $t['id'] ?>">
        <button class="btn approve">
            ✔ Approve
        </button>
    </form>

    <!-- REJECT -->
    <form method="POST">
        <input type="hidden" name="reject_id" value="<?= $t['id'] ?>">
        <button class="btn reject">
            ✖ Reject
        </button>
    </form>

</td>

</tr>
<?php endwhile; ?>
</table>
</div>

<!-- CHARTS -->
  <h2 style="
    text-align: center;
    margin: 50px 0 30px 0; /* Optional: top and bottom spacing */
    color: #6a0dad;        /* Optional: make them match your theme */
">TICKET STATISTICS</h2>
<div class="chart-container">

<div class="chart-box">
<canvas id="pie"></canvas>
</div>

<div class="chart-box">
<canvas id="bar"></canvas>
</div>

</div>

</div>
<h2 style="
    text-align:center;
    margin:50px 0 30px 0;
    color:#16a34a;
">Scanned Tickets (Entry Logs)</h2>

<div class="table-box">

<table>
<thead>
<tr>
<th>Name</th>
<th>Event</th>
<th>Ticket Type</th>
<th>Amount</th>
<th>Scanned At</th>
</tr>
</thead>

<tbody>
<?php if(mysqli_num_rows($scanned_tickets)==0): ?>
<tr><td colspan="5">No scanned tickets yet</td></tr>
<?php else: ?>
<?php while($s = mysqli_fetch_assoc($scanned_tickets)): ?>
<tr>
<td><?= htmlspecialchars($s['user_name']) ?></td>
<td><?= htmlspecialchars($s['title']) ?></td>
<td>
<span class="type <?= $s['ticket_type'] ?>">
<?= strtoupper($s['ticket_type']) ?>
</span>
</td>
<td>₦<?= number_format((float)$s['amount'],2) ?></td>
<td><?= $s['scanned_at'] ?></td>
</tr>
<?php endwhile; ?>
<?php endif; ?>
</tbody>
</table>

</div>

<script>
const sidebar = document.getElementById('sidebar');
document.getElementById('hamburger').onclick = ()=>{
    sidebar.classList.toggle('active');
};

new Chart(document.getElementById('pie'), {
    type: 'doughnut',
    data: {
        labels: ['Early', 'Walk', 'VIP', 'T4', 'T5', 'T6'],
        datasets: [{
            data: [
                <?= $stats['early_bird'] ?>,
                <?= $stats['walk_in'] ?>,
                <?= $stats['vip'] ?>,
                <?= $stats['table_4'] ?>,
                <?= $stats['table_5'] ?>,
                <?= $stats['table_6'] ?>
            ],
            backgroundColor: [
                '#22c55e',
                '#3b82f6',
                '#f59e0b',
                '#8b5cf6',
                '#ec4899',
                '#ef4444'
            ],
            borderWidth: 2,
            borderColor: '#0a0f1f',
            hoverOffset: 12
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#cbd5e1',
                    padding: 15,
                    font: {
                        size: 12
                    }
                }
            },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#fff',
                bodyColor: '#ddd',
                borderColor: '#a855f7',
                borderWidth: 1,
                padding: 12
            }
        },
        animation: {
            animateRotate: true,
            duration: 1800
        }
    }
});

const ctx = document.getElementById('bar').getContext('2d');

const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, '#a855f7');
gradient.addColorStop(1, '#3b82f6');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Early', 'Walk', 'VIP', 'T4', 'T5', 'T6'],
        datasets: [{
            label: 'Tickets Sold',
            data: [
                <?= $stats['early_bird'] ?>,
                <?= $stats['walk_in'] ?>,
                <?= $stats['vip'] ?>,
                <?= $stats['table_4'] ?>,
                <?= $stats['table_5'] ?>,
                <?= $stats['table_6'] ?>
            ],
            backgroundColor: gradient,
            borderRadius: 10,
            borderSkipped: false,
            barThickness: 28,
            hoverBackgroundColor: '#22c55e'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#111827',
                titleColor: '#fff',
                bodyColor: '#ddd',
                borderColor: '#a855f7',
                borderWidth: 1,
                padding: 12
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { color: '#9ca3af' }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.05)' },
                ticks: { color: '#9ca3af' }
            }
        },
        animation: {
            duration: 2000,
            easing: 'easeOutQuart'
        }
    }
});
/* ======================
   SEARCH (PENDING TABLE)
====================== */
document.getElementById("search").addEventListener("input", function () {
    let val = this.value.toLowerCase();

    document.querySelectorAll(".row").forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(val) ? "" : "none";
    });
});

/* ======================
   MOBILE SIDEBAR CLOSE ON OUTSIDE TAP
====================== */
document.addEventListener("click", function (e) {
    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburger");

    const isClickInsideSidebar = sidebar.contains(e.target);
    const isClickHamburger = hamburger.contains(e.target);

    if (!isClickInsideSidebar && !isClickHamburger) {
        sidebar.classList.remove("active");
    }
});
</script>

</body>
</html>