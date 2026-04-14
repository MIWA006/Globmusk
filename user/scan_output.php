<?php
include('../includes/db.php');

// Check QR data
if(!isset($_GET['data'])){
    die("Invalid QR Code");
}

// Decode QR data
$data = json_decode(urldecode($_GET['data']), true);

if(!$data){
    die("Invalid QR Data");
}

$ticket_id = intval($data['ticket_id']);
$ticket_code = $data['ticket_code'];

// Fetch ticket
$query = mysqli_query($conn, "
SELECT t.*, e.title, e.event_date, e.location, u.name
FROM tickets t
JOIN events e ON t.event_id = e.id
JOIN users u ON t.user_id = u.id
WHERE t.id='$ticket_id' AND t.ticket_code='$ticket_code'
");

if(mysqli_num_rows($query) == 0){
    die("<h2 style='color:red;text-align:center;'>❌ Invalid Ticket</h2>");
}

$ticket = mysqli_fetch_assoc($query);

// CHECK IF ALREADY USED
if($ticket['scan_status'] == 'used'){
    echo "
    <h2 style='color:red;text-align:center;'>❌ Ticket Already Used</h2>
    <p style='text-align:center;'>Scanned at: {$ticket['scanned_at']}</p>
    ";
    exit();
}

// MARK AS USED
$now = date('Y-m-d H:i:s');

mysqli_query($conn, "
UPDATE tickets 
SET scan_status='used', scanned_at='$now' 
WHERE id='{$ticket['id']}'
");

// SUCCESS RESPONSE
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Ticket Verified</title>
<style>
body{
    font-family:Arial;
    background:#f4f4f9;
    text-align:center;
    padding:50px;
}
.box{
    background:white;
    padding:30px;
    border-radius:10px;
    max-width:500px;
    margin:auto;
    box-shadow:0 5px 15px rgba(0,0,0,0.1);
}
.success{color:green;font-size:22px;}
.info{margin:10px 0;color:#333;}
</style>
</head>
<body>

<div class="box">
    <h2 class="success">✅ Ticket Verified</h2>

    <p class="info"><strong>Name:</strong> <?= $ticket['name'] ?></p>
    <p class="info"><strong>Event:</strong> <?= $ticket['title'] ?></p>
    <p class="info"><strong>Date:</strong> <?= $ticket['event_date'] ?></p>
    <p class="info"><strong>Location:</strong> <?= $ticket['location'] ?></p>
    <p class="info"><strong>Ticket Type:</strong> <?= strtoupper($ticket['ticket_type']) ?></p>

    <p style="margin-top:20px;color:#6a0dad;">
        Entry Granted 🎉
    </p>
</div>

</body>
</html>