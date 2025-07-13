<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user already activated trial
$stmt = $conn->prepare("SELECT role, trial_started_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role, $trial_started_at);
$stmt->fetch();
$stmt->close();

// If already trial or upgraded
if ($role === 'trial' || $role === 'pro') {
    header("Location: dashboard.php?trial=already_used");
    exit();
}

// Activate trial: update role and timestamp
$now = date('Y-m-d H:i:s');
$update = $conn->prepare("UPDATE users SET role = 'trial', trial_started_at = ? WHERE id = ?");
$update->bind_param("si", $now, $user_id);

if ($update->execute()) {
    header("Location: dashboard.php?trial=success");
} else {
    header("Location: dashboard.php?trial=failed");
}
exit();
