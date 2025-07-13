<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upgrade_id'], $_POST['action'])) {
    $upgrade_id = (int)$_POST['upgrade_id'];
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE upgrades SET status = ?, reviewed_by = ?, reviewed_at = NOW() WHERE id = ?");
    $stmt->bind_param("sii", $action, $admin_id, $upgrade_id);
    $stmt->execute();

    header("Location: upgrade_requests.php?success=1");
    exit();
}

// Fetch upgrade requests
$query = "
  SELECT 
    u.id AS user_id,
    u.name, 
    u.email,
    up.id AS upgrade_id,
    up.requested_at,
    up.status,
    up.reviewed_at,
    ur.name AS reviewer_name
  FROM upgrades up
  JOIN users u ON up.user_id = u.id
  LEFT JOIN users ur ON up.reviewed_by = ur.id
  ORDER BY up.requested_at DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upgrade Requests - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary mb-4">
  <div class="container">
    <a class="navbar-brand" href="admin_dashboard.php">Admin Dashboard</a>
    <div class="d-flex">
      <a href="admin_dashboard.php" class="btn btn-outline-light me-2"><i class="fas fa-arrow-left"></i> Back</a>
      <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
  </div>
</nav>

<div class="container">
  <h3 class="text-primary mb-3">Upgrade Requests</h3>

  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success">✅ Request handled successfully.</div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Email</th>
          <th>Requested At</th>
          <th>Status</th>
          <th>Reviewed By</th>
          <th>Reviewed At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): $i = 1; ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= date("d M Y H:i", strtotime($row['requested_at'])) ?></td>
              <td>
                <span class="badge bg-<?= 
                  $row['status'] === 'approved' ? 'success' :
                  ($row['status'] === 'rejected' ? 'danger' : 'secondary')
                ?>">
                  <?= ucfirst($row['status']) ?>
                </span>
              </td>
              <td><?= $row['reviewer_name'] ?? '-' ?></td>
              <td><?= $row['reviewed_at'] ? date("d M Y H:i", strtotime($row['reviewed_at'])) : '-' ?></td>
              <td>
                <?php if ($row['status'] === 'pending'): ?>
                  <form method="POST" class="d-inline">
                    <input type="hidden" name="upgrade_id" value="<?= $row['upgrade_id'] ?>">
                    <button name="action" value="approve" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
                    <button name="action" value="reject" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
                  </form>
                <?php else: ?>
                  <span class="text-muted">Handled</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center">No upgrade requests found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php include 'footer.php' ?>