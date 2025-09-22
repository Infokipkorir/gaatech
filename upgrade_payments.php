<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include "loader.php"; // Loader animation
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upgrade Membership</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>
body {
    background: #f8fafc;
}
.upgrade-card {
    max-width: 500px;
    margin: auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.plan-icon {
    font-size: 50px;
}
.navbar-custom {
    display:flex;
    align-items:center;
    justify-content:space-between;
    background: linear-gradient(90deg,#0ea5e9 0%, #2563eb 100%);
    padding:10px 20px;
    color:white;
}
.navbar-custom a {
    color:white;
    text-decoration:none;
    font-weight:500;
}
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar-custom">
    <div class="left">
        <a href="index.php">Gaatech QR</a>
    </div>
    <div class="right">
        <a href="logout.php" class="btn btn-light btn-sm">Logout</a>
    </div>
</div>

<!-- Upgrade Form -->
<div class="container py-5">
    <div class="upgrade-card">
        <div class="text-center mb-4">
            <i class="bi bi-gem text-primary plan-icon"></i>
            <h3>Upgrade Your Plan</h3>
            <p class="text-muted">Select a membership and proceed to secure payment.</p>
        </div>

        <form id="upgradeForm" method="POST" action="process_payment.php">
            <div class="mb-3">
                <label class="form-label">Select Plan</label>
                <select class="form-select" name="plan" id="planSelect" required>
                    <option value="">-- Choose Plan --</option>
                    <option value="basic" data-price="0">Basic - $0</option>
                    <option value="pro" data-price="9.99">Pro - $9.99</option>
                    <option value="premium" data-price="19.99">Premium - $19.99</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Price</label>
                <input type="text" class="form-control" id="planPrice" readonly>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-credit-card"></i> Proceed to Payment
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById("planSelect").addEventListener("change", function() {
    let price = this.options[this.selectedIndex].dataset.price || "";
    document.getElementById("planPrice").value = price ? `$${price} / month` : "";
});

document.getElementById("upgradeForm").addEventListener("submit", function() {
    showLoader("Redirecting to payment...");
});
</script>

</body>
</html>
