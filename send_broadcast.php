<?php
require_once 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $_SESSION['user_role'] === 'admin') {
    $message = trim($_POST['message']);
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) SELECT ?, id, ? FROM users WHERE id != ?");
    $admin_id = $_SESSION['user_id'];
    $stmt->bind_param("isi", $admin_id, $message, $admin_id);
    $stmt->execute();
}
header("Location: admin_messages.php");
exit();
