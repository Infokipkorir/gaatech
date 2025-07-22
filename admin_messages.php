<?php
session_start();
require_once 'db.php'; 

// Fetch all user messages
$stmt = $conn->prepare("SELECT m.id, m.message, m.created_at, u.name, u.email FROM messages m JOIN users u ON m.sender_id = u.id ORDER BY m.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Messages - Gaatech</title>
  <link rel="icon" type="image/png" href="admin\assets\Gaatech logo2.jpg">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .message-card { background: #fff; border-left: 4px solid #0d6efd; margin-bottom: 1rem; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
    .message-card small { color:rgb(5, 138, 255); }
  </style>
</head>
<body>
  
<div class="container mt-4">
  <h3 class="text-primary mb-3"><i class="fas fa-inbox me-2"></i>Admin Messages Panel</h3>
  <a href="admin_dashboard.php" class="btn btn-outline-dark"><i class="fas fa-arrow-left"></i> Back</a>
  <!-- Broadcast Button -->
  <div class="mb-3">
    <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#broadcastModal">
      <i class="fas fa-bullhorn me-1"></i> Broadcast to All Users
    </button>
  </div>

  <?php if ($result->num_rows > 0): ?>
    <?php while ($row = $result->fetch_assoc()): ?>
      <div class="message-card rounded">
        <div class="d-flex justify-content-between">
          <div>
            <strong><?= htmlspecialchars($row['name']) ?> (<?= htmlspecialchars($row['email']) ?>)</strong><br>
            <small><?= date('F j, Y g:i A', strtotime($row['created_at'])) ?></small>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#replyModal" data-id="<?= $row['id'] ?>" data-name="<?= htmlspecialchars($row['name']) ?>" data-email="<?= htmlspecialchars($row['email']) ?>">
              <i class="fas fa-reply"></i> Reply
            </button>
          </div>
        </div>
        <p class="mt-2"><?= nl2br(htmlspecialchars($row['message'])) ?></p>
      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <div class="alert alert-info">📭 No messages yet.</div>
  <?php endif; ?>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="send_broadcast.php" class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title">Broadcast Message</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        <a href="admin_dashboard.php" class="btn btn-outline-light me-2"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      <div class="modal-body">
        <textarea name="message" class="form-control" rows="5" placeholder="Type your broadcast message..." required></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Send Broadcast</button>
      </div>
    </form>
  </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="send_reply.php" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Reply to User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="email" id="replyEmail">
        <div class="mb-2">
          <label>User:</label>
          <input type="text" class="form-control" id="replyUser" readonly>
        </div>
        <textarea name="message" class="form-control" rows="5" placeholder="Write your reply..." required></textarea>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button class="btn btn-primary" type="submit">Send Reply</button>
      </div>
    </form>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('replyModal').addEventListener('show.bs.modal', function (event) {
  const button = event.relatedTarget;
  const name = button.getAttribute('data-name');
  const email = button.getAttribute('data-email');
  document.getElementById('replyUser').value = name;
  document.getElementById('replyEmail').value = email;
});
</script>
</body>
</html>
<?php include 'footer.php' ?>