<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_SESSION['name'] ?? 'User';

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['method'])) {
    $method = $_POST['method'];
    $amount = 299.00;

    // Extract user-entered details per method
    if ($method === 'MPESA') {
        $details = $_POST['mpesa_number'];
    } elseif ($method === 'PayPal') {
        $details = $_POST['paypal_email'];
    } elseif ($method === 'Card') {
        $details = $_POST['card_number']; // Simplified
    } else {
        $details = 'unknown';
    }

    $stmt = $conn->prepare("INSERT INTO payments (user_id, amount, payment_method, status) VALUES (?, ?, ?, 'paid')");
    $stmt->bind_param("ids", $user_id, $amount, $method);
    if ($stmt->execute()) {
        $conn->query("UPDATE users SET plan = 'Pro' WHERE id = $user_id");
        $successMessage = "✅ $method payment received and plan upgraded.";
    } else {
        $errorMessage = "❌ Failed to record payment.";
    }
}
    $stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .card:hover { transform: scale(1.02); transition: 0.3s ease; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-primary mb-4">
  <div class="container d-flex justify-content-between">
    <a class="navbar-brand fw-bold" href="dashboard.php">Gaatech QR</a>
    <div>
      <a href="dashboard.php" class="btn btn-light me-2"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <a href="logout.php" class="btn btn-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- Alerts -->
<div class="container">
  <?php if ($successMessage): ?>
    <div class="alert alert-success"><?= $successMessage ?></div>
  <?php elseif ($errorMessage): ?>
    <div class="alert alert-danger"><?= $errorMessage ?></div>
  <?php endif; ?>
</div>

<!-- Payment Methods -->
<div class="container">
  <h3 class="text-center mb-4">Upgrade to Pro - Ksh 299</h3>
  <div class="row justify-content-center g-4">

    <!-- MPESA -->
    <div class="col-md-4">
      <div class="card border-success text-center">
        <div class="card-body">
          <i class="fas fa-mobile-alt fa-2x mb-3 text-success"></i>
          <h5>Pay with MPESA</h5>
          <p>Paybill: <strong>123456</strong><br> Account: <strong><?= $user_id ?></strong></p>
          <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mpesaModal">Confirm Payment</button>
        </div>
      </div>
    </div>

    <!-- PayPal -->
    <div class="col-md-4">
      <div class="card border-primary text-center">
        <div class="card-body">
          <i class="fab fa-paypal fa-2x mb-3 text-primary"></i>
          <h5>Pay with PayPal</h5>
          <p>Click to simulate PayPal payment confirmation</p>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#paypalModal">Pay with PayPal</button>
        </div>
      </div>
    </div>

    <!-- Card -->
    <div class="col-md-4">
      <div class="card border-info text-center">
        <div class="card-body">
          <i class="far fa-credit-card fa-2x mb-3 text-info"></i>
          <h5>Pay with Card</h5>
          <p>Visa, MasterCard, etc.</p>
          <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#cardModal">Pay by Card</button>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- MPESA Modal -->
<div class="modal fade" id="mpesaModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title"><i class="fas fa-mobile-alt me-2"></i> MPESA Payment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Ensure you've sent Ksh 299 via MPESA Paybill <strong>123456</strong>, Account: <strong><?= $user_id ?></strong>.</p>
        <input type="hidden" name="method" value="MPESA">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">I Have Paid</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- PayPal Modal -->
<div class="modal fade" id="paypalModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fab fa-paypal me-2"></i> PayPal</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Simulating PayPal payment confirmation for Ksh 299.</p>
        <input type="hidden" name="method" value="PayPal">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Confirm PayPal</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Card Modal -->
<div class="modal fade" id="cardModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="far fa-credit-card me-2"></i> Card Payment</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p>Confirm you paid via Visa/MasterCard for Ksh 299.</p>
        <input type="hidden" name="method" value="Card">
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-info">Confirm Card Payment</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
