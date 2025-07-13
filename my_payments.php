<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's payment records
$stmt = $conn->prepare("SELECT amount, payment_method, status, created_at FROM payments WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Payments - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">

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

<div class="container py-5">
  <h3 class="mb-4"><i class="fas fa-wallet me-1"></i> My Payment History</h3>

  <?php if ($result->num_rows > 0): ?>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead class="table-dark">
          <tr>
            <th>Amount</th>
            <th>Method</th>
            <th>Status</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td>KES <?= number_format($row['amount'], 2) ?></td>
              <td><?= htmlspecialchars($row['payment_method']) ?></td>
              <td>
                <span class="badge bg-<?= 
                  $row['status'] === 'approved' ? 'success' : 
                  ($row['status'] === 'rejected' ? 'danger' : 'warning'
                ) ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">You have not made any payments yet.</div>
  <?php endif; ?>
</div>

</body>
</html>
