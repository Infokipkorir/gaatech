<?php
session_start();
require 'config.php';

//Check if admin
$stmt = $pdo->query("SELECT * FROM support_chats ORDER BY created_at DESC");

echo "<h2>All Support Chats</h2><hr>";
while ($row = $stmt->fetch()) {
    echo "<p><strong>User {$row['user_id']}:</strong> {$row['message']}<br>";
    echo "<strong>AI:</strong> {$row['response']}<br><small>{$row['created_at']}</small></p><hr>";
}
