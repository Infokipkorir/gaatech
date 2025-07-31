<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'] ?? $_POST['user_id'] ?? 0;
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if ($subject === '' || $message === '') {
        echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $user_id, $subject, $message);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Message sent successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Could not send message. Try later.']);
    }

    $stmt->close();
}
