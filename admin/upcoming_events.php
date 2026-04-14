<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if(!isset($_SESSION['admin_id'])){
    header("Location: login.php");
    exit();
}


// ---------------- ADD EVENT ----------------
$message = "";
date_default_timezone_set('Africa/Lagos');

if(isset($_POST['add_event'])){

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $event_date = str_replace('T',' ',$_POST['event_date']). ':00';

    // ✅ ADD THIS HERE
    // ✅ SAFE VALUES
$early_bird_deadline = !empty($_POST['early_bird_end']) 
    ? date("Y-m-d H:i:s", strtotime($_POST['early_bird_end'])) 
    : null;

$table_4_total = !empty($_POST['table_4_total']) 
    ? intval($_POST['table_4_total']) 
    : 0;

$table_5_total = !empty($_POST['table_5_total']) 
    ? intval($_POST['table_5_total']) 
    : 0;

$table_6_total = !empty($_POST['table_6_total']) 
    ? intval($_POST['table_6_total']) 
    : 0;
    // Prices
    $early_bird = isset($_POST['early_bird_price']) ? (float)$_POST['early_bird_price'] : 0;
$walk_in = isset($_POST['walk_in_price']) ? (float)$_POST['walk_in_price'] : 0;
$vip = isset($_POST['vip_price']) ? (float)$_POST['vip_price'] : 0;

$table4 = isset($_POST['table_4_price']) ? (float)$_POST['table_4_price'] : 0;
$table5 = isset($_POST['table_5_price']) ? (float)$_POST['table_5_price'] : 0;
$table6 = isset($_POST['table_6_price']) ? (float)$_POST['table_6_price'] : 0;

$table_4_limit = isset($_POST['table_4_limit']) ? (int)$_POST['table_4_limit'] : 0;
$table_5_limit = isset($_POST['table_5_limit']) ? (int)$_POST['table_5_limit'] : 0;
$table_6_limit = isset($_POST['table_6_limit']) ? (int)$_POST['table_6_limit'] : 0;

    if(!is_dir("../uploads/events")) mkdir("../uploads/events", 0777, true);

    $filename = time() . "_" . basename($_FILES['image']['name']);
    $upload_path = "../uploads/events/" . $filename;

    if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)){

        // Resize image
       if (file_exists($upload_path)) {

    $imageInfo = getimagesize($upload_path);

    if ($imageInfo !== false) {

        list($width, $height) = $imageInfo;

        // prevent crash
        if ($width > 0 && $height > 0) {

            $target_width = 800;
            $target_height = 450;

            $ratio_orig = $width / $height;

            if ($target_height == 0) $target_height = 450;

            if ($target_width / $target_height > $ratio_orig) {
                $target_width = (int) ($target_height * $ratio_orig);
            } else {
                $target_height = (int) ($target_width / $ratio_orig);
            }

            $src = @imagecreatefromstring(file_get_contents($upload_path));

            if ($src) {

                $dst = imagecreatetruecolor($target_width, $target_height);

                imagecopyresampled(
                    $dst, $src,
                    0, 0, 0, 0,
                    $target_width, $target_height,
                    $width, $height
                );

                imagejpeg($dst, $upload_path, 90);

                imagedestroy($src);
                imagedestroy($dst);
            }
        }
    }
}

        // ---------------- INSERT INTO UPCOMING EVENTS ----------------
   // ---------------- INSERT INTO UPCOMING EVENTS ----------------
mysqli_query($conn, "INSERT INTO upcoming_events 
(title, description, location, event_date,
early_bird_price, walk_in_price, vip_price,
table_4_price, table_5_price, table_6_price,
early_bird_end,
table_4_limit, table_5_limit, table_6_limit,
image)
VALUES 
('$title','$description','$location','$event_date',
'$early_bird','$walk_in','$vip',
'$table4','$table5','$table6',
'$early_bird_deadline',
'$table_4_limit','$table_5_limit','$table_6_limit',
'$filename')");

$event_id = mysqli_insert_id($conn);
    

        // ---------------- INSERT INTO EVENTS (USER SIDE) ----------------
  // ---------------- INSERT INTO EVENTS (USER SIDE) ----------------
mysqli_query($conn, "INSERT INTO events 
(title, description, location, event_date,
early_bird_price, walk_in_price, vip_price,
table_4_price, table_5_price, table_6_price,
early_bird_end,
table_4_limit, table_5_limit, table_6_limit,
image,
event_id_link)
VALUES 
('$title','$description','$location','$event_date',
'$early_bird','$walk_in','$vip',
'$table4','$table5','$table6',
'$early_bird_deadline',
'$table_4_limit','$table_5_limit','$table_6_limit',
'$filename',
'$event_id')");
        $message = "Event added successfully!";

    } else {
        $message = "Image upload failed!";
    }
}

// ---------------- DELETE EVENT ----------------
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);

    $res = mysqli_query($conn, "SELECT * FROM upcoming_events WHERE id='$id'");
    $row = mysqli_fetch_assoc($res);

    if($row){

        $image = $row['image'];

        // delete from upcoming
        mysqli_query($conn, "DELETE FROM upcoming_events WHERE id='$id'");

        // delete from events using link
        mysqli_query($conn, "DELETE FROM events WHERE event_id_link='$id'");

        if(file_exists("../uploads/events/$image")){
            unlink("../uploads/events/$image");
        }
    }

    $message = "Event deleted successfully!";
}

