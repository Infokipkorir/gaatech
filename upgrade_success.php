<?php
session_start();
require_once "db.php";
require_once "config.php";
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payment Success</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
  <div class="alert alert-success">
    Payment completed — thank you! Your plan will be activated shortly. If it doesn't update in a few seconds, check your account later.
  </div>
  <a href="index.php" class="btn btn-primary">Go to Dashboard</a>
</body>
</html>
