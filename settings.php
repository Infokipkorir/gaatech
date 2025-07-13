<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings - Gaatech QR</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f4f6f9;
      margin: 0;
    }

    header {
      background-color: #007bff;
      padding: 15px;
      color: #fff;
      text-align: center;
    }

    .container {
      max-width: 900px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      color: #007bff;
      margin-bottom: 20px;
    }

    section {
      margin-bottom: 40px;
    }

    label {
      display: block;
      margin: 10px 0 5px;
      font-weight: 500;
    }

    input, select {
      padding: 10px;
      width: 100%;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    button {
      background: #007bff;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      margin-top: 10px;
      cursor: pointer;
    }

    button:hover {
      background: #0056b3;
    }

    .danger {
      background: #dc3545;
    }

    .danger:hover {
      background: #b02a37;
    }
  </style>
</head>
<body>
  <header>
    <h1>Account Settings</h1>
  </header>

  <div class="container">
    <!-- Plan Section -->
    <section id="plan">
      <h2>My Plan</h2>
      <p>Current Plan: <strong>Free</strong> - 3 QR codes/day</p>
      <button onclick="alert('Upgrade feature coming')">Upgrade to Pro</button>
    </section>

    <a href="account_privacy.php" class="dropdown-item"><i class="fas fa-user-shield"></i> Privacy & Data</a>

    <!-- Billing Section -->
    <section id="billing">
      <h2>Billing Information</h2>
      <label for="card">Card Number</label>
      <input type="text" id="card" placeholder="**** **** **** 1234" disabled />
      <button disabled>Update Billing</button>
    </section>

    <!-- Password Section -->
    <section id="password">
      <h2>Password Settings</h2>
      <form action="update_password.php" method="POST">
        <label for="current">Current Password</label>
        <input type="password" id="current" name="current" required />

        <label for="new">New Password</label>
        <input type="password" id="new" name="new" required />

        <label for="confirm">Confirm New Password</label>
        <input type="password" id="confirm" name="confirm" required />

        <button type="submit">Update Password</button>
      </form>
    </section>

    <!-- Delete Account -->
    <section id="delete">
      <h2>Delete Account</h2>
      <p>Warning: This will remove all your data permanently.</p>
      <form action="delete_account.php" method="POST" onsubmit="return confirm('Are you sure?');">
        <button type="submit" class="danger">Delete My Account</button>
      </form>
    </section>
  </div>
</body>
</html>
