<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];

// Get upgrade status
$stmt = $conn->prepare("SELECT status FROM upgrades WHERE user_id = ? ORDER BY requested_at DESC LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($upgrade_status);
$stmt->fetch();
$stmt->close();

$isUpgraded = ($upgrade_status === 'confirmed');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Plan - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="#">Gaatech QR</a>
      <div class="d-flex">
        <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
        <a href="logout.php" class="btn btn-outline-light">Logout</a>
      </div>
    </div>
  </nav>
    <?php if (isset($_GET['status']) && $_GET['status'] === 'downgraded'): ?>
  <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
    ✅ Your upgrade has been successfully canceled. You're now on the Free plan.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php elseif (isset($_GET['status']) && $_GET['status'] === 'not_pro'): ?>
  <div class="alert alert-warning text-center alert-dismissible fade show" role="alert">
    ⚠️ You're not on the Pro plan, so there's nothing to cancel.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

  <div class="container py-5">
    <h3 class="text-primary mb-4">My Plan</h3>

    <?php if ($isUpgraded): ?>
      <div class="alert alert-success">
        🎉 You are on the <strong>Pro Plan</strong>.
      </div>

      <div class="card border-success">
        <div class="card-body">
          <h5 class="card-title">Pro Plan</h5>
          <p class="card-text">You have unlimited QR code generation and access to all premium features.</p>
          <form action="cancel_upgrade.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel your upgrade?');">
            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-ban"></i> Cancel Upgrade</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning">
        ⚠️ You are currently on the <strong>Free Plan</strong>.
      </div>

      <a href="upgrade.php" class="btn btn-primary"><i class="fas fa-rocket"></i> Upgrade to Pro</a>
    <?php endif; ?>
  </div>

</body>
</html>
