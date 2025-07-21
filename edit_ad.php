<?php
include '../conn.php';

$id = $_GET['id'];
$result = $conn->query("SELECT * FROM ads WHERE id = $id");
$ad = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $link = $_POST['link'];
    $plan_target = $_POST['plan_target'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $image = $ad['image']; // Keep existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $uploadFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
            $image = $imageName;
        }
    }

    $stmt = $conn->prepare("UPDATE ads SET title=?, content=?, image=?, link=?, plan_target=?, start_date=?, end_date=? WHERE id=?");
    $stmt->bind_param("sssssssi", $title, $content, $image, $link, $plan_target, $start_date, $end_date, $id);
    $stmt->execute();

    header("Location: admin_ads.php?updated=1");
}
?>

<!-- Basic edit form -->
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Edit Ad</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <h3>Edit Ad</h3>
  <form method="POST" enctype="multipart/form-data" class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title</label>
      <input name="title" value="<?= htmlspecialchars($ad['title']) ?>" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Target Plan</label>
      <select name="plan_target" class="form-select">
        <option value="Free" <?= $ad['plan_target'] === 'Free' ? 'selected' : '' ?>>Free</option>
        <option value="Pro" <?= $ad['plan_target'] === 'Pro' ? 'selected' : '' ?>>Pro</option>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Content</label>
      <textarea name="content" class="form-control"><?= htmlspecialchars($ad['content']) ?></textarea>
    </div>
    <div class="col-md-6">
      <label class="form-label">Start Date</label>
      <input type="datetime-local" name="start_date" value="<?= date('Y-m-d\TH:i', strtotime($ad['start_date'])) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">End Date</label>
      <input type="datetime-local" name="end_date" value="<?= date('Y-m-d\TH:i', strtotime($ad['end_date'])) ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Link</label>
      <input type="url" name="link" value="<?= $ad['link'] ?>" class="form-control">
    </div>
    <div class="col-md-6">
      <label class="form-label">Change Image</label>
      <input type="file" name="image" class="form-control">
      <?php if ($ad['image']): ?>
        <img src="../uploads/<?= $ad['image'] ?>" height="80" class="mt-2">
      <?php endif; ?>
    </div>
    <div class="col-12">
      <button class="btn btn-success" type="submit">Update</button>
      <a href="admin_ads.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</body>
</html>
