<?php
// load_ads.php
require_once 'db.php';

// Optional: Filter by user plan (e.g., 'Free', 'Pro')
$user_plan = $_SESSION['user_plan'] ?? 'Free';

$today = date('Y-m-d');
$stmt = $conn->prepare("
    SELECT * FROM ads 
    WHERE status = 'active' 
      AND (plan_target = 'All' OR plan_target = ?) 
      AND start_date <= ? AND end_date >= ?
    ORDER BY RAND() 
    LIMIT 3
");
$stmt->bind_param("sss", $user_plan, $today, $today);
$stmt->execute();
$ads = $stmt->get_result();
?>

<?php while ($ad = $ads->fetch_assoc()): ?>
  <div class="card shadow mb-3 position-relative ad-card">
    <button type="button" class="btn-close position-absolute top-0 end-0 m-2" onclick="this.parentElement.remove()"></button>
    <?php if (!empty($ad['image'])): ?>
      <img src="uploads/<?= htmlspecialchars($ad['image']) ?>" class="card-img-top" alt="Ad Image">
    <?php endif; ?>
    <div class="card-body">
      <h5 class="card-title text-primary"><?= htmlspecialchars($ad['title']) ?></h5>
      <p class="card-text"><?= htmlspecialchars($ad['content']) ?></p>
      <?php if (!empty($ad['link'])): ?>
        <a href="<?= htmlspecialchars($ad['link']) ?>" target="_blank" class="btn btn-sm btn-primary">Learn More</a>
      <?php endif; ?>
    </div>
  </div>
<?php endwhile; ?>
