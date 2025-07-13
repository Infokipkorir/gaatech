<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Simulate payment success (normally you'd verify with Stripe, PayPal etc.)
$card_name = $_POST['card_name'] ?? '';
$card_number = $_POST['card_number'] ?? '';
$expiry = $_POST['expiry'] ?? '';
$cvv = $_POST['cvv'] ?? '';

if ($card_name && $card_number && $expiry && $cvv) {
    // Mark user as Pro
    $stmt = $conn->prepare("UPDATE users SET plan = 'Pro' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "🎉 Payment successful! You are now a Pro user.";
        header("Location: upgrade_success.php");
    } else {
        echo "Error upgrading plan.";
    }
} else {
    echo "Invalid payment details.";
}
