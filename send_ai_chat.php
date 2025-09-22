<?php
session_start();
require 'config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$message = trim($_POST['message'] ?? '');

if (!$user_id || !$message) {
    echo json_encode(['response' => 'Invalid request']);
    exit;
}


// Simulated AI reply
function ai_reply($msg) {
    $msg = strtolower($msg);
    if (strpos($msg, 'hello') !== false) return "Hi there! How can I help you?";
    if (strpos($msg, 'problem') !== false) return "Sorry to hear that. Please explain the issue in detail.";
    return "Thank you. We’ll get back to you shortly.";
}

$response = ai_reply($message);

// Save
$stmt = $pdo->prepare("INSERT INTO support_chats (user_id, message, response) VALUES (?, ?, ?)");
$stmt->execute([$user_id, $message, $response]);

echo json_encode(['response' => $response]);
