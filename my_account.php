<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update settings
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'])) {
    $newName = trim($_POST['name']);
    $newEmail = trim($_POST['email']);
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $updateSuccess = false;

    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $settingsError = "❌ Invalid email format.";
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $settingsError = "❌ Passwords do not match.";
    } else {
        $query = "UPDATE users SET name = ?, email = ?";
        $params = [$newName, $newEmail];
        $types = "ss";

        if (!empty($newPassword)) {
            $query .= ", password = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
            $types .= "s";
        }

        $query .= " WHERE id = ?";
        $params[] = $user_id;
        $types .= "i";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $updateSuccess = true;
            $name = $newName;
            $email = $newEmail;
        }
        $stmt->close();
    }
}

// Fetch user info
$stmt = $conn->prepare("SELECT name, email, plan FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $email, $plan);
$stmt->fetch();
$stmt->close();

// Update plan
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['new_plan'])) {
    $new_plan = $_POST['new_plan'];
    $stmt = $conn->prepare("UPDATE users SET plan = ? WHERE id = ?");
    $stmt->bind_param("si", $new_plan, $user_id);
    if ($stmt->execute()) {
        $plan = $new_plan;
        $planMessage = "✅ Plan updated to <strong>$plan</strong>.";
    } else {
        $planMessage = "❌ Failed to update your plan.";
    }
    $stmt->close();
}

// Fetch QR codes
$stmt = $conn->prepare("SELECT id, data, filename, created_at FROM qrcodes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Account - Gaatech QR</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .card-img-top { max-height: 220px; object-fit: contain; }
    .plan-badge { font-size: 0.85rem; padding: 4px 10px; border-radius: 20px; }
    .plan-pro { background: #0d6efd; color: #fff; }
    .plan-free { background: #6c757d; color: #fff; }
    .icon-button { border: none; background: none; color: #333; font-size: 1.1rem; cursor: pointer; }
    .icon-button:hover { color: #0d6efd; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">Gaatech QR</a>
    <div class="ms-auto d-flex gap-2">
      <a href="dashboard.php" class="btn btn-outline-light" title="Dashboard"><i class="fas fa-home"></i></a>
      <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Account Settings">
        <i class="fas fa-user-cog">Settings</i>
      </button>
      <a href="logout.php" class="btn btn-outline-light" title="Logout"><i class="fas fa-sign-out-alt">Logout</i></a>
    </div>
  </div>
</nav>

<!-- Main -->
<div class="container py-4">
  <div class="bg-white shadow-sm p-4 rounded mb-4">
    <h3 class="mb-1 text-primary"><i class="fas fa-user-circle me-2"></i><?= htmlspecialchars($name) ?></h3>
    <p class="mb-2 text-muted"><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($email) ?></p>

    <div class="mb-3">
      <span class="fw-bold">Your Plan:</span>
      <span class="plan-badge <?= $plan === 'Pro' ? 'plan-pro' : 'plan-free' ?>">
        <?= $plan ?> <i class="fas <?= $plan === 'Pro' ? 'fa-crown' : 'fa-user' ?>"></i>
      </span>
    </div>

    <?php if (isset($planMessage)): ?>
      <div class="alert alert-info alert-dismissible fade show"><?= $planMessage ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Change Plan Form -->
    <form method="POST" class="row g-3 align-items-center">
      <div class="col-auto">
        <label for="new_plan" class="form-label mb-0"><i class="fas fa-sync-alt me-1"></i> Change Plan:</label>
      </div>
      <div class="col-auto">
        <select name="new_plan" id="new_plan" class="form-select">
          <option value="Free" <?= $plan === 'Free' ? 'selected' : '' ?>>Free</option>
          <option value="Pro" <?= $plan === 'Pro' ? 'selected' : '' ?>>Pro</option>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-outline-primary"><i class="fas fa-check-circle"></i></button>
      </div>
    </form>
  </div>

  <!-- QR Code Alerts -->
  <?php if (isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      ✅ QR Code deleted successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php elseif (isset($_GET['error'])): ?>
    <?php
      $errorMsg = match ($_GET['error']) {
        'delete-failed' => '❌ Failed to delete QR Code.',
        'not-found' => '❌ QR Code not found.',
        'invalid-id' => '❌ Invalid QR Code ID.',
        default => '❌ Unexpected error.'
      };
    ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <?= $errorMsg ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- QR Code List -->
  <h4 class="mt-4">Your QR Codes</h4>
  <?php if ($result->num_rows > 0): ?>
    <div class="row">
      <?php while ($row = $result->fetch_assoc()): ?>
        <?php
          $filename = basename($row['filename'] ?? '');
          $filePath = $filename ? "qrcodes/" . $filename : '';
          $shortname = htmlspecialchars(strtok(parse_url($row['data'], PHP_URL_HOST) ?: $row['data'], '.'));
          $imageSrc = ($filePath && file_exists($filePath)) ? $filePath : "admin/assets/gaatech_qr.png";
        ?>
        <div class="col-md-4 mb-4">
          <div class="card shadow-sm">
            <img src="<?= $imageSrc ?>" class="card-img-top" alt="QR Code">
            <div class="card-body d-flex justify-content-between align-items-center">
              <h6 class="card-title text-primary mb-0"><?= $shortname ?></h6>
              <div class="d-flex gap-2">
                <a href="<?= $imageSrc ?>" download="<?= $shortname ?>.png" class="icon-button" title="Download"><i class="fas fa-download"></i></a>
                <a href="<?= $imageSrc ?>" target="_blank" class="icon-button" title="Share"><i class="fas fa-share-alt"></i></a>
                <a href="delete_qr.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this QR code?');" class="icon-button text-danger" title="Delete"><i class="fas fa-trash"></i></a>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="alert alert-warning">⚠️ You haven’t generated any QR codes yet.</div>
  <?php endif; ?>
</div>

<!-- Settings Modal -->
<?php include 'settings_modal.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  if (window.location.search.includes('deleted=') || window.location.search.includes('error=')) {
    window.history.replaceState({}, document.title, window.location.pathname);
  }

  // Optional: Hide modal after 2s if success
  document.getElementById('settingsForm')?.addEventListener('submit', () => {
    setTimeout(() => {
      const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
      modal?.hide();
    }, 2000);
  });
</script>
<?php include 'footer.php'; ?>
</body>
</html>
