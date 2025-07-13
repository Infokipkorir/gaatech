<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle support form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    if (empty($subject) || empty($message)) {
        $error = "All fields are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO messages (from_user, to_user, message, created_at) VALUES (?, 0, ?, NOW())");
        $stmt->bind_param("is", $user_id, $message);
        if ($stmt->execute()) {
            $success = "✅ Message sent to support successfully.";
        } else {
            $error = "❌ Failed to send message. Try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support - Gaatech QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .support-container {
      max-width: 700px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Gaatech QR</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light me-2"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="support-container">
  <h3 class="text-primary mb-4">📨 Contact Support</h3>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php elseif ($error): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="support.php">
    <div class="mb-3">
      <label for="subject" class="form-label">Subject</label>
      <input type="text" name="subject" id="subject" class="form-control" required>
    </div>
    <div class="mb-3">
      <label for="message" class="form-label">Message / Issue</label>
      <textarea name="message" id="message" rows="6" class="form-control" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Message</button>
  </form>
</div>

</body>
</html>
