<!-- admin_support.php -->
<?php
include 'admin_auth.php'; // admin session
include 'conn.php';

$supportMessages = [];
$sql = "SELECT m.*, u.full_name, u.email 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.is_support = 1 
        ORDER BY m.created_at DESC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $supportMessages[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support Messages - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .support-card {
      border-left: 4px solid #0d6efd;
      background: #f9fbff;
    }
  </style>
</head>
<body>
<div class="container py-4">
  <h3 class="mb-4 text-primary">Support Messages</h3>

  <?php foreach ($supportMessages as $msg): ?>
    <div class="card mb-3 support-card shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-primary"><?= htmlspecialchars($msg['full_name']) ?> (<?= $msg['email'] ?>)</h5>
        <p class="card-text"><strong>Issue:</strong> <?= htmlspecialchars($msg['issue']) ?></p>
        <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
        <small class="text-muted">Sent on <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></small>
        <form class="mt-3" action="send_reply.php" method="POST">
          <input type="hidden" name="to_user_id" value="<?= $msg['sender_id'] ?>">
          <input type="hidden" name="subject" value="Support Reply">
          <div class="input-group">
            <input type="text" name="message" class="form-control" placeholder="Write a reply..." required>
            <button class="btn btn-primary" type="submit">Send</button>
          </div>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($supportMessages)): ?>
    <p class="text-muted">No support messages yet.</p>
  <?php endif; ?>
</div>
</body>
</html>
