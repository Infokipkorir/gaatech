<?php
include 'admin_auth.php';
include '../conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = $_POST['to_user_id'];
    $message = $_POST['message'];
    $subject = $_POST['subject'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, to_user_id, message, subject, is_support) VALUES (0, ?, ?, ?, 1)");
    $stmt->bind_param("iss", $to, $message, $subject);
    $stmt->execute();
    $stmt->close();

    // Optional: add notification logic if using separate notifications table

    header("Location: admin_support.php?success=1");
    exit();
}
?>
