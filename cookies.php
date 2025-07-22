<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cookies Policy | Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .policy-container {
      max-width: 900px;
      margin: 40px auto;
      background: #ffffff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    h1, h2 {
      color: #0d6efd;
    }
    .footer-btn {
      background-color: #dc3545;
      color: #fff;
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1000;
      padding: 12px 20px;
      border-radius: 30px;
      box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>

<div class="policy-container">
  <h1 class="mb-4">🍪 Cookies Policy</h1>
  <p>Last updated: <?= date("F j, Y") ?></p>

  <p>This Cookies Policy explains what cookies are and how Gaatech uses them. By using our website, you consent to the use of cookies.</p>

  <h2>What Are Cookies?</h2>
  <p>Cookies are small pieces of data stored on your device that help websites remember your preferences and actions over time.</p>

  <h2>How We Use Cookies</h2>
  <ul>
    <li>To keep you logged in.</li>
    <li>To understand how users interact with our pages.</li>
    <li>To remember preferences (such as themes or settings).</li>
    <li>To improve user experience.</li>
  </ul>

  <h2>Your Control</h2>
  <p>You can choose to disable cookies through your browser settings. However, disabling them may affect site functionality.</p>

  <h2>Third-Party Cookies</h2>
  <p>We may use trusted third-party tools (like analytics or ads) that may also store cookies on your device.</p>

  <h2>Updates to This Policy</h2>
  <p>We may update this Cookies Policy occasionally. Changes will be posted here with the date updated above.</p>

  <h2>Contact Us</h2>
  <p>If you have questions about this Cookies Policy, please contact us via <a href="support.php">support</a>.</p>
</div>

<!-- Optional: Floating Support Button -->
<a href="support.php" class="footer-btn">Need Help?</a>

</body>
</html>
