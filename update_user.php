<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("UPDATE users SET plan = 'pro', plan_updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Successfully upgraded to Pro!";
    }
    $stmt->close();
    header("Location: my_account.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Upgrade Plan - Gaatech QR</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
  <div class="container py-5">
    <h2>Upgrade to Pro Plan</h2>
    <p>With the Pro plan, you can generate unlimited QR codes per day.</p>
    <form method="POST">
      <button type="submit" class="btn btn-success">Upgrade Now (Free Demo)</button>
    </form>
    <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
  </div>
</body>
</html>
