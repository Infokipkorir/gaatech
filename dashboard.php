<?php
session_start();
require_once 'db.php';
 include 'load_ads.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();

// Fetch notifications
$notifications = [];
$stmt = $conn->prepare("
    SELECT * FROM messages 
    WHERE (recipient_id = ? OR (is_broadcast = 1 AND recipient_id IS NULL)) 
      AND is_read = 0 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Fetch active ads
$ads = $conn->query("SELECT * FROM ads WHERE is_active = 1 AND (plan_target = 'Free' OR plan_target IS NULL) AND NOW() BETWEEN start_date AND end_date ORDER BY start_date DESC LIMIT 3");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - Gaatech QR</title>
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
          <?php if (count($notifications) === 0): ?>
            <li class="px-3 py-2 text-muted small">No new messages</li>
          <?php endif; ?>
          <?php foreach ($notifications as $note): ?>
            <li class="px-3 py-2 small border-bottom">
              <div><?= htmlspecialchars($note['message']) ?></div>
              <div class="text-muted small"><?= date('M j, H:i', strtotime($note['created_at'])) ?></div>
            </li>
          <?php endforeach; ?>
          <li><a href="messages.php" class="dropdown-item text-center">View all</a></li>
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
    <?php while ($ad = $ads->fetch_assoc()): ?>
      <div class="col-md-4 mb-3">
        <div class="card shadow">
          <?php if (!empty($ad['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($ad['image']) ?>" class="card-img-top" alt="Ad Image">
          <?php endif; ?>
          <div class="card-body">
            <h5 class="card-title text-primary"><?= htmlspecialchars($ad['title']) ?></h5>
            <p class="card-text"><?= htmlspecialchars($ad['content']) ?></p>
            <?php if (!empty($ad['link'])): ?>
              <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">Learn More</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
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
    <div class="mb-3">
      <label class="form-label">QR Color</label>
      <input type="color" id="qrColor" value="#000000" class="form-control form-control-color">
    </div>
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

  if (!qrData) return alert("Please enter data.");

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
