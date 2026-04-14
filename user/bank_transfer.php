<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if(!isset($_POST['event_id']) || !isset($_POST['ticket_type']) || !isset($_POST['amount'])){
    die("Invalid request.");
}

$event_id = intval($_POST['event_id']);
$ticket_type = $_POST['ticket_type'];
$amount = $_POST['amount'];

$event = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM events WHERE id='$event_id'"));
if(!$event) die("Event not found.");

// ✅ Create pending ticket
mysqli_query($conn, "
    INSERT INTO tickets (user_id, event_id, ticket_type, amount, payment_status, purchase_date)
    VALUES ('$user_id', '$event_id', '$ticket_type', '$amount', 'pending', NOW())
");

$ticket_id = mysqli_insert_id($conn);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Bank Transfer | Globmusk</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#f8f7ff;margin:0;padding:0;text-align:center;}
.container{max-width:600px;margin:80px auto;padding:30px;background:white;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
h2{color:#6a0dad;margin-bottom:15px;}
p{margin-bottom:10px;font-size:16px;}
.bank-details{background:#f0f0ff;padding:15px;margin:15px 0;border-radius:10px;}
button{padding:12px 20px;margin-top:10px;background:#6a0dad;color:white;border:none;border-radius:25px;cursor:pointer;transition:0.3s;}
button:hover{background:#4b0082;}
input[type="file"]{margin-top:10px;}
</style>
</head>
<body>

<div class="container">
    <h2><?= htmlspecialchars($event['title']) ?> - Bank Transfer</h2>
    <p><strong>Ticket:</strong> <?= strtoupper($ticket_type) ?></p>
    <p><strong>Amount:</strong> ₦<?= number_format($amount,2) ?></p>

    <div class="bank-details">
        <p><strong>Bank Name:</strong> Zenith Bank</p>
        <p><strong>Account Name:</strong> Globmusk Events</p>
        <p><strong>Account Number:</strong> 1234567890</p>
        <p>Transfer the amount to the above account and upload your payment receipt below.</p>
    </div>

    <form action="confirm_payment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
        <input type="file" name="receipt" required>
        <br>
        <button type="submit" name="confirm_payment">I Have Paid</button>
    </form>
</div>

</body>
</html>