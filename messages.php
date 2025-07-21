<?php
session_start();
require_once 'db.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch messages (received only)
$stmt = $conn->prepare("
    SELECT m.*, u.name AS sender_name
    FROM messages m
    LEFT JOIN users u ON m.sender_id = u.id
    WHERE m.recipient_id = ? OR (m.is_broadcast = 1 AND m.recipient_id IS NULL)
    ORDER BY m.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">📨 My Notifications</h2>

    <?php if (empty($messages)): ?>
        <div class="alert alert-info">No Notifications yet.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($messages as $msg): ?>
                <div class="list-group-item mb-2 shadow-sm rounded">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>From:</strong>
                            <?= $msg['is_broadcast'] ? '<span class="text-primary">Admin (Broadcast)</span>' : htmlspecialchars($msg['sender_name']) ?>
                        </div>
                        <small class="text-muted"><?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></small>
                    </div>
                    <hr class="my-2">
                    <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <a href="dashboard.php" class="btn btn-secondary mt-4">← Back to Dashboard</a>
</div>
</body>
</html>
