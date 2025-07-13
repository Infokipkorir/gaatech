<?php
session_start();
require_once 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$planMessage = "";

// Handle plan selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_plan'])) {
    $selected_plan = $_POST['selected_plan'];

    if ($selected_plan === 'Free') {
        $stmt = $conn->prepare("UPDATE users SET plan = ? WHERE id = ?");
        $stmt->bind_param("si", $selected_plan, $user_id);
        if ($stmt->execute()) {
            $_SESSION['plan'] = 'Free';
            header("Location: dashboard.php?plan=free");
            exit();
        } else {
            $planMessage = "❌ Failed to update plan. Please try again.";
        }
        $stmt->close();
    } elseif ($selected_plan === 'Pro') {
        header("Location: payment.php?plan=pro");
        exit();
    } else {
        $planMessage = "❌ Invalid plan selected.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Choose Plan - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <style>
    .plan-card {
      border: 2px solid #ccc;
      border-radius: 10px;
      transition: 0.3s;
    }
    .plan-card:hover {
      border-color: #0d6efd;
      box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
    }
    .plan-badge {
      font-size: 0.85rem;
      padding: 4px 10px;
      border-radius: 20px;
    }
    .plan-pro {
      background: #0d6efd;
      color: #fff;
    }
    .plan-free {
      background: #6c757d;
      color: #fff;
    }
  </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Gaatech QR</a>
    <div class="d-flex">
      <a href="dashboard.php" class="btn btn-outline-light me-2"><i class="fas fa-arrow-left"></i> Dashboard</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container py-5">
  <h2 class="text-center mb-4">Choose Your Plan</h2>

  <?php if ($planMessage): ?>
    <div class="alert alert-danger"><?= $planMessage ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="row justify-content-center">

      <!-- Free Plan Card -->
      <div class="col-md-4 mb-4">
        <div class="plan-card p-4 text-center bg-light">
          <h4>Free Plan</h4>
          <p>✅ Generate up to <strong>3 QR codes/day</strong></p>
          <p>✅ Basic export (PNG)</p>
          <p>❌ No analytics, no branding</p>
          <input type="hidden" name="selected_plan" value="Free">
          <button type="submit" class="btn btn-outline-primary mt-3">Try Free</button>
        </div>
      </div>

      <!-- Pro Plan Card -->
      <div class="col-md-4 mb-4">
        <div class="plan-card p-4 text-center bg-light border-primary">
          <h4>Pro Plan <span class="badge plan-badge plan-pro ms-2">KES 300/mo</span></h4>
          <p>✅ <strong>Unlimited QR codes</strong></p>
          <p>✅ Branding + Analytics</p>
          <p>✅ Export: PNG, JPG, SVG, PDF</p>
          <input type="hidden" name="selected_plan" value="Pro">
          <button type="submit" class="btn btn-primary mt-3">Upgrade Now</button>
        </div>
      </div>

    </div>
  </form>
</div>

</body>
</html>
