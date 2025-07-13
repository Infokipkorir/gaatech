<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

// Handle delete request
if (isset($_POST['delete_account'])) {
  // Backup user data before deletion
$backup = $conn->prepare("SELECT name, email, password, role, plan, plan_expiry, trial_started_at FROM users WHERE id = ?");
$backup->bind_param("i", $user_id);
$backup->execute();
$backup->bind_result($name, $email, $password, $role, $plan, $plan_expiry, $trial_started_at);
$backup->fetch();
$backup->close();

// Generate recovery token
$token = bin2hex(random_bytes(32));

// Insert to deleted_users
$insert = $conn->prepare("INSERT INTO deleted_users (original_user_id, name, email, password, role, plan, plan_expiry, trial_started_at, token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$insert->bind_param("issssssss", $user_id, $name, $email, $password, $role, $plan, $plan_expiry, $trial_started_at, $token);
$insert->execute();
$insert->close();

// Delete user
$delete = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete->bind_param("i", $user_id);
$delete->execute();

// Redirect to goodbye with token
header("Location: goodbye.php?token=$token");
exit();
  } else {
    $message = "❌ Failed to delete your account. Try again.";
  }

// Fetch user data
$stmt = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $role, $created);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Privacy Settings - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
  <h3 class="mb-4 text-primary">Privacy & Account Data</h3>

  <?php if ($message): ?>
    <div class="alert alert-danger"><?= $message ?></div>
  <?php endif; ?>

  <div class="card mb-4">
    <div class="card-header bg-light"><strong>Your Account Info</strong></div>
    <div class="card-body">
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
      <p><strong>Role:</strong> <?= htmlspecialchars(ucfirst($role)) ?></p>
      <p><strong>Account Created:</strong> <?= htmlspecialchars($created) ?></p>
      <a href="download_user_data.php" class="btn btn-success mt-3"><i class="fas fa-download"></i> Download My Data (JSON)</a>
    </div>
  </div>

  <form method="POST" onsubmit="return confirm('Are you sure you want to permanently delete your account?');">
    <button name="delete_account" class="btn btn-danger"><i class="fas fa-trash"></i> Delete My Account Permanently</button>
  </form>
</div>

</body>
</html>
