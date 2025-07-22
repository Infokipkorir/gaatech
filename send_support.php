<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';

$user_id = $_SESSION['user_id'];
$issue = $_POST['issue'] ?? '';
$message = $_POST['message'] ?? '';

if ($issue && $message) {
    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, issue, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
    $stmt->bind_param("iss", $user_id, $issue, $message);
    if ($stmt->execute()) {
        header("Location: support.php?sent=1");
    } else {
        echo "Error sending message: " . $conn->error;
    }
    $stmt->close();
} else {
    echo "Missing fields.";
}
?>
