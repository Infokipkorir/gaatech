<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
$message = $_SESSION['message'] ?? 'You have upgraded successfully.';
unset($_SESSION['message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upgrade Success - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container py-5 text-center">
  <h2 class="text-success mb-4">✅ Upgrade Successful</h2>
  <p class="lead"><?= htmlspecialchars($message) ?></p>
  <a href="dashboard.php" class="btn btn-primary mt-3">Go to Dashboard</a>
</div>

</body>
</html>
