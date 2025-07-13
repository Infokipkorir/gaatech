<?php
session_start();
require_once 'db.php';

$error = '';

// Count total upgrades
$upgradeCountResult = $conn->query("SELECT COUNT(*) AS total FROM upgrades");
$upgradeCount = $upgradeCountResult->fetch_assoc()['total'] ?? 0;

// Optional: Get upgrade stats by plan
$plansResult = $conn->query("SELECT plan, COUNT(*) as count FROM upgrades GROUP BY plan");
$planStats = [];
while ($row = $plansResult->fetch_assoc()) {
    $planStats[$row['plan']] = $row['count'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                if ($role === 'admin') {
                    $_SESSION['admin_id'] = $id;
                    $_SESSION['admin_name'] = $name;
                    $_SESSION['role'] = $role;
                    header("Location: admin_dashboard.php");
                    exit();
                } else {
                    $error = "❌ Access denied. Not an admin.";
                }
            } else {
                $error = "❌ Invalid password.";
            }
        } else {
            $error = "❌ Admin not found.";
        }

        $stmt->close();
    } else {
        $error = "❌ Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 450px;">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="text-center text-primary">Admin Login</h3>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login as Admin</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
