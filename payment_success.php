<?php
session_start();
$status = $_GET['status'] ?? 'pending';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Status</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5 text-center">
    <?php if ($status === 'pending'): ?>
      <h2 class="text-warning mb-3">🕓 Payment Pending</h2>
      <p>Your payment request has been submitted and is pending admin confirmation.</p>
    <?php elseif ($status === 'confirmed'): ?>
      <h2 class="text-success mb-3">✅ Payment Confirmed</h2>
      <p>Thank you! Your upgrade has been successfully processed.</p>
    <?php else: ?>
      <h2 class="text-danger mb-3">❌ Payment Failed</h2>
      <p>Something went wrong. Please try again or contact support.</p>
    <?php endif; ?>
    <a href="dashboard.php" class="btn btn-primary mt-4"><-Go Back</a>
  </div>
</body>
</html>
