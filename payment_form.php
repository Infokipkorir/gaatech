<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h2 class="mb-4"><i class="fas fa-credit-card"></i> Pay for Pro Plan</h2>

  <form action="payment.php" method="POST" class="card p-4 shadow-sm">
    <div class="mb-3">
      <label class="form-label">Payment Method</label>
      <select name="method" class="form-select" required>
        <option value="">Select...</option>
        <option value="card">Credit/Debit Card</option>
        <option value="mpesa">M-Pesa</option>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Phone/Card Number</label>
      <input type="text" name="account" class="form-control" placeholder="Enter payment number" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Amount</label>
      <input type="text" class="form-control" value="KES 499" disabled>
    </div>

    <input type="hidden" name="amount" value="499">
    <button class="btn btn-success w-100"><i class="fas fa-check-circle"></i> Complete Payment</button>
  </form>

  <div class="text-center mt-4">
    <a href="upgrade.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a>
  </div>
</div>
</body>
</html>
<?php include 'footer.php' ?>