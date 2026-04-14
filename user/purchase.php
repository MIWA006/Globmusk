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

if(!isset($_GET['event_id']) || !isset($_GET['ticket_type'])){
    die("Invalid request.");
}

$event_id = intval($_GET['event_id']);
$ticket_type = $_GET['ticket_type'];

$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'"));
if(!$event) die("Event not found.");
// GET EVENT AGAIN (IMPORTANT)
$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'"));
if(!$event) die("Event not found");

// CHECK TABLE LIMITS

if($ticket_type == 'table_4' && $event['table_4_sold'] >= $event['table_4_limit']){
    die("❌ Table for 4 is sold out.");
}

if($ticket_type == 'table_5' && $event['table_5_sold'] >= $event['table_5_limit']){
    die("❌ Table for 5 is sold out.");
}

if($ticket_type == 'table_6' && $event['table_6_sold'] >= $event['table_6_limit']){
    die("❌ Table for 6 is sold out.");
}

// Determine ticket price
$amount = 0;

switch($ticket_type){

    case 'early_bird':
        $amount = $event['early_bird_price'];
        break;

    case 'walk_in':
        $amount = $event['walk_in_price'];
        break;

    case 'vip':
        $amount = $event['vip_price'];
        break;

    case 'table_4':
        $amount = $event['table_4_price'];
        break;

    case 'table_5':
        $amount = $event['table_5_price'];
        break;

    case 'table_6':
        $amount = $event['table_6_price'];
        break;

    default:
        die("Invalid ticket type selected.");
}
if($amount <= 0){
    die("Invalid ticket price.");
}

/* ================= REMITA HANDLER ================= */
if(isset($_POST['pay_remita'])){

    // 👉 Replace with your real credentials
    $merchantId = "2547916";
    $serviceTypeId = "4430731";
    $apiKey = "1946";

    $orderId = "GM_".time();

    // Get user details
    $user = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id='$user_id'"));
    $payerName = $user['name'];
    $payerEmail = $user['email'];

    $hash = hash('sha512', $merchantId.$serviceTypeId.$orderId.$amount.$apiKey);

    $url = "https://remitademo.net/remita/exapp/api/v1/send/api/echannelsvc/merchant/api/paymentinit";

    $data = [
        "serviceTypeId" => $serviceTypeId,
        "amount" => $amount,
        "orderId" => $orderId,
        "payerName" => $payerName,
        "payerEmail" => $payerEmail,
        "payerPhone" => "08000000000",
        "description" => "Globmusk Ticket Payment"
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: remitaConsumerKey=$merchantId,remitaConsumerToken=$hash"
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    $rrr = $result['RRR'] ?? null;

    if($rrr){

        // Save payment
        mysqli_query($conn, "
        INSERT INTO payments (order_id, rrr, amount, status, user_id)
        VALUES ('$orderId','$rrr','$amount','pending','$user_id')
        ");

        // Save ticket as pending
        mysqli_query($conn, "
        INSERT INTO tickets (user_id, event_id, ticket_type, payment_status, rrr)
        VALUES ('$user_id','$event_id','$ticket_type','pending','$rrr')
        ");

        // Redirect to Remita
        header("Location: https://remitademo.net/remita/ecomm/finalize.reg?rrr=$rrr");
        exit();

    } else {
        die("Failed to initialize Remita payment.");
    }
}
/* ================= END REMITA ================= */
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Purchase Ticket</title>
<style>
body{font-family:Arial,sans-serif;background:#f8f7ff;text-align:center;padding:50px;}
.box{background:white;padding:30px;border-radius:10px;max-width:500px;margin:auto;}
button{padding:12px 20px;margin:10px;background:#6a0dad;color:white;border:none;border-radius:8px;cursor:pointer;}
</style>
</head>
<body>

<div class="box">
    <h2><?= htmlspecialchars($event['title']) ?></h2>
    <p><strong>Ticket:</strong> <?= strtoupper($ticket_type) ?></p>
    <p><strong>Amount:</strong> ₦<?= number_format($amount,2) ?></p>

    <h3>Select Payment Method</h3>

    <!-- BANK TRANSFER -->
    <form action="bank_transfer.php" method="POST">
        <input type="hidden" name="event_id" value="<?= $event_id ?>">
        <input type="hidden" name="ticket_type" value="<?= $ticket_type ?>">
        <input type="hidden" name="amount" value="<?= $amount ?>">
        <button type="submit">Pay via Bank Transfer</button>
    </form>

    <!-- 🔥 REMITA PAYMENT -->
    <form method="POST">
        <button type="submit" name="pay_remita">Pay with Remita</button>
    </form>

</div>

</body>
</html>