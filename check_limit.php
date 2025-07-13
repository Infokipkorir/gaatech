<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role === 'pro' || $role === 'trial') {
    echo 'ok';
    exit;
}

$today = date('Y-m-d');
$stmt = $conn->prepare("SELECT COUNT(*) FROM qr_usage WHERE user_id = ? AND DATE(generated_at) = ?");
$stmt->bind_param("is", $user_id, $today);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

if ($count >= 3) {
    echo 'limit_reached';
} else {
    echo 'ok';
}
?>
