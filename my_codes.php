<?php
session_start();
require_once "db.php";
include "loader.php";

// Check login
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM my_codes WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: my_codes.php");
    exit;
}

// Get saved QR codes
$stmt = $conn->prepare("SELECT id, qr_data, qr_image, created_at FROM my_codes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$codes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Saved QR Codes - Gaatech QR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f8fafc; color:#1e293b; }

        /* Navbar same as index.php */
        .navbar {
            display:flex; align-items:center; justify-content:space-between;
            background: linear-gradient(90deg,#0ea5e9 0%, #2563eb 100%);
            padding:10px 20px; color:white;
        }
        .navbar .left { display:flex; align-items:center; gap:20px; }
        .navbar a { color:white; text-decoration:none; font-weight:500; }
        .navbar .right { display:flex; align-items:center; gap:15px; }
        .btn-upgrade {
            background:#facc15; color:#1e293b; padding:6px 12px; border-radius:6px;
            font-weight:600; display:flex; align-items:center; gap:5px;
        }
        .dropdown { position:relative; }
        .dropdown-menu {
            position:absolute; top:100%; right:0; background:white; color:black;
            border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,0.1);
            display:none; flex-direction:column; min-width:160px;
        }
        .dropdown-menu a {
            padding:10px; text-decoration:none; color:#1e293b; font-size:14px;
        }
        .dropdown:hover .dropdown-menu { display:flex; }

        /* Container */
        .container {
            max-width:1000px; margin:30px auto; background:white; padding:20px;
            border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.05);
        }
        h1 { font-size:22px; margin-bottom:15px; }
        .grid {
            display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
            gap:20px;
        }
        .card {
            border:1px solid #e2e8f0; border-radius:10px; padding:10px;
            display:flex; flex-direction:column; align-items:center;
            background:#f9fafb;
        }
        .card img { max-width:100%; border-radius:6px; }
        .card .text { font-size:13px; color:#334155; margin-top:8px; text-align:center; word-break:break-word; }
        .card .date { font-size:12px; color:#64748b; margin-top:4px; }
        .card .actions {
            margin-top:10px; display:flex; gap:6px;
        }
        .actions a {
            padding:6px 10px; border-radius:6px; font-size:12px;
            text-decoration:none; color:white;
        }
        .btn-download { background:#2563eb; }
        .btn-delete { background:#dc2626; }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="left">
        <a href="index.php">Gaatech QR</a>
        <a href="my_codes.php">My Codes</a>
    </div>
    <div class="right">
        <a href="#" class="btn-upgrade"><i class="fas fa-crown"></i> Upgrade</a>
        <div class="dropdown">
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <div class="dropdown-menu">
                <a href="profile.php"><i class="fas fa-user"></i> My Profile</a>
                <a href="settings.php"><i class="fas fa-sliders-h"></i> Preferences</a>
            </div>
        </div>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Main -->
<div class="container">
    <h1>My Saved QR Codes</h1>

    <?php if (empty($codes)): ?>
        <p>No QR codes saved yet.</p>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($codes as $code): ?>
                <div class="card">
                    <img src="<?= htmlspecialchars($code['qr_image']) ?>" alt="QR Code">
                    <div class="text"><?= htmlspecialchars($code['qr_data']) ?></div>
                    <div class="date"><?= date('M j, Y H:i', strtotime($code['created_at'])) ?></div>
                    <div class="actions">
                        <a href="<?= htmlspecialchars($code['qr_image']) ?>" download="qrcode.png" class="btn-download">Download</a>
                        <a href="?delete=<?= $code['id'] ?>" class="btn-delete" onclick="return confirm('Delete this QR code?')">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
