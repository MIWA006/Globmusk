<?php
session_start();
include('../includes/db.php');

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

if(isset($_POST['confirm_payment'])){
    $ticket_id = intval($_POST['ticket_id']);
    $user_id = $_SESSION['user_id'];

    // ---------------- FILE CHECK ----------------
    if(!isset($_FILES['receipt'])){
        die("No file uploaded. Please select a receipt.");
    }

    $file = $_FILES['receipt'];

    // Check upload errors
    if($file['error'] !== 0){
        $errors = [
            1 => "File exceeds the upload_max_filesize in php.ini",
            2 => "File exceeds MAX_FILE_SIZE in HTML form",
            3 => "File partially uploaded",
            4 => "No file uploaded",
            6 => "Missing temporary folder",
            7 => "Failed to write file to disk",
            8 => "File upload stopped by extension"
        ];
        $error_msg = isset($errors[$file['error']]) ? $errors[$file['error']] : "Unknown upload error";
        die("Upload failed: $error_msg");
    }

    // ---------------- VALIDATE FILE TYPE ----------------
    $allowed = ['jpg','jpeg','png','pdf'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if(!in_array($ext, $allowed)){
        die("Invalid file type. Allowed: JPG, PNG, PDF.");
    }

    // ---------------- PREPARE UPLOAD PATH ----------------
    $upload_dir = "../uploads/receipts/";
    if(!file_exists($upload_dir)){
        mkdir($upload_dir, 0777, true); // create folder if missing
    }
    if(!is_writable($upload_dir)){
        die("Uploads folder is not writable. Check permissions!");
    }

    $receipt_name = time().'_'.$file['name'];
    $upload_path = $upload_dir.$receipt_name;

    // ---------------- MOVE FILE ----------------
    if(!move_uploaded_file($file['tmp_name'], $upload_path)){
        die("Failed to move uploaded file.");
    }

    // ---------------- UPDATE TICKET ----------------
    $stmt = mysqli_prepare($conn, "UPDATE tickets SET payment_status='pending', receipt=? WHERE id=? AND user_id=?");
    mysqli_stmt_bind_param($stmt, "sii", $receipt_name, $ticket_id, $user_id);
    mysqli_stmt_execute($stmt);

    if(mysqli_stmt_affected_rows($stmt) > 0){
        header("Location: tickets.php?msg=Payment submitted, awaiting approval");
        exit();
    } else {
        die("Failed to update ticket. Please try again.");
    }

} else {
    die("Invalid access.");
}
?>