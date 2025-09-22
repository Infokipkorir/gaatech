<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    die();
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM ai_support WHERE user_id = ? ORDER BY created_at ASC");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

foreach ($messages as $msg) {
    $class = $msg['role'] === 'user' ? 'msg-user' : 'msg-ai';
    $label = $msg['role'] === 'user' ? 'Me' : 'Support';
    $time = date("H:i", strtotime($msg['created_at']));
    echo "<div class='$class'><strong>$label:</strong><br>{$msg['message']}<div class='time'>$time</div></div>";
}
