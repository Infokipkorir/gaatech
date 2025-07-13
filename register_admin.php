<?php
session_start();
require_once 'db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($name && $email && $password && $confirm) {
        if ($password !== $confirm) {
            $error = "❌ Passwords do not match.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = "❌ Email already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $role = 'admin';

                $insert = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $insert->bind_param("ssss", $name, $email, $hashed, $role);

                if ($insert->execute()) {
                    $success = "✅ Admin account created successfully.";
                } else {
                    $error = "❌ Failed to register admin.";
                }

                $insert->close();
            }

            $stmt->close();
        }
    } else {
        $error = "❌ All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5" style="max-width: 500px;">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="text-center text-primary mb-3">Register New Admin</h3>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="mb-3">
          <label>Name</label>
          <input type="text" name="name" class="form-control" required placeholder="Full name">
        </div>
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" required placeholder="admin@example.com">
        </div>
        <div class="mb-3">
          <label>Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label>Confirm Password</label>
          <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100"><i class="fas fa-user-shield"></i> Register Admin</button>
      </form>

      <div class="text-center mt-3">
        <a href="admin_login.php" class="text-primary">Back to Admin Login</a>
      </div>
    </div>
  </div>
</div>
</body>
</html>
