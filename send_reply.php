<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['email']) && $_SESSION['user_role'] === 'admin') {
    $message = trim($_POST['message']);
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($receiver_id);
    if ($stmt->fetch()) {
        $stmt->close();
        $stmt2 = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $_SESSION['user_id'], $receiver_id, $message);
        $stmt2->execute();
    }
}
header("Location: admin_messages.php");
exit();
