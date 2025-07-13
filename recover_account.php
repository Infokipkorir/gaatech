<?php
require_once 'db.php';

$token = $_GET['token'] ?? '';

if (!$token) {
  exit('Invalid or missing token.');
}

// Fetch user from backup
$stmt = $conn->prepare("SELECT * FROM deleted_users WHERE token = ? AND deleted_at >= NOW() - INTERVAL 1 DAY");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  exit('Recovery link expired or already used.');
}

$user = $result->fetch_assoc();

// Restore to users table
$restore = $conn->prepare("INSERT INTO users (id, name, email, password, role, plan, plan_expiry, trial_started_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$restore->bind_param("isssssss", $user['original_user_id'], $user['name'], $user['email'], $user['password'], $user['role'], $user['plan'], $user['plan_expiry'], $user['trial_started_at']);
$restore->execute();

// Clean up
$cleanup = $conn->prepare("DELETE FROM deleted_users WHERE id = ?");
$cleanup->bind_param("i", $user['id']);
$cleanup->execute();

// Redirect to login
header("Location: login.php?recovered=1");
exit();