// Fetch events
$events = mysqli_query($conn, "SELECT * FROM upcoming_events ORDER BY event_date ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Manage Events</title>

<style>
/* =========================
   DARK SAAS THEME FIX
========================= */
*{
    box-sizing:border-box;
}
body{
    margin:0;
    font-family:'Segoe UI', sans-serif;
    background:#0a0f1f;
    color:#e5e7eb;
}

/* HEADER */
header{
    background:#111827;
    color:#a855f7;
    padding:15px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.05);
}

/* CONTAINER */
.container{
    max-width:1100px;
    width:100%;
    margin:90px auto 40px;
    padding:20px;
    overflow-x:hidden;
}

/* FORM BOX (IMPORTANT FIX) */
form{
    background:#111827;
    padding:20px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,0.05);
    box-shadow:0 10px 30px rgba(0,0,0,0.4);
}

/* INPUTS */
input, textarea{
    width:100%;
    padding:12px;
    margin:8px 0 15px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.08);
    background:#0f172a;
    color:#fff;
    outline:none;
}

/* FOCUS EFFECT */
input:focus, textarea:focus{
    border:1px solid #a855f7;
    box-shadow:0 0 0 3px rgba(168,85,247,0.15);
}

/* BUTTON */
button{
    width:100%;
    padding:12px;
    background:linear-gradient(90deg,#a855f7,#6366f1);
    border:none;
    border-radius:10px;
    color:white;
    font-weight:600;
    cursor:pointer;
}

/* TABLE */
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
    background:#111827;
    border-radius:12px;
    overflow:hidden;
}

th{
    background:#a855f7;
    padding:12px;
    color:white;
}

td{
    padding:12px;
    text-align:center;
    color:#d1d5db;
    border-bottom:1px solid rgba(255,255,255,0.05);
}

tr:hover{
    background:rgba(168,85,247,0.08);
}

/* IMAGE */
img.event-img{
    width:90px;
    height:60px;
    object-fit:cover;
    border-radius:6px;
}

/* DELETE BUTTON */
a.delete-btn{
    color:#ef4444;
    font-weight:bold;
}

/* MESSAGE */
.msg{
    color:#22c55e;
    text-align:center;
    margin-bottom:10px;
}
</style>
</head>

<body>

<header>
    <h1>Globmusk Admin Dashboard</h1>
    <a href="dashboard.php" style="color:white;">Back</a>
</header>

<div class="container">

<h2>Add New Event</h2>

<div class="container">

<?php if($message) echo "<p class='msg'>$message</p>"; ?>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="Event Title" required>
    <textarea name="description" placeholder="Event Description" required></textarea>
    <input type="text" name="location" placeholder="Location" required>
    <input type="datetime-local" name="event_date" required>

    <h3>Ticket Prices</h3>
    <input type="number" name="early_bird_price" placeholder="Early Bird Price" required>
    <input type="number" name="walk_in_price" placeholder="Walk-in Price" required>
    <input type="number" name="vip_price" placeholder="VIP Price" required>

   <h3>Table Packages</h3>

<input type="number" name="table_4_price" placeholder="Table for 4 Price" required>
<input type="number" name="table_5_price" placeholder="Table for 5 Price" required>
<input type="number" name="table_6_price" placeholder="Table for 6 Price" required>
<h3>Early Bird Countdown</h3>
<input type="datetime-local" name="early_bird_end">

<h3>Table Availability</h3>
<input type="number" name="table_4_limit" placeholder="Table 4 Limit">
<input type="number" name="table_5_limit" placeholder="Table 5 Limit">
<input type="number" name="table_6_limit" placeholder="Table 6 Limit">


    <input type="file" name="image" required>

    <button type="submit" name="add_event">Upload Event</button>
</form>

<h2>Existing Events</h2>

<table>
<tr>
    <th>ID</th>
    <th>Title</th>
    <th>Image</th>
    <th>Date</th>
    <th>Location</th>
    <th>Prices</th>
    <th>Action</th>
</tr>

<?php while($e = mysqli_fetch_assoc($events)): ?>
<tr>
    <td><?= $e['id'] ?></td>
    <td><?= htmlspecialchars($e['title']) ?></td>
    <td>
        <img src="../uploads/events/<?= htmlspecialchars($e['image']) ?>" class="event-img">
    </td>
    <td><?= $e['event_date'] ?></td>
    <td><?= htmlspecialchars($e['location']) ?></td>
    <td style="font-size:12px;">

        Early: ₦<?= number_format($e['early_bird_price']) ?><br>
        Walk-in/Regular: ₦<?= number_format($e['walk_in_price']) ?><br>
        VIP: ₦<?= number_format($e['vip_price']) ?><br><br>

        Table 4: ₦<?= number_format($e['table_4_price']) ?><br>
        Table 5: ₦<?= number_format($e['table_5_price']) ?><br>
        Table 6: ₦<?= number_format($e['table_6_price']) ?>

    </td>

    <td>
        <a class="delete-btn" href="?delete_id=<?= $e['id'] ?>" onclick="return confirm('Delete event?')">
            Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</div>

<footer>
    © 2026 Globmusk Admin Panel
</footer>

</body>
</html>