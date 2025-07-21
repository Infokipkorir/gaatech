<?php
// admin_ads.php
include 'admin_header.php';
include '../db.php'; // use your actual DB connection

// Handle ad creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_ad'])) {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $link = $_POST['link'];
    $plan_target = $_POST['plan_target'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $image = null;

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $imagePath = '../uploads/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        $image = $imagePath;
    }

    $stmt = $conn->prepare("INSERT INTO ads (title, content, image, link, plan_target, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $title, $content, $image, $link, $plan_target, $start_date, $end_date);
    $stmt->execute();
}

// Handle toggle
if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $conn->query("UPDATE ads SET is_active = NOT is_active WHERE id = $id");
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM ads WHERE id = $id");
}

// Fetch ads
$ads = $conn->query("SELECT * FROM ads ORDER BY created_at DESC");
?>

<div class="container mt-5">
  <h2 class="mb-4"><i class="fas fa-bullhorn"></i> Manage Ads</h2>

  <!-- Create Ad Form -->
  <form method="POST" enctype="multipart/form-data" class="card p-4 mb-4">
    <h5>Create New Ad</h5>
    <div class="row g-3">
      <div class="col-md-6">
        <label>Title</label>
        <input type="text" name="title" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>Target Plan</label>
        <select name="plan_target" class="form-select">
          <option value="Free">Free</option>
          <option value="Pro">Pro</option>
        </select>
      </div>
      <div class="col-12">
        <label>Content</label>
        <textarea name="content" class="form-control" rows="3" required></textarea>
      </div>
      <div class="col-md-6">
        <label>Image (optional)</label>
        <input type="file" name="image" class="form-control">
      </div>
      <div class="col-md-6">
        <label>Link (optional)</label>
        <input type="url" name="link" class="form-control">
      </div>
      <div class="col-md-6">
        <label>Start Date</label>
        <input type="datetime-local" name="start_date" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label>End Date</label>
        <input type="datetime-local" name="end_date" class="form-control" required>
      </div>
      <div class="col-12">
        <button class="btn btn-primary" name="create_ad"><i class="fas fa-plus-circle"></i> Create Ad</button>
      </div>
    </div>
  </form>

  <!-- Ads Table -->
  <div class="table-responsive card p-3">
    <h5 class="mb-3">All Ads</h5>
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Title</th>
          <th>Plan</th>
          <th>Status</th>
          <th>Clicks</th>
          <th>Start</th>
          <th>End</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($ad = $ads->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($ad['title']) ?></td>
          <td><?= $ad['plan_target'] ?></td>
          <td>
            <?php if ($ad['is_active']): ?>
              <span class="badge bg-success">Active</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inactive</span>
            <?php endif; ?>
          </td>
          <td><?= $ad['clicks'] ?></td>
          <td><?= date('M d, H:i', strtotime($ad['start_date'])) ?></td>
          <td><?= date('M d, H:i', strtotime($ad['end_date'])) ?></td>
          <td>
            <a href="?toggle=<?= $ad['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-toggle-on"></i></a>
            <a href="?delete=<?= $ad['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this ad?')"><i class="fas fa-trash"></i></a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
