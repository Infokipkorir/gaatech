<?php
session_start();
require_once 'db.php';
include 'load_ads.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch unread notifications
$notifications = [];
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (recipient_id = ? OR (is_broadcast = 1 AND recipient_id IS NULL)) 
    AND is_read = 0 
    ORDER BY created_at DESC LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Fetch support unread count
$unread = 0;
$stmt = $conn->prepare("SELECT COUNT(*) AS unread FROM support_messages WHERE user_id = ? AND is_read = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $unread = $row['unread'];
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Gaatech QR</title>
  <link rel="icon" type="image/png" href="admin/assets/Gaatech logo2.jpg">
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

.support-float-btn {
  position: fixed;
  bottom: 20px;
  right: 20px;
  background-color:rgb(77, 255, 0);
  color: white;
  padding: 14px 18px;
  border-radius: 50%;
  font-size: 20px;
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
  text-decoration: none;
  z-index: 9999;
}
.support-float-btn:hover {
  background-color:rgb(255, 11, 11);
}
.support-badge {
  position: absolute;
  top: 5px;
  right: 5px;
  background: red;
  color: white;
  padding: 3px 6px;
  border-radius: 50%;
  font-size: 12px;
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
          <?php if (!empty($notifications)): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              <?= count($notifications) ?>
            </span>
          <?php endif; ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" style="width:300px;">
          <li class="dropdown-header">Notifications</li>
          <?php if (empty($notifications)): ?>
            <li class="px-3 py-2 text-muted small">No new messages</li>
          <?php else: ?>
            <?php foreach ($notifications as $note): ?>
              <li class="px-3 py-2 small border-bottom">
                <div><?= htmlspecialchars($note['message']) ?></div>
                <div class="text-muted small"><?= date('M j, H:i', strtotime($note['created_at'])) ?></div>
              </li>
            <?php endforeach; ?>
            <li><a href="messages.php" class="dropdown-item text-center">View all</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <a href="my_account.php" class="btn btn-outline-light me-2"><i class="fas fa-user"></i> My Account</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<!-- Ads Section -->
<div class="container mt-4">
  <div class="row">
    <?php if (!empty($ads)): ?>
      <?php foreach ($ads as $ad): ?>
        <div class="card mb-3">
          <div class="row g-0">
            <div class="col-md-4">
              <img src="uploads/<?= htmlspecialchars($ad['image']) ?>" class="img-fluid rounded-start" alt="Ad">
            </div>
            <div class="col-md-8">
              <div class="card-body">
                <h5 class="card-title text-primary"><?= htmlspecialchars($ad['title']) ?></h5>
                <p class="card-text"><?= htmlspecialchars($ad['content']) ?></p>
                <?php if (!empty($ad['link'])): ?>
                  <a href="<?= htmlspecialchars($ad['link']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Visit</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-muted">No ads to display.</p>
    <?php endif; ?>
  </div>
</div>

<!-- QR Code Generator -->
<div class="container py-5">
  <h3 class="text-primary mb-4">Generate QR Code</h3>
  <form id="qrForm">
    <div class="mb-3">
      <label class="form-label">Enter URL or Text</label>
      <input type="text" id="qrData" class="form-control" required placeholder="https://gaatech.co.ke">
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
    <div class="mb-3">
      <label class="form-label">QR Color</label>
      <input type="color" id="qrColor" value="#000000" class="form-control form-control-color">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-qrcode"></i> Generate</button>
  </form>

  <div class="text-center mt-4">
    <canvas id="qrCanvas"></canvas>
    <a id="downloadBtn" class="btn btn-success me-2" style="display:none;"><i class="fas fa-download"></i> Download</a>
    <a id="shareBtn" class="btn btn-info" target="_blank" style="display:none;"><i class="fas fa-share-alt"></i> Share</a>
  </div>
</div>

<!-- Cookie Banner -->
<div id="cookie-banner" class="alert alert-dark text-center fixed-bottom mb-0" style="display:none; z-index:1050;">
  🍪 We use cookies to enhance your experience. By continuing, you agree to our 
  <a href="privacy.php" class="text-primary">Privacy Policy</a>.
  <button class="btn btn-sm btn-primary ms-3" onclick="acceptCookies()">Accept</button>
</div>

<!-- Floating Support Button -->
<a href="support.php" class="support-float-btn">
  💬
  <?php if ($unread > 0): ?> 
    <span class="support-badge"><?= $unread ?></span>
  <?php endif; ?>
</a>

<!-- Scripts -->
<script>
document.getElementById('qrForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const qrData = document.getElementById('qrData').value.trim();
  const qrSize = parseInt(document.getElementById('qrSize').value);
  const color = document.getElementById('qrColor').value;
  const canvas = document.getElementById('qrCanvas');

  if (!qrData) return alert("Please enter some data.");

  const qr = new QRious({
    element: canvas,
    value: qrData,
    size: qrSize,
    foreground: color
  });

  const base64 = canvas.toDataURL("image/png");
  document.getElementById("downloadBtn").href = base64;
  document.getElementById("downloadBtn").download = 'gaatech_qr_' + Date.now() + '.png';
  document.getElementById("downloadBtn").style.display = 'inline-block';

  document.getElementById("shareBtn").href = base64;
  document.getElementById("shareBtn").style.display = 'inline-block';
});

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
