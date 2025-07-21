<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$plan = $_SESSION['plan'] ?? 'Free'; // Optional targeting

// Fetch active ads (you can expand with targeting and schedule)
$ads = [];
$result = $conn->query("SELECT * FROM ads WHERE status = 'active' ORDER BY created_at DESC");
while ($row = $result->fetch_assoc()) {
    $ads[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Ads - Gaatech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f8f9fa;
      padding: 20px;
    }
    .ad-card {
      position: relative;
      animation: fadeIn 0.5s ease-in;
      max-width: 500px;
      margin: 10px auto;
    }
    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 18px;
      color: #888;
      cursor: pointer;
    }
    .close-btn:hover {
      color: red;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
  </style>
</head>
<body>

<h3 class="text-center mb-4">📢 Promotional Ads</h3>

<?php if (empty($ads)): ?>
  <div class="alert alert-info text-center">No ads to display right now.</div>
<?php endif; ?>

<div id="ad-container">
  <?php foreach ($ads as $ad): ?>
    <div class="card shadow ad-card" id="ad-<?= $ad['id'] ?>">
      <div class="card-body">
        <span class="close-btn" onclick="dismissAd(<?= $ad['id'] ?>)">×</span>
        <h5 class="card-title"><?= htmlspecialchars($ad['title']) ?></h5>
        <p class="card-text"><?= nl2br(htmlspecialchars($ad['content'])) ?></p>
        <?php if (!empty($ad['link'])): ?>
          <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="btn btn-primary">Learn More</a>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<a href="<?= $ad['link'] ?>" onclick="trackClick(<?= $ad['id'] ?>)" target="_blank">Visit</a>

<script>
function trackClick(id) {
  fetch('track_click.php?id=' + id);
}

function dismissAd(id) {
  const adCard = document.getElementById("ad-" + id);
  if (adCard) adCard.remove();
}
</script>

</body>
</html>
