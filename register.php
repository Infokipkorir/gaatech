<?php
session_start();
include "db.php"; // DB connection

$message = "";

// Handle register
if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);

        if ($stmt->execute()) {
            header("Location: login.php?registered=1");
            exit;
        }
    } catch (mysqli_sql_exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            $message = "⚠️ Email already exists. Please login instead.";
        } else {
            $message = "Database Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Gaatech QR</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(135deg, #3b82f6, #1e3a8a);
      display: flex; justify-content: center; align-items: center;
      height: 100vh; margin: 0;
    }
    .card {
      background: #fff; padding: 30px; border-radius: 14px;
      width: 360px; max-width: 95%;
      box-shadow: 0 6px 20px rgba(0,0,0,0.15);
      text-align: center;
    }
    h2 { margin-bottom: 20px; color: #1e293b; }
    input {
      width: 100%; padding: 12px; margin: 8px 0;
      border: 1px solid #ddd; border-radius: 8px;
      font-size: 14px;
    }
    button {
      width: 100%; padding: 12px; margin-top: 10px;
      background: #2563eb; color: white; border: none;
      border-radius: 8px; cursor: pointer; font-weight: bold;
    }
    button:hover { background: #1d4ed8; }
    .alert {
      background: #ffe5e5; color: #d90429;
      padding: 10px; border-radius: 6px;
      font-size: 14px; margin-bottom: 10px;
    }
    p { font-size: 14px; color: #475569; margin-top: 15px; }
    p a { color: #2563eb; text-decoration: none; font-weight: bold; }
    p a:hover { text-decoration: underline; }
  </style>
</head>
<body>

<div class="card">
  <h2>Create Account</h2>

  <?php if (!empty($message)): ?>
    <div class="alert"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="register">Register</button>
  </form>

  <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>
