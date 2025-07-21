<?php 
include 'conn.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Ads Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    .card-hover:hover {
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      transition: 0.3s ease;
    }
    .ad-image {
      max-height: 100px;
      object-fit: cover;
    }
  </style>
</head>
<body>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2><i class="fas fa-bullhorn me-2"></i>Admin Ads Manager</h2>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAdModal">
        <i class="fas fa-plus"></i> Create Ad
      </button>
    </div>

    <!-- Ads Table -->
    <div class="table-responsive">
      <table class="table table-striped align-middle">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Image</th>
            <th>Status</th>
            <th>Target Plan</th>
            <th>Start</th>
            <th>End</th>
            <th>Clicks</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          include 'conn.php';
          $result = $conn->query("SELECT * FROM ads ORDER BY created_at DESC");
          while ($ad = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $ad['id'] ?></td>
              <td><?= htmlspecialchars($ad['title']) ?></td>
              <td>
                <?php if (!empty($ad['image'])): ?>
                  <img src="../uploads/<?= $ad['image'] ?>" class="img-thumbnail ad-image">
                <?php endif; ?>
              </td>
              <td>
                <span class="badge bg-<?= $ad['is_active'] ? 'success' : 'secondary' ?>">
                  <?= $ad['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td><?= $ad['plan_target'] ?></td>
              <td><?= date('M d, Y', strtotime($ad['start_date'])) ?></td>
              <td><?= date('M d, Y', strtotime($ad['end_date'])) ?></td>
              <td><?= $ad['clicks'] ?></td>
              <td>
                <a href="edit_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                <a href="toggle_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-secondary">
                  <?= $ad['is_active'] ? 'Deactivate' : 'Activate' ?>
                </a>
                <a href="delete_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                  <i class="fas fa-trash"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Create Ad Modal -->
    <div class="modal fade" id="createAdModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <form action="create_ad.php" method="POST" enctype="multipart/form-data" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Create New Ad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Target Plan</label>
                <select name="plan_target" class="form-select">
                  <option value="Free">Free</option>
                  <option value="Pro">Pro</option>
                </select>
              </div>
              <div class="col-12">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-control" rows="3"></textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="datetime-local" name="start_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="datetime-local" name="end_date" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Ad Link (optional)</label>
                <input type="url" name="link" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label">Image</label>
                <input type="file" name="image" accept="image/*" class="form-control">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Save</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
