<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$method = $_POST['method'] ?? '';
$contact = '';

if ($method === 'mpesa' && !empty($_POST['mpesa_number'])) {
  $contact = $_POST['mpesa_number'];
} elseif ($method === 'airtel' && !empty($_POST['airtel_number'])) {
  $contact = $_POST['airtel_number'];
} elseif ($method === 'paypal' && !empty($_POST['paypal_email'])) {
  $contact = $_POST['paypal_email'];
} else {
  die("❌ Invalid or missing payment info.");
}

// Save payment
$stmt = $conn->prepare("INSERT INTO payments (user_id, method, phone_email, status) VALUES (?, ?, ?, 'pending')");
$stmt->bind_param("iss", $user_id, $method, $contact);
if ($stmt->execute()) {
  header("Location: payment_success.php?status=pending");
} else {
  echo "❌ Failed to record payment. Try again.";
}
