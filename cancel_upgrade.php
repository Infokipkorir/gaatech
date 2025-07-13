<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if user is Pro before canceling
$stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();

if ($role !== 'pro') {
    header("Location: upgraded_user.php?status=not_pro");
    exit();
}

// Update role back to 'user'
$updateUser = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
$updateUser->bind_param("i", $user_id);
$updateUser->execute();
$updateUser->close();

// Delete upgrade record (optional if you want to clear upgrade history)
$deleteUpgrade = $conn->prepare("DELETE FROM upgrades WHERE user_id = ?");
$deleteUpgrade->bind_param("i", $user_id);
$deleteUpgrade->execute();
$deleteUpgrade->close();

// Redirect with success message
header("Location: upgraded_user.php?status=downgraded");
exit();
?>
