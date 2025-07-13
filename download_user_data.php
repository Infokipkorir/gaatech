<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, role, plan, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="my_data.json"');
echo json_encode($userData, JSON_PRETTY_PRINT);
exit;
