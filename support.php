<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

include 'conn.php';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Support | Gaatech</title>
  <link rel="icon" type="image/png" href="admin\assets\Gaatech logo2.jpg">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f0f4f8;
      font-family: 'Segoe UI', sans-serif;
    }
    .support-wrapper {
      max-width: 600px;
      margin: 60px auto;
    }
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }
    .card-header {
      background-color: #0d6efd;
      color: white;
      border-radius: 12px 12px 0 0;
    }
    .btn-primary {
      background-color: #0d6efd;
      border: none;
    }
    .btn-primary:hover {
      background-color: #0b5ed7;
    }
  </style>
</head>
<body>

<div class="container support-wrapper">
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Support Center</h5>
    </div>
    <div class="card-body">
      <p>Hello <strong><?= htmlspecialchars($user_name) ?></strong>, I’m <strong>Alex</strong>. How can I help?</p>

      <?php if (isset($_GET['sent'])): ?>
        <div class="alert alert-success">✅ Message sent successfully!</div>
      <?php endif; ?>

      <form action="send_support.php" method="POST">
        <div class="mb-3">
          <label for="issue" class="form-label">Select Issue</label>
          <select name="issue" id="issue" class="form-select" required>
            <option value="">-- Choose an issue --</option>
            <option>Login Problem</option>
            <option>Upgrade Request</option>
            <option>QR Code Error</option>
            <option>Billing</option>
            <option>Other</option>
          </select>
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Your Message</label>
          <textarea name="message" id="message" rows="4" class="form-control" placeholder="Write your message..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send Message</button>
      </form>
    </div>
  </div>
</div>

</body>
</html>
<?php include 'footer.php' ?>