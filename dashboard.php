<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'Free';

// Trial logic
$trialAlert = '';
if ($role === 'trial') {
    $stmt = $conn->prepare("SELECT trial_started_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($trial_start);
    $stmt->fetch();
    $stmt->close();

    if ($trial_start) {
        $trial_end = strtotime($trial_start . ' +7 days');
        $remaining = $trial_end - time();
        if ($remaining > 0) {
            $days = floor($remaining / 86400);
            $hours = floor(($remaining % 86400) / 3600);
            $minutes = floor(($remaining % 3600) / 60);
            $trialAlert = "<div class='alert alert-info'>🕒 Trial ends in <strong>$days days, $hours hrs, $minutes min</strong>.</div>";
        } else {
            $trialAlert = "<div class='alert alert-warning'>⚠️ Your trial has ended. Please upgrade.</div>";
        }
    }
}

// Downgrade if Pro expired
$stmt = $conn->prepare("SELECT plan, plan_expiry FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($plan, $expiry);
$stmt->fetch();
$stmt->close();

if ($plan === 'Pro' && $expiry && strtotime($expiry) < time()) {
    $conn->query("UPDATE users SET plan = 'Free', payment_status = 'expired', plan_expiry = NULL WHERE id = $user_id");
    $_SESSION['plan'] = 'Free';
    $_SESSION['role'] = 'Free';
    $role = 'Free';
}

// Notifications
$notifications = [];
$stmt = $conn->prepare("SELECT id, message, created_at FROM messages WHERE to_user = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while ($note = $res->fetch_assoc()) {
    $notifications[] = $note;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
  <style>
    body { background: #f8f9fa; }
    canvas {
      display: block;
      margin: 20px auto;
      border: 1px solid #ccc;
      padding: 10px;
      background: white;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="#">Gaatech QR</a>
    <div class="ms-auto d-flex align-items-center">
      <!-- Notifications -->
      <div class="dropdown me-3">
        <button class="btn btn-outline-light position-relative" data-bs-toggle="dropdown">
          <i class="fas fa-bell"></i>
          <?php if (count($notifications) > 0): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($notifications) ?>
            </span>
          <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="width:300px;">
          <li class="dropdown-header">Notifications</li>
          <?php foreach ($notifications as $note): ?>
            <li class="px-3 py-2 small">
              <div><?= htmlspecialchars($note['message']) ?></div>
              <div class="text-muted small"><?= date('M j, H:i', strtotime($note['created_at'])) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <a href="my_account.php" class="btn btn-outline-light me-2"><i class="fas fa-user"></i> My Account</a>
      <a href="upgrade.php" class="btn btn-warning me-2"><i class="fas fa-rocket"></i> Upgrade</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- Trial Alert -->
<div class="container mt-4">
  <?= $trialAlert ?>
  <?php if ($role === 'trial'): ?>
    <span class="badge bg-warning text-dark mb-3">Trial User</span>
  <?php elseif ($role === 'pro'): ?>
    <span class="badge bg-success mb-3">Pro User</span>
  <?php else: ?>
    <div class="alert alert-warning">⚠️ Upgrade to unlock logo + color features <a href="upgrade.php" class="btn btn-sm btn-primary ms-2">Upgrade</a></div>
  <?php endif; ?>
</div>

<!-- QR Code Generator -->
<div class="container py-5">
  <h3 class="text-primary mb-4">Generate QR Code</h3>

  <form id="qrForm" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Enter URL or Text</label>
      <input type="text" id="qrData" class="form-control" required placeholder="https://example.com">
    </div>

    <div class="mb-3">
      <label class="form-label">Size</label>
      <select id="qrSize" class="form-select">
        <option value="150">150x150</option>
        <option value="200">200x200</option>
        <option value="250" selected>250x250</option>
        <option value="300">300x300</option>
      </select>
    </div>

    <?php if ($role === 'pro' || $role === 'trial'): ?>
      <div class="mb-3">
        <label class="form-label">QR Color</label>
        <input type="color" id="qrColor" value="#000000" class="form-control form-control-color">
      </div>

      <div class="mb-3">
        <label class="form-label">Upload Logo (PNG/JPG)</label>
        <input type="file" id="qrLogo" accept="image/png, image/jpeg" class="form-control">
      </div>
    <?php endif; ?>

    <button type="submit" class="btn btn-primary"><i class="fas fa-qrcode"></i> Generate</button>
  </form>

  <div class="text-center mt-4">
    <canvas id="qrCanvas"></canvas>
  </div>

  <div class="text-center">
    <a id="downloadBtn" class="btn btn-success me-2" style="display:none;"><i class="fas fa-download"></i> Download</a>
    <a id="shareBtn" class="btn btn-info" target="_blank" style="display:none;"><i class="fas fa-share-alt"></i> Share</a>
  </div>
</div>
<div id="cookie-banner" class="alert alert-dark text-center fixed-bottom mb-0" style="display:none; z-index: 1050;">
  🍪 We use cookies to enhance your experience. By continuing, you agree to our 
  <a href="privacy.php" class="text-primary">Privacy Policy</a>.
  <button class="btn btn-sm btn-primary ms-3" onclick="acceptCookies()">Accept</button>
</div>

<script>
document.getElementById('qrForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const qrData = document.getElementById('qrData').value.trim();
  const qrSize = parseInt(document.getElementById('qrSize').value);
  const canvas = document.getElementById('qrCanvas');
  const color = document.getElementById('qrColor')?.value || '#000000';
  const logoInput = document.getElementById('qrLogo');

  if (!qrData) return alert("Please enter data.");

  const qr = new QRious({
    element: canvas,
    value: qrData,
    size: qrSize,
    foreground: color
  });

  const base64 = canvas.toDataURL("image/png");

  if (logoInput && logoInput.files.length > 0) {
    const logo = logoInput.files[0];
    const img = new Image();
    img.src = URL.createObjectURL(logo);
    img.onload = async () => {
      const ctx = canvas.getContext('2d');
      const size = canvas.width * 0.25;
      ctx.drawImage(img, (canvas.width - size) / 2, (canvas.height - size) / 2, size, size);
      saveQR(base64, logo);
    };
  } else {
    saveQR(base64);
  }
});

function saveQR(base64, logo = null) {
  const formData = new FormData();
  formData.append("data", document.getElementById('qrData').value.trim());
  formData.append("image", base64);
  if (logo) formData.append("qrLogo", logo);

  fetch("save_qr.php", {
    method: "POST",
    body: formData
  });

  document.getElementById("downloadBtn").href = base64;
  document.getElementById("downloadBtn").download = 'gaatech_qr_' + Date.now() + '.png';
  document.getElementById("downloadBtn").style.display = 'inline-block';

  document.getElementById("shareBtn").href = base64;
  document.getElementById("shareBtn").style.display = 'inline-block';
}

function acceptCookies() {
  localStorage.setItem("cookieAccepted", "true");
  document.getElementById("cookie-banner").style.display = "none";
}

window.onload = () => {
  if (!localStorage.getItem("cookieAccepted")) {
    document.getElementById("cookie-banner").style.display = "block";
  }
};
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>
</body>
</html>
