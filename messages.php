<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Send message
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recipient_id'], $_POST['message'])) {
    $recipient_id = (int) $_POST['recipient_id'];
    $message = trim($_POST['message']);

    if ($recipient_id && $message) {
        $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, message, is_read, created_at) VALUES (?, ?, ?, 0, NOW())");
        $stmt->bind_param("iis", $user_id, $recipient_id, $message);
        if ($stmt->execute()) {
            $success = "✅ Message sent successfully!";
        } else {
            $error = "❌ Failed to send message.";
        }
    } else {
        $error = "⚠️ All fields are required.";
    }
}

// Fetch users for recipient dropdown
$users = $conn->query("SELECT id, name FROM users WHERE id != $user_id ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Message - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h3 class="text-primary mb-4">📩 Send a Notification Message</h3>

    <?php if ($success): ?>
      <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 shadow-sm">
      <div class="mb-3">
        <label for="recipient_id" class="form-label">Select User</label>
        <select name="recipient_id" id="recipient_id" class="form-select" required>
          <option value="">-- Choose User --</option>
          <?php while ($user = $users->fetch_assoc()): ?>
            <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea name="message" id="message" class="form-control" rows="4" required placeholder="Type your message..."></textarea>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
    </form>
  </div>

  <script src="https://kit.fontawesome.com/a2d2a0e1a8.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footerr.php' ?>