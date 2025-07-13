<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) exit("Unauthorized");

$from_user = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_user = (int)$_POST['to_user'];
    $message = trim($_POST['message']);

    if (!$to_user || !$message) exit("Missing fields");

    $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iis", $from_user, $to_user, $message);
    echo $stmt->execute() ? "✅ Message sent!" : "❌ Failed to send";
}
?>
