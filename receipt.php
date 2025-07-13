<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT name, email, plan, last_payment_date, plan_expiry FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['plan'] !== 'Pro') {
    echo "<div class='text-center text-danger'>No Pro payment record found.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receipt - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <div class="card shadow p-4">
    <h4 class="text-center mb-3">✅ Pro Plan Receipt</h4>
    <table class="table table-bordered">
      <tr><th>Name:</th><td><?= htmlspecialchars($user['name']) ?></td></tr>
      <tr><th>Email:</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
      <tr><th>Plan:</th><td><?= $user['plan'] ?></td></tr>
      <tr><th>Payment Date:</th><td><?= $user['last_payment_date'] ?></td></tr>
      <tr><th>Valid Until:</th><td><?= $user['plan_expiry'] ?></td></tr>
      <tr><th>Amount Paid:</th><td>KES 300.00</td></tr>
    </table>
    <div class="text-center mt-4">
      <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
      <button onclick="window.print()" class="btn btn-outline-secondary">Print Receipt</button>
    </div>
  </div>
</div>
</body>
</html>
