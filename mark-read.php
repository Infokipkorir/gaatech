<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    http_response_code(403);
    exit();
}

$id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE id = ? AND to_user = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
