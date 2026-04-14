<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('../includes/db.php');

if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}

/* ======================
   APPROVED TICKETS
====================== */
$approved = mysqli_query($conn, "
SELECT t.*, e.title, u.name AS user_name, u.email
FROM tickets t
JOIN events e ON t.event_id = e.id
JOIN users u ON t.user_id = u.id
WHERE t.payment_status='paid'
ORDER BY t.id DESC
");

/* ======================
   KPI
====================== */
$totalRevenue = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(amount) as total FROM tickets WHERE payment_status='paid'
"))['total'] ?? 0;

$totalTickets = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) as total FROM tickets WHERE payment_status='paid'
"))['total'] ?? 0;

/* ======================
   TYPE STATS
====================== */
$type_stats = mysqli_query($conn,"
SELECT ticket_type, COUNT(*) as total
FROM tickets
WHERE payment_status='paid'
GROUP BY ticket_type
");

$labels=[]; $data=[];
while($t=mysqli_fetch_assoc($type_stats)){
    $labels[] = strtoupper($t['ticket_type']);
    $data[] = $t['total'];
}

/* ======================
   🔥 NEW: REVENUE PER EVENT
====================== */
$eventQ = mysqli_query($conn,"
SELECT e.title, SUM(t.amount) as total
FROM tickets t
JOIN events e ON t.event_id=e.id
WHERE t.payment_status='paid'
GROUP BY e.title
");

$eventLabels=[]; $eventData=[];
while($e=mysqli_fetch_assoc($eventQ)){
    $eventLabels[] = $e['title'];
    $eventData[] = $e['total'];
}

/* ======================
   🔥 NEW: NOTIFICATIONS DATA
====================== */
$notifQ = mysqli_query($conn,"
SELECT u.name,e.title
FROM tickets t
JOIN users u ON t.user_id=u.id
JOIN events e ON t.event_id=e.id
ORDER BY t.id DESC
LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Globmusk SaaS Admin</title>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:Segoe UI;}
body{background:#0a0f1f;color:#fff;}

/* HEADER (UNCHANGED) */
header{
position:fixed;top:0;width:100%;
padding:15px 20px;
display:flex;justify-content:space-between;
align-items:center;
background:rgba(10,15,30,0.9);
backdrop-filter:blur(10px);
z-index:1000;
}

.logo{color:#a855f7;font-weight:bold;display:flex;gap:10px;align-items:center}

/* HAMBURGER (UNCHANGED) */
.hamburger{display:none;flex-direction:column;cursor:pointer}
.hamburger span{height:3px;width:25px;background:#a855f7;margin:4px;transition:0.3s}
.hamburger.active span:nth-child(1){transform:rotate(45deg) translate(5px,5px)}
.hamburger.active span:nth-child(2){opacity:0}
.hamburger.active span:nth-child(3){transform:rotate(-45deg) translate(6px,-6px)}

/* NAV (UNCHANGED) */
nav{display:flex;gap:20px}
nav a{color:#ccc;text-decoration:none}
nav a:hover{color:#a855f7}

@media(max-width:768px){
nav{
position:fixed;right:-260px;top:0;
width:250px;height:100vh;
background:#111827;
flex-direction:column;
padding:80px 20px;
transition:0.3s;
}
nav.active{right:0}
.hamburger{display:flex}
}

/* CONTAINER */
.container{margin-top:90px;padding:20px}

/* CARDS (UNCHANGED) */
.kpi,.types{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(160px,1fr));
gap:12px;margin-bottom:20px;
}

.card{
background:#111827;
padding:15px;
border-radius:12px;
border:1px solid rgba(255,255,255,0.05);
}

.card h4{color:#aaa;font-size:12px}
.card p{color:#a855f7;font-size:20px;font-weight:bold}

/* TABLE (UNCHANGED) */
.table-box{
background:#111827;
border-radius:12px;
overflow:auto;
}

table{width:100%;border-collapse:collapse}
th{background:#a855f7;padding:12px}
td{padding:12px;text-align:center;color:#ddd}

/* NOTIFICATION */
.notify{position:relative;cursor:pointer}
.badge{position:absolute;top:-5px;right:-5px;background:red;padding:2px 6px;border-radius:50%;font-size:10px}
.dropdown{position:absolute;right:10px;top:60px;background:#111827;width:250px;border-radius:10px;display:none;padding:10px}
.dropdown.show{display:block}
.dropdown div{padding:8px;border-bottom:1px solid rgba(255,255,255,0.05);font-size:12px}

/* CHART */
.chart-box{
background:#111827;
padding:15px;
border-radius:12px;
margin-top:20px;
}
</style>
</head>

<body>

<header>
<div class="logo">Globmusk</div>

<div class="notify" id="notify">
    🔔
    <span class="badge" id="notifyCount">0</span>

    <div class="dropdown" id="dropdown"></div>
</div>

<div class="hamburger" id="hamburger" onclick="toggleMenu()">
<span></span><span></span><span></span>
</div>

<nav id="nav">
<a href="dashboard.php">Dashboard</a>
<a href="approved_ticket.php">Tickets</a>
<a href="upcoming_events.php">Events</a>
<a href="../auth/logout.php">Logout</a>
</nav>
</header>

<div class="container">

<!-- KPI -->
<div class="kpi">
<div class="card">Revenue<br><b>₦<?= number_format($totalRevenue) ?></b></div>
<div class="card">Tickets<br><b><?= $totalTickets ?></b></div>
</div>

<!-- TYPE CARDS -->
<div class="types">
<?php foreach($labels as $i=>$l): ?>
<div class="card">
<?= $l ?><br><b><?= $data[$i] ?></b>
</div>
<?php endforeach; ?>
</div>

<!-- CHART -->
<div class="chart-box">
<canvas id="eventChart"></canvas>
</div>

<!-- TABLE -->
 <!-- SEARCH -->
<div style="margin-bottom:15px;">
    <input 
        type="text" 
        id="searchBox" 
        placeholder="Search by user name or event title..."
        style="
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            background:#111827;
            color:#fff;
            outline:none;
        "
    >
</div>
<div class="table-box">
<table>
<tr>
<th>Name</th>
<th>Email</th>
<th>Event</th>
<th>Type</th>
<th>Amount</th>
</tr>

<?php while($r=mysqli_fetch_assoc($approved)): ?>
<tr class="row">
<td><?= $r['user_name'] ?></td>
<td><?= $r['email'] ?></td>
<td><?= $r['title'] ?></td>
<td><?= strtoupper($r['ticket_type']) ?></td>
<td>₦<?= number_format($r['amount']) ?></td>
</tr>
<?php endwhile; ?>

</table>
</div>

</div>

<script>

/* ======================
   MENU + TAP OUTSIDE CLOSE
====================== */
function toggleMenu(){
document.getElementById("nav").classList.toggle("active");
document.getElementById("hamburger").classList.toggle("active");
}

document.addEventListener("click", function(e){
let nav=document.getElementById("nav");
let burger=document.getElementById("hamburger");

if(!nav.contains(e.target) && !burger.contains(e.target)){
nav.classList.remove("active");
burger.classList.remove("active");
}
});

/* ======================
   🔔 FIXED LIVE NOTIFICATIONS
====================== */

function loadNotifications(){
    fetch("fetch_notifications.php")
    .then(res => res.json())
    .then(data => {

        // update badge
        document.getElementById("notifyCount").innerText = data.length;

        // update dropdown
        let box = document.getElementById("dropdown");
        box.innerHTML = "";

        if(data.length === 0){
            box.innerHTML = "<div>No new notifications</div>";
            return;
        }

        data.forEach(n => {
            box.innerHTML += `
                <div>
                    🎟 <b>${n.name}</b> bought <br> 
                    <small>${n.title}</small>
                </div>
            `;
        });
    })
    .catch(err => console.log("Notification error:", err));
}

/* AUTO REFRESH EVERY 3 SECONDS */
setInterval(loadNotifications, 3000);
loadNotifications();

/* ======================
   CLICK TO TOGGLE
====================== */
document.getElementById("notify").addEventListener("click", function(e){
    document.getElementById("dropdown").classList.toggle("show");
    e.stopPropagation();
});

/* CLOSE WHEN CLICKING OUTSIDE */
document.addEventListener("click", function(){
    document.getElementById("dropdown").classList.remove("show");
});

/* ======================
   📊 FANCY REVENUE PER EVENT CHART
====================== */

const ctx = document.getElementById("eventChart").getContext("2d");

const gradient = ctx.createLinearGradient(0, 0, 0, 400);
gradient.addColorStop(0, "#a855f7");
gradient.addColorStop(1, "#3b82f6");

new Chart(ctx, {
    type: "bar",
    data: {
        labels: <?= json_encode($eventLabels) ?>,
        datasets: [{
            label: "Revenue per Event",
            data: <?= json_encode($eventData) ?>,
            backgroundColor: gradient,
            borderRadius: 10,
            borderSkipped: false,
            barThickness: 30,
            hoverBackgroundColor: "#22c55e"
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
                backgroundColor: "#111827",
                titleColor: "#fff",
                bodyColor: "#ddd",
                borderColor: "#a855f7",
                borderWidth: 1,
                padding: 12,
                displayColors: false
            }
        },

        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: "#aaa",
                    font: {
                        size: 12
                    }
                }
            },
            y: {
                grid: {
                    color: "rgba(255,255,255,0.05)"
                },
                ticks: {
                    color: "#aaa"
                }
            }
        },

        animation: {
            duration: 1800,
            easing: "easeOutQuart"
        }
    }
});

/* ======================
   SEARCH USER + EVENT TITLE
====================== */
document.getElementById("searchBox").addEventListener("input", function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll(".row");

    rows.forEach(row => {
        let name = row.children[0].innerText.toLowerCase();   // user name
        let email = row.children[1].innerText.toLowerCase();  // email (optional match)
        let event = row.children[2].innerText.toLowerCase();  // event title

        if (name.includes(value) || event.includes(value) || email.includes(value)) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
});
</script>

</body>
</html>