<?php
include('../includes/db.php');

$q=mysqli_query($conn,"
SELECT u.name,e.title
FROM tickets t
JOIN users u ON t.user_id=u.id
JOIN events e ON t.event_id=e.id
ORDER BY t.id DESC
LIMIT 5
");

$data=[];
while($r=mysqli_fetch_assoc($q)){
$data[]=$r;
}

echo json_encode($data);
?>