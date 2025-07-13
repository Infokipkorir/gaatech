<?php if (isset($_GET['token'])): ?>
  <div class="alert alert-info mt-3">
    Changed your mind? <br>
    <a href="recover_account.php?token=<?= htmlspecialchars($_GET['token']) ?>" class="btn btn-outline-primary mt-2">Recover My Account</a> <br>
    <small>This link is valid for 24 hours.</small>
  </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Goodbye - Gaatech QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      text-align: center;
      padding-top: 100px;
    }
    .goodbye-card {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    .goodbye-icon {
      font-size: 60px;
      color: #dc3545;
    }
  </style>
</head>
<body>

<div class="goodbye-card">
  <div class="goodbye-icon">👋</div>
  <h2 class="mt-3 text-danger">Goodbye from Gaatech QR</h2>
  <p class="text-muted mt-3">Your account has been successfully deleted.</p>
  <p>We’re sad to see you go. If you change your mind, you're always welcome to join again!</p>
  <a href="register.php" class="btn btn-primary mt-3"><i class="fas fa-user-plus"></i> Register Again</a>
  <a href="index.php" class="btn btn-outline-secondary mt-3 ms-2"><i class="fas fa-home"></i> Go to Home</a>
</div>

</body>
</html>
