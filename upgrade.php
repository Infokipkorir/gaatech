<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user info
$stmt = $conn->prepare("SELECT plan, role, trial_started_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($plan, $role, $trial_started_at);
$stmt->fetch();
$stmt->close();

// Check if trial was already used
$trial_used = !empty($trial_started_at);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upgrade Plan - Gaatech QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .card-header h1 { font-size: 2rem; }
    .btn-trial { background: #ffc107; color: #000; }
    .btn-trial:hover { background: #e0a800; color: #000; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">Gaatech QR</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light me-2"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- Upgrade Section -->
<div class="container py-5">
  <div class="text-center mb-5">
    <h2>Upgrade Your Plan</h2>
    <p class="text-muted">Choose the best plan for your QR code needs</p>
  </div>

  <div class="row g-4 justify-content-center">
    <!-- Free Plan -->
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-header text-center bg-light">
          <h4>Free Plan</h4>
          <h1 class="text-primary">Ksh 0</h1>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Up to 3 QR codes/day</li>
            <li class="list-group-item"><i class="fas fa-times text-danger me-2"></i> No analytics</li>
            <li class="list-group-item"><i class="fas fa-times text-danger me-2"></i> No branding or customization</li>
            <li class="list-group-item"><i class="fas fa-times text-danger me-2"></i> No priority support</li>
          </ul>
          <div class="d-grid">
            <button class="btn btn-secondary" disabled>Current Plan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Free Trial -->
    <div class="col-md-4">
      <div class="card shadow-sm border-warning border-2">
        <div class="card-header text-center bg-warning text-dark">
          <h4>Free Trial</h4>
          <h1 class="text-dark">7 Days</h1>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Unlimited QR codes</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> All Pro features</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> One-time activation</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> No payment needed</li>
          </ul>
          <div class="d-grid">
            <?php if ($trial_used || $role === 'trial'): ?>
              <button class="btn btn-trial" disabled><i class="fas fa-check-circle me-1"></i> Trial Already Used</button>
            <?php else: ?>
              <a href="activate_trial.php" class="btn btn-trial"><i class="fas fa-clock me-1"></i> Start Free Trial</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Pro Plan -->
    <div class="col-md-4">
      <div class="card shadow-sm border-primary border-2">
        <div class="card-header text-center bg-primary text-white">
          <h4>Pro Plan</h4>
          <h1>Ksh 299/mo</h1>
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush mb-3">
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Unlimited QR codes</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Download in PNG, JPG, SVG, PDF</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Custom branding & logos</li>
            <li class="list-group-item"><i class="fas fa-check text-success me-2"></i> Priority support</li>
          </ul>
          <div class="d-grid">
            <?php if ($plan === 'Pro'): ?>
              <button class="btn btn-success" disabled><i class="fas fa-check-circle me-1"></i> Already Upgraded</button>
            <?php else: ?>
              <a href="payment.php" class="btn btn-success"><i class="fas fa-rocket me-1"></i> Upgrade to Pro</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="text-center bg-light py-3 mt-5">
  &copy; <?= date('Y') ?> Gaatech QR Generator
</footer>

</body>
</html>
