<?php
session_start();
require_once "db.php";
include "loader.php";

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $phone);
$stmt->fetch();
$stmt->close();

// Update profile
if (isset($_POST['update_profile'])) {
    $new_name  = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_phone = trim($_POST['phone']);

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, phone=? WHERE user_id=?");
    $stmt->bind_param("sssi", $new_name, $new_email, $new_phone, $user_id);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = "Profile updated successfully.";
    header("Location: settings.php");
    exit;
}

// Change password
if (isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass     = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_pass);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_pass, $hashed_pass)) {
        $error = "Current password is incorrect.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "New passwords do not match.";
    } else {
        $new_hashed = password_hash($new_pass, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE user_id=?");
        $stmt->bind_param("si", $new_hashed, $user_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = "Password changed successfully.";
        header("Location: settings.php");
        exit;
    }
}

// Delete account
if (isset($_POST['delete_account'])) {
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - Gaatech QR</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f8fafc; color:#1e293b; }
    .navbar { display:flex; justify-content:space-between; align-items:center;
        padding:12px 20px; background:linear-gradient(90deg,#0ea5e9,#2563eb); color:white; }
    .navbar a { color:white; text-decoration:none; margin-right:15px; font-weight:500; }
    .container { max-width:700px; margin:20px auto; background:white; padding:20px;
        border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.05); }
    h1 { font-size:22px; margin-bottom:20px; }
    .card { margin-bottom:20px; padding:15px; border-radius:8px; background:#f9fafb;
        box-shadow:0 2px 6px rgba(0,0,0,0.05); }
    .card h2 { font-size:18px; margin-bottom:10px; color:#475569; }
    .form-group { margin-bottom:12px; }
    label { display:block; margin-bottom:5px; font-weight:500; }
    input { width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:6px; }
    button { padding:10px 15px; border:none; border-radius:6px; font-weight:600; cursor:pointer; }
    .btn-primary { background:#2563eb; color:white; }
    .btn-danger { background:#dc2626; color:white; }
    .message { padding:10px; margin-bottom:15px; border-radius:6px; background:#bbf7d0; color:#166534; }
    .error { background:#fecaca; color:#991b1b; }
    @media(max-width:600px){ .container { margin:10px; padding:15px; } }
</style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div><a href="index.php">Gaatech QR</a></div>
    <div>
        <a href="profile.php">Profile</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<div class="container">
    <h1>⚙️ Account Settings</h1>

    <?php if (!empty($_SESSION['message'])): ?>
        <div class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="message error"><?= $error; ?></div>
    <?php endif; ?>

    <!-- Update Profile -->
    <div class="card">
        <h2>Update Profile</h2>
        <form method="POST">
            <div class="form-group">
                <label>Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($name) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>">
            </div>
            <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
        </form>
    </div>

    <!-- Change Password -->
    <div class="card">
        <h2>Change Password</h2>
        <form method="POST">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required>
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" name="change_password" class="btn-primary">Update Password</button>
        </form>
    </div>

    <!-- Delete Account -->
    <div class="card">
        <h2>Delete Account</h2>
        <p style="color:#991b1b;">⚠️ This action cannot be undone. All your data will be permanently deleted.</p>
        <form method="POST" onsubmit="return confirm('Are you sure you want to delete your account permanently?');">
            <button type="submit" name="delete_account" class="btn-danger">Delete My Account</button>
        </form>
    </div>
</div>

</body>
</html>
