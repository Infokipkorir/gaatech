<?php
session_start();

// Check admin session
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Include DB connection
include 'conn.php'; // Make sure conn.php is in the same folder

// Fetch support messages
$supportMessages = [];
$sql = "SELECT m.*, u.name, u.email 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.is_support = 1 
        ORDER BY m.created_at DESC";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $supportMessages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Support Messages</title>
  <link rel="icon" type="image/png" href="admin\assets\Gaatech logo2.jpg">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .support-card {
      border-left: 4px solid #0d6efd;
      background-color: #fff;
    }
    .support-header {
      background-color: #0d6efd;
      color: #fff;
      padding: 1rem;
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
  <div class="support-header text-center">
    <h2>📬 Admin Support Messages</h2>
  </div>

  <div class="container">
    <?php if (!empty($supportMessages)): ?>
      <?php foreach ($supportMessages as $msg): ?>
        <div class="card mb-4 shadow support-card">
          <div class="card-body">
            <h5 class="card-title text-primary">
              <?= htmlspecialchars($msg['name']) ?> (<?= htmlspecialchars($msg['email']) ?>)
            </h5>
            <p><strong>Issue:</strong> <?= htmlspecialchars($msg['issue']) ?></p>
            <p><?= nl2br(htmlspecialchars($msg['message'])) ?></p>
            <small class="text-muted">Sent on <?= date('M j, Y H:i', strtotime($msg['created_at'])) ?></small>

            <form class="mt-3" method="POST" action="send_reply.php">
              <input type="hidden" name="to_user_id" value="<?= $msg['sender_id'] ?>">
              <input type="hidden" name="subject" value="Support Reply">
              <div class="input-group">
                <input type="text" name="message" class="form-control" placeholder="Write a reply..." required>
                <button type="submit" class="btn btn-primary">Reply</button>
              </div>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="alert alert-info text-center">No support messages yet.</div>
    <?php endif; ?>
  </div>
</body>
</html>
