<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT name, email, last_payment_date, plan_expiry FROM users WHERE plan = 'Pro' ORDER BY last_payment_date DESC");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Export CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header("Content-Type: text/csv");
    header("Content-Disposition: attachment;filename=pro_payments.csv");

    $output = fopen("php://output", "w");
    fputcsv($output, ['Name', 'Email', 'Payment Date', 'Plan Expiry']);

    $res = $conn->query("SELECT name, email, last_payment_date, plan_expiry FROM users WHERE plan = 'Pro'");
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit();
}

// Normal view
$result = $conn->query("SELECT name, email, last_payment_date, plan_expiry FROM users WHERE plan = 'Pro' ORDER BY last_payment_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Payments - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4">📄 All Pro Payments</h3>
  <table class="table table-bordered table-hover">
    <thead class="table-dark">
      <tr>
        <th>User</th>
        <th>Email</th>
        <th>Payment Date</th>
        <th>Expiry Date</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['email']) ?></td>
          <td><?= $row['last_payment_date'] ?></td>
          <td><?= $row['plan_expiry'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Back to Admin Dashboard</a>
</div>
</body>
</html>
