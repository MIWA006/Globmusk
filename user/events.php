<?php
session_start();
include('../includes/db.php'); // Make sure this path is correct

// Ensure user is logged in
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all events
$events = mysqli_query($conn, "SELECT * FROM events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Events | Globmusk</title>

<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:#f8f7ff;color:#333;line-height:1.6;}
header{display:flex;justify-content:space-between;align-items:center;padding:15px 8%;background:rgba(255,255,255,0.95);position:fixed;top:0;width:100%;z-index:1000;backdrop-filter:blur(10px);box-shadow:0 2px 10px rgba(0,0,0,0.05);}
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
nav a{text-decoration:none;color:#555;font-weight:500;}
nav a:hover{color:#6a0dad;}
.hamburger{display:none;flex-direction:column;justify-content:space-between;width:30px;height:22px;cursor:pointer;z-index:1100;}
.hamburger div{width:100%;height:4px;background:#6a0dad;border-radius:3px;transition:0.4s;}
.hamburger.active div:nth-child(1){transform: rotate(45deg) translate(5px,5px);}
.hamburger.active div:nth-child(2){opacity:0;}
.hamburger.active div:nth-child(3){transform: rotate(-45deg) translate(6px,-6px);}
@media(max-width:768px){
    nav{position:fixed;top:70px;right:-250px;width:200px;background:white;flex-direction:column;gap:15px;padding:20px;border-radius:10px 0 0 10px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
    nav.active{right:0;}
    .hamburger{display:flex;}
}
.container{max-width:1100px;margin:140px auto 50px auto;padding:20px;}
h2{text-align:center;color:#6a0dad;margin-bottom:20px;}
.events-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:25px;}
.event-card .location {
    font-weight: bold;
    margin-bottom: 10px;
    color: #333;
}
.event-card{background:white;border-radius:15px;padding:20px;box-shadow:0 5px 15px rgba(0,0,0,0.08);display:flex;flex-direction:column;transition:0.3s;}
.event-card:hover{transform:translateY(-5px);}
.event-card img {
    width: 100%;
    height: 300px;      /* standard card height */
    object-fit: contain; /* show full image without cropping */
    border-radius: 10px;
    background: #f4f4f4; /* placeholder for empty areas */
}
.event-card h3{color:#6a0dad;margin-bottom:10px;}
.event-card p{flex:1;margin-bottom:10px;color:#555;}
.event-card .date{font-weight:bold;margin-bottom:10px;}
.event-card .tickets span{display:block;margin-bottom:5px;color:#333;}
.event-card select{margin:10px 0;padding:8px 10px;border-radius:10px;border:1px solid #ccc;}
.event-card button{padding:10px;background:#6a0dad;color:white;border:none;border-radius:25px;cursor:pointer;transition:0.3s;}
.event-card button:hover{background:#4b0082;}
footer{background:#6a0dad;color:white;text-align:center;padding:20px;margin-top:30px;}
@media(max-width:768px){.container{margin:160px 20px;padding:15px;}}
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
        <a href="profile.php">Profile</a>
        <a href="tickets.php">My Tickets</a>
        <a href="../auth/logout.php">Logout</a>
    </nav>
</header>

<div class="container">
    <h2>Browse Events</h2>
    <div class="events-grid">
        <?php while($row = mysqli_fetch_assoc($events)): ?>
            <?php
$t4_left = $row['table_4_limit'] - $row['table_4_sold'];
$t5_left = $row['table_5_limit'] - $row['table_5_sold'];
$t6_left = $row['table_6_limit'] - $row['table_6_sold'];
?>
            <?php
$is_sold_out = false; // temporarily disable until you implement sales tracking
?>

        <div class="event-card">
    <img src="../uploads/events/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
    <h3><?= htmlspecialchars($row['title']) ?></h3>
    <p><?= htmlspecialchars($row['description']) ?></p>
    <div class="date">
    Date: <?= date("F j, Y - h:i A", strtotime($row['event_date'])) ?>
</div>
    <div class="location">Location: <?= htmlspecialchars($row['location']) ?></div>
    <?php
$now = time();
$deadline = strtotime($row['early_bird_end'] ?? '');

$time_left = $deadline - $now;
?>
    <div class="tickets">
    <span>Early Bird: ₦<?= number_format($row['early_bird_price'] ?? 0) ?></span>
    <span>Walk-in/Regular: ₦<?= number_format($row['walk_in_price'] ?? 0) ?></span>
    <span>VIP: ₦<?= number_format($row['vip_price'] ?? 0) ?></span>

    <hr style="margin:8px 0;">

    <span>Table for 4: ₦<?= number_format($row['table_4_price'] ?? 0) ?></span>
    <span>Table for 5: ₦<?= number_format($row['table_5_price'] ?? 0) ?></span>
    <span>Table for 6: ₦<?= number_format($row['table_6_price'] ?? 0) ?></span>
</div>
<div class="availability">
    <span>Table 4 Left: <?= ($row['table_4_limit'] - $row['table_4_sold']) ?></span><br>
    <span>Table 5 Left: <?= ($row['table_5_limit'] - $row['table_5_sold']) ?></span><br>
    <span>Table 6 Left: <?= ($row['table_6_limit'] - $row['table_6_sold']) ?></span><br>
    
</div>
<div id="countdown_<?= $row['id'] ?>" style="color:red;font-weight:bold;margin-bottom:10px;"></div>

            <form method="GET" action="purchase.php">
                <input type="hidden" name="event_id" value="<?= $row['id'] ?>">
                <label for="ticket_type_<?= $row['id'] ?>">Select Ticket Type:</label>
                <select name="ticket_type" id="ticket_type_<?= $row['id'] ?>" required onchange="updatePrice(<?= $row['id'] ?>)">
    <option value="">--Choose Ticket--</option>

    <option 
    id="early_<?= $row['id'] ?>"
    value="early_bird" 
    data-price="<?= $row['early_bird_price'] ?>">
    Early Bird
</option>
    <option value="walk_in" data-price="<?= $row['walk_in_price'] ?>">Walk-in</option>
    <option value="vip" data-price="<?= $row['vip_price'] ?>">VIP</option>

    <option value="table_4" data-price="<?= $row['table_4_price'] ?>"
<?= ($t4_left <= 0 ? 'disabled' : '') ?>>
Table for 4
</option>

<option value="table_5" data-price="<?= $row['table_5_price'] ?>"
<?= ($t5_left <= 0 ? 'disabled' : '') ?>>
Table for 5
</option>

<option value="table_6" data-price="<?= $row['table_6_price'] ?>"
<?= ($t6_left <= 0 ? 'disabled' : '') ?>>
Table for 6
</option>
</select>
                <div id="price_display_<?= $row['id'] ?>" style="margin-bottom:10px;font-weight:bold;"></div>
                <?php if($is_sold_out): ?>
    <button disabled style="background:red;">Sold Out</button>
<?php else: ?>
    <button type="submit">Purchase</button>
<?php endif; ?>
            </form>
            <?php if(!empty($row['early_bird_end'])): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    startCountdown(
        <?= $row['id'] ?>,
        "<?= $row['early_bird_end'] ?>",
        "early_<?= $row['id'] ?>"
    );
});
</script>
<?php endif; ?>
        </div>
        <?php endwhile; ?>
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

function updatePrice(eventId){
    const select = document.getElementById('ticket_type_' + eventId);
    const option = select.options[select.selectedIndex];

    if(!option || !option.dataset.price){
        document.getElementById('price_display_' + eventId).innerText = '';
        return;
    }

    const price = Number(option.dataset.price);

    document.getElementById('price_display_' + eventId).innerText =
        'Price: ₦' + price.toLocaleString();
}


//QWERTYUIOP







function startCountdown(id, endTime, earlyBirdOptionId = null){

    const el = document.getElementById("countdown_" + id);

    function update(){

        const now = new Date().getTime();
        let distance = new Date(endTime).getTime() - now;

        if(distance <= 0){

            if(el){
                el.innerHTML = "🔥 Early Bird Ended";
            }

            if(earlyBirdOptionId){
                const option = document.getElementById(earlyBirdOptionId);
                if(option) option.remove();
            }

            return;
        }

        // ✅ FIXED TIME CALCULATION
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        distance %= (1000 * 60 * 60 * 24);

        const hours = Math.floor(distance / (1000 * 60 * 60));
        distance %= (1000 * 60 * 60);

        const mins = Math.floor(distance / (1000 * 60));
        distance %= (1000 * 60);

        const secs = Math.floor(distance / 1000);

        if(el){
            el.innerHTML =
                `⏳ Early Bird: ${days}d ${hours}h ${mins}m ${secs}s`;
        }
    }

    update();
    setInterval(update, 1000);
}
</script>
</body>
</html>