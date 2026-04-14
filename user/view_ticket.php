<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['user_id']) || !isset($_GET['ticket_id'])){
    header("Location: tickets.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ticket_id = intval($_GET['ticket_id']);

$ticket_q = mysqli_query($conn, "
    SELECT t.*, e.title AS event_name, e.location, e.event_date, e.description,
           u.email, u.name AS user_name
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    JOIN users u ON t.user_id = u.id
    WHERE t.id='$ticket_id' AND t.user_id='$user_id'
");

if(mysqli_num_rows($ticket_q) == 0){
    die("Ticket not found.");
}

$ticket = mysqli_fetch_assoc($ticket_q);

// 🔐 Ensure ticket_code always exists
if(empty($ticket['ticket_code'])){
    $ticket['ticket_code'] = "GMB-" . strtoupper(substr(md5($ticket['id']), 0, 8));
}

require_once('../lib/tcpdf/tcpdf.php');
include('../lib/phpqrcode/qrlib.php');

$tmp_folder = '../tmp_qr/';
if(!is_dir($tmp_folder)) mkdir($tmp_folder, 0777, true);

$local_ip = '172.20.10.6:8000'; // keep your IP

// Create secure payload
$qr_payload = json_encode([
    "ticket_id" => $ticket['id'],
    "ticket_code" => $ticket['ticket_code']
]);

// Encode for URL
$qr_data = urlencode($qr_payload);

// Final scan URL
$scan_url = "http://localhost/globmusk/user/scan_output.php?data=".$qr_data;

// Generate QR
$tmpfile = $tmp_folder . 'ticket_' . $ticket['id'] . '.png';

// Generate QR code
QRcode::png($scan_url, $tmpfile, 'L', 4, 2);

// Ensure file exists
clearstatcache();
if(!file_exists($tmpfile)){
    die("QR Code not generated");
}
/* ================= PDF SETUP ================= */
$pdf = new TCPDF('P','mm','A4',true,'UTF-8',false);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();
$pdf->SetMargins(10,10,10);

/* ================= COLOR BY TYPE ================= */
$type = strtolower($ticket['ticket_type']);

if($type == 'vip'){
    $color = [245, 158, 11]; // gold
} 
elseif($type == 'walk_in'){
    $color = [59, 130, 246]; // blue
} 
elseif($type == 'early_bird'){
    $color = [156, 163, 175]; // ash
}
 else {
    $color = [139, 92, 246]; // purple
}

/* ================= HEADER ================= */
$pdf->SetFillColor($color[0], $color[1], $color[2]);
$pdf->Rect(0, 0, 210, 32, 'F');

// LOGO
$logo = '../assets/logo.JPG';
$pdf->Image($logo, 10, 6, 18, 18); // x, y, width, height

// TITLE
$pdf->SetTextColor(255,255,255);
$pdf->SetFont('helvetica','B',16);
$pdf->SetXY(32, 10);
$pdf->Cell(120, 10, 'GLOBMUSK EVENT TICKET', 0, 0);

// TICKET ID (right side)
$pdf->SetFont('helvetica','B',10);
$pdf->SetXY(140, 12);
$pdf->Cell(60, 10, 'TICKET ID: GM-'.$ticket['id'], 0, 0, 'R');

/* ================= MAIN TICKET BODY ================= */
$pdf->SetTextColor(0,0,0);

/* Admit One Stamp */
$pdf->SetFont('helvetica','B',20);
$pdf->SetTextColor($color[0],$color[1],$color[2]);
$pdf->SetXY(150,40);
$pdf->Cell(40,10,'ADMIT ONE',0,1);

/* Event Title */
$pdf->SetFont('helvetica','B',16);
$pdf->SetTextColor(0,0,0);
$pdf->SetXY(15,40);
$pdf->Cell(120,10,$ticket['event_name'],0,1);

/* Ticket Box */
$pdf->SetFillColor(245,245,245);
$pdf->RoundedRect(10, 55, 190, 85, 5, '1111', 'F');

/* Details */
$pdf->SetFont('helvetica','',11);

$html = '
<table cellpadding="5">
<tr>
<td width="50%"><b>Name:</b> '.$ticket['user_name'].'</td>
<td width="50%"><b>Email:</b> '.$ticket['email'].'</td>
</tr>

<tr>
<td><b> Ticket Type:</b> '.strtoupper($ticket['ticket_type']).'</td>
<td><b>Location:</b> '.$ticket['location'].'</td>
</tr>

<tr>
<td><b>Purchased:</b> '.$ticket['purchase_date'].'</td>
<td><b>Status:</b> '.ucfirst($ticket['payment_status']).'</td>
</tr>

<tr>
<td><b>Amount:</b>NGN'.number_format((float)$ticket['amount'],2).'</td>
</tr>
<tr>
<td><b> Event Date:</b> '.$ticket['event_date'].'</td>
</tr>
<tr><td><b>Description:</b> '.$ticket['description'].'</td></tr>

<tr>
<td><b>Ticket Code:</b> '.$ticket['ticket_code'].'</td>
</tr>
</table>
';

$pdf->writeHTMLCell(0,0,15,60,$html,0,1,false,true);

/* ================= PERFORATION LINE ================= */
$pdf->SetLineStyle(['width'=>0.3,'dash'=>3]);
$pdf->Line(140, 55, 140, 140);

/* ================= QR CODE ================= */
/* ================= QR SETUP ================= */
$tmp_folder = '../tmp_qr/';
if(!is_dir($tmp_folder)){
    mkdir($tmp_folder, 0777, true);
}

$tmpfile = $tmp_folder . 'ticket_' . $ticket['id'] . '.png';

// Generate QR
QRcode::png($scan_url, $tmpfile, 'L', 4, 2);

// Ensure file exists
clearstatcache();
if(!file_exists($tmpfile)){
    die("QR Code not generated");
}

/* ================= QR DISPLAY ================= */
$pdf->Image(realpath($tmpfile), 145, 75, 45, 45, 'PNG');

$pdf->SetFont('helvetica','',9);
$pdf->SetXY(145, 122);
$pdf->Cell(45,5,'Scan for Entry',0,1,'C');


/* Optional Barcode-style text */
$pdf->SetFont('courier','',10);
$pdf->SetXY(15, 145);
$pdf->Cell(0,5,'|| GM-'.$ticket['id'].' || EVENT PASS || GLOBMUSK ||',0,1);

/* ================= FOOTER ================= */
/* ================= FOOTER ================= */
$pdf->SetY(-20);
$pdf->SetTextColor($color[0],$color[1],$color[2]);
$pdf->SetFont('helvetica','I',10);
$pdf->Cell(0,10,'Enjoy your event — Globmusk Experience',0,1,'C');

/* 🔥 CLEAN OUTPUT BUFFER BEFORE PDF */
if(ob_get_length()){
    ob_end_clean();
}

$pdf->Output('ticket_'.$ticket['id'].'.pdf','D');
exit;
?>