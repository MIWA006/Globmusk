<?php
session_start();
include('../includes/db.php');

// 🔒 Check login
if(!isset($_SESSION['user_id']) || !isset($_GET['ticket_id'])){
    header("Location: tickets.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ticket_id = intval($_GET['ticket_id']);

// Fetch ticket info
$ticket_q = mysqli_query($conn, "
    SELECT t.*, e.title, e.event_date, e.location, e.description
    FROM tickets t
    JOIN events e ON t.event_id = e.id
    WHERE t.id='$ticket_id' AND t.user_id='$user_id'
");
if(mysqli_num_rows($ticket_q)==0){
    die("Ticket not found.");
}
$ticket = mysqli_fetch_assoc($ticket_q);

// --- Update status logic ---
if($ticket['scan_status'] == 'unused') {
    mysqli_query($conn,"UPDATE tickets SET scan_status='verifying3' WHERE id='$ticket_id'");
} elseif($ticket['scan_status'] == 'verifying') {
    mysqli_query($conn,"UPDATE tickets SET scan_status='expired' WHERE id='$ticket_id'");
    $ticket['scan_status'] = 'expired';
}

// Include TCPDF
require_once('../lib/tcpdf/tcpdf.php');
include('../lib/phpqrcode/qrlib.php');

$tmp_folder = '../tmp_qr/';
if(!is_dir($tmp_folder)) mkdir($tmp_folder, 0777, true);

$qr_data = json_encode([
    'ticket_id'=>$ticket['id'],
    'user_id'=>$user_id,
    'event'=>$ticket['title'],
    'location'=>$ticket['location'],
    'description'=>$ticket['description'],
    'type'=>$ticket['ticket_type'],
    'amount'=>$ticket['amount'],
    'status'=>$ticket['scan_status'] // updated status
]);
$tmpfile = $tmp_folder.'ticket_'.$ticket['id'].'.png';
QRcode::png($qr_data, $tmpfile, 'L', 4, 2);

// PDF generation (unchanged)
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('Globmusk');
$pdf->SetAuthor('Globmusk');
$pdf->SetTitle('Event Ticket');
$pdf->SetMargins(15, 20, 15);
$pdf->AddPage();

// Watermark
$pdf->SetAlpha(0.1);
$pdf->SetFont('helvetica', 'B', 50);
$pdf->SetTextColor(150,150,150);
$pdf->StartTransform();
$pdf->Rotate(45,105,140);
$pdf->Text(30, 120, 'GLOBMUSK');
$pdf->StopTransform();
$pdf->SetAlpha(1);

// Ticket Header
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor(106,13,173);
$pdf->Cell(0,10,'Event Ticket',0,1,'C');
$pdf->Ln(5);

// Ticket details
$pdf->SetFont('helvetica','',12);
$pdf->SetTextColor(0,0,0);

$html = '
<table cellpadding="5">
<tr><td><strong>Event:</strong></td><td>'.htmlspecialchars($ticket['title']).'</td></tr>
<tr><td><strong>Location:</strong></td><td>'.htmlspecialchars($ticket['location']).'</td></tr>
<tr><td><strong>Description:</strong></td><td>'.htmlspecialchars($ticket['description']).'</td></tr>
<tr><td><strong>Ticket Type:</strong></td><td>'.strtoupper($ticket['ticket_type']).'</td></tr>
<tr><td><strong>Amount Paid:</strong></td><td>₦'.number_format($ticket['amount'],2).'</td></tr>
<tr><td><strong>Event Date:</strong></td><td>'.$ticket['event_date'].'</td></tr>
<tr><td><strong>Purchase Date:</strong></td><td>'.$ticket['purchase_date'].'</td></tr>
<tr><td><strong>Status:</strong></td><td>'.$ticket['scan_status'].'</td></tr>
</table>
';

$pdf->writeHTML($html, true, false, false, false, '');

// QR Code
$pdf->Ln(10);
$pdf->Image($tmpfile, 80, $pdf->GetY(), 50, 50, 'PNG');

// Footer
$pdf->Ln(60);
$pdf->SetFont('helvetica','I',10);
$pdf->SetTextColor(106,13,173);
$pdf->Cell(0,10,'Thank you for using Globmusk!',0,1,'C');

// Output
$pdf->Output('ticket_'.$ticket['id'].'.pdf', 'D');
?>