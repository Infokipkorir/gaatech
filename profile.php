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

// Fetch user details
$stmt = $conn->prepare("SELECT name, email, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $join_date);
$stmt->fetch();
$stmt->close();

// Count saved QR codes
$stmt = $conn->prepare("SELECT COUNT(*) FROM my_codes WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($code_count);
$stmt->fetch();
$stmt->close();

// Fetch QR codes + views (using code ID, not title)
$stmt = $conn->prepare("
    SELECT c.id, 
           COALESCE(c.name, CONCAT('QR Code #', c.id)) AS label,
           COUNT(v.id) AS views
    FROM my_codes c
    LEFT JOIN qr_views v ON c.id = v.code_id
    WHERE c.user_id = ?
    GROUP BY c.id
    ORDER BY views DESC
");
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
    <title>Profile - Gaatech QR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin:0; background:#f8fafc; color:#1e293b; }
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

        .container {
            max-width:900px; margin:30px auto; background:white; padding:20px;
            border-radius:12px; box-shadow:0 6px 20px rgba(0,0,0,0.05);
        }
        h1 { font-size:22px; margin-bottom:15px; }
        .profile-info { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .card {
            background:#f9fafb; padding:15px; border-radius:8px;
            box-shadow:0 2px 6px rgba(0,0,0,0.05);
        }
        .card h2 { font-size:16px; margin-bottom:8px; color:#475569; }
        .card p { font-size:14px; margin:0; color:#1e293b; }
        .actions { display:flex; gap:10px; margin-top:20px; flex-wrap:wrap; }
        .actions a {
            background:#2563eb; color:white; padding:8px 14px; border-radius:6px;
            text-decoration:none; font-size:14px; display:flex; align-items:center; gap:5px;
        }
        .actions a.settings { background:#64748b; }
        .actions a.codes { background:#0ea5e9; }

        /* Analytics Section */
        .analytics { margin-top:30px; }
        .analytics h2 { font-size:20px; margin-bottom:15px; color:#1e293b; }
        .analytics-grid {
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:15px;
        }
        .analytics-grid .card {
            background:#f1f5f9;
            padding:15px;
            border-radius:8px;
            box-shadow:0 2px 6px rgba(0,0,0,0.05);
        }
        .analytics-grid .card h3 {
            margin:0 0 8px;
            font-size:16px;
            color:#334155;
        }
        .analytics-grid .card p {
            margin:0;
            font-size:14px;
            color:#1e293b;
        }
        @media(max-width:600px) {
            .profile-info { grid-template-columns:1fr; }
            .analytics-grid { grid-template-columns:1fr; }
        .code-card {
  position: relative;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  padding: 20px;
  margin: 20px;
  text-align: center;
}

/* Badge Style */
.badge {
  position: absolute;
  top: 10px;
  right: 10px;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: bold;
  color: white;
}

/* Colors for different tags */
.badge-new { background: green; }
.badge-beta { background: orange; }
.badge-hot { background: red; }
.badge-premium { background: purple; }


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
        <a href="upgrade.php" class="btn-upgrade"><i class="fas fa-crown"></i> Upgrade</a>
        <div class="dropdown">
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
            <div class="dropdown-menu">
                <a href="settings.php"><i class="fas fa-sliders-h"></i> Preferences</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
            </div>
        </div>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</div>

<!-- Profile Container -->
<div class="container">
    <h1>Welcome, <?= htmlspecialchars($name) ?> 👋</h1>

    <div class="profile-info">
        <div class="card">
            <h2>Name</h2>
            <p><?= htmlspecialchars($name) ?></p>
        </div>
        <div class="card">
            <h2>Email</h2>
            <p><?= htmlspecialchars($email) ?></p>
        </div>
        <div class="card">
            <h2>Member Since</h2>
            <p><?= date("F j, Y", strtotime($join_date)) ?></p>
        </div>
        <div class="card">
            <h2>Total QR Codes Saved</h2>
            <p><?= $code_count ?></p>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <a href="settings.php" class="settings"><i class="fas fa-cog"></i> Edit Settings</a>
        <a href="my_codes.php" class="codes"><i class="fas fa-qrcode"></i> View My Codes</a>
        <a href="upgrade.php"><i class="fas fa-crown"></i> Upgrade Plan</a>
    </div>

    <!-- Analytics Section -->
    <div class="analytics">
        <h2>📊 QR Code Analytics</h2>
        <div class="analytics-grid">
            <?php if (count($codes) > 0): ?>
                <?php foreach ($codes as $code): ?>
                    <div class="card">
                        <h3><?= htmlspecialchars($code['title'] ?? "Untitled QR") ?></h3>
                        <p><strong>Total Views:</strong> <?= $code['views'] ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>You haven’t created any QR codes yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
