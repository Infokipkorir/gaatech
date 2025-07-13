<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Gaatech QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f2f5;
      margin: 0;
      padding: 0;
    }

    .login-container {
      width: 100%;
      max-width: 400px;
      margin: 100px auto;
      background: #fff;
      padding: 30px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      border-radius: 8px;
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #007bff;
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 12px;
      margin: 10px 0;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      background: #007bff;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      margin-top: 10px;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #0056b3;
    }

    .register-link {
      text-align: center;
      margin-top: 15px;
    }

    .register-link a {
      color: #007bff;
      text-decoration: none;
      font-weight: 500;
    }

    .register-link a:hover {
      text-decoration: underline;
    }

    .error-message {
      color: red;
      text-align: center;
      margin-bottom: 10px;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Login</h2>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <form action="auth.php" method="POST">
      <input type="email" name="email" placeholder="Enter your email" required>
      <input type="password" name="password" placeholder="Enter your password" required>
      <button type="submit" name="login">Login</button>
    </form>

    <div class="register-link">
      Don't have an account? <a href="signup.php">Register</a>
    </div>
  </div>
</body>
</html>
