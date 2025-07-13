<?php
session_start();
require_once 'db.php';

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // Validate input
    if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "Email is already registered.";
        } else {
            // Insert new user
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, plan) VALUES (?, ?, ?, 'user', 'Free')");
            $stmt->bind_param("sss", $name, $email, $hashed);
            if ($stmt->execute()) {
                $success = "✅ Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .form-container {
      max-width: 450px;
      margin: 50px auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>

<div class="form-container">
  <h3 class="text-center text-primary mb-4">Create Your Account</h3>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?>
          <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= $success ?></div>
  <?php endif; ?>

  <form method="POST" action="register.php">
    <div class="mb-3">
      <label for="name" class="form-label">Name</label>
      <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email Address</label>
      <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
    </div>
    <div class="mb-3">
      <label for="password" class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required minlength="6">
    </div>
    <div class="mb-3">
      <label for="confirm" class="form-label">Confirm Password</label>
      <input type="password" name="confirm" class="form-control" required minlength="6">
    </div>
    <button type="submit" class="btn btn-primary w-100">Register</button>
  </form>
<small class="form-text text-muted">
  By registering, you agree to our <a href="terms.php" target="_blank">Terms & Conditions</a>.
</small>
  <p class="mt-3 text-center text-muted">
    Already have an account? <a href="login.php">Login</a>
  </p>
</div>

</body>
</html>
