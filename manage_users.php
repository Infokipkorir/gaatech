<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle role toggle
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'toggle_role') {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($role);
    if ($stmt->fetch()) {
        $stmt->close();
        $newRole = $role === 'admin' ? 'user' : 'admin';
        $updateStmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newRole, $id);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $stmt->close();
    }
    header("Location: manage_users.php");
    exit();
}

// Handle deletion
if (isset($_GET['action'], $_GET['id']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

$query = "SELECT * FROM users WHERE 1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $like = "%" . $search . "%";
    $params[] = $like;
    $params[] = $like;
}

if (!empty($roleFilter)) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$roleFilter = $_GET['role'] ?? '';
$fromDate = $_GET['from'] ?? '';
$toDate = $_GET['to'] ?? '';

$query = "SELECT id, name, email, role, created_at FROM users WHERE 1";

if ($roleFilter) {
    $query .= " AND role = '" . $conn->real_escape_string($roleFilter) . "'";
}
if ($fromDate && $toDate) {
    $query .= " AND DATE(created_at) BETWEEN '$fromDate' AND '$toDate'";
}

$query = "SELECT id, name, email, role, created_at FROM users WHERE 1";
$params = [];
$types = '';

if ($roleFilter) {
    $query .= " AND role = ?";
    $params[] = $roleFilter;
    $types .= 's';
}
if ($fromDate && $toDate) {
    $query .= " AND DATE(created_at) BETWEEN ? AND ?";
    $params[] = $fromDate;
    $params[] = $toDate;
    $types .= 'ss';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Users - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
  <h3 class="text-primary mb-4">Manage Users</h3>

  <form class="row mb-3">
    <div class="col-md-4">
      <input type="text" name="search" class="form-control" placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
    </div>
    <div class="col-md-3">
      <select name="role" class="form-select">
        <option value="">All Roles</option>
        <option value="user" <?= $roleFilter === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $roleFilter === 'admin' ? 'selected' : '' ?>>Admin</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filter</button>
    </div>
    <div class="col-md-3 text-end">
      <a href="export_users.php" class="btn btn-success w-100"><i class="fas fa-file-csv"></i> Export CSV</a>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-primary">
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Joined</th>
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
              <td><span class="badge bg-<?= $row['role'] === 'admin' ? 'warning' : 'secondary' ?>"><?= ucfirst($row['role']) ?></span></td>
              <td><?= date("d M Y", strtotime($row['created_at'])) ?></td>
              <td>
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewModal<?= $row['id'] ?>"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#roleModal<?= $row['id'] ?>"><i class="fas fa-sync-alt"></i></button>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#messageModal<?= $row['id'] ?>"><i class="fas fa-envelope"></i></button>
                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>"><i class="fas fa-trash"></i></button>
              </td>
            </tr>

            <!-- View Modal -->
            <div class="modal fade" id="viewModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">User Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>Name:</strong> <?= htmlspecialchars($row['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
                    <p><strong>Role:</strong> <?= htmlspecialchars($row['role']) ?></p>
                    <p><strong>Joined:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Role Modal -->
            <div class="modal fade" id="roleModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title">Toggle Role</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p>Are you sure you want to <?= $row['role'] === 'admin' ? 'downgrade' : 'upgrade' ?> this user?</p>
                    <a href="?action=toggle_role&id=<?= $row['id'] ?>" class="btn btn-warning">Confirm</a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Send Message Modal -->
<div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="sendMessageForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="messageModalLabel">Send Message</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="to_user" id="to_user_id">
          <div class="mb-3">
            <label class="form-label">To</label>
            <input type="text" class="form-control" id="to_user_name" disabled>
          </div>
          <div class="mb-3">
            <label class="form-label">Message</label>
            <textarea name="message" class="form-control" required></textarea>
          </div>
          <div id="messageFeedback" class="text-success small mt-1"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Send</button>
        </div>
      </div>
    </form>
  </div>
</div>
            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1">
              <div class="modal-dialog">
                <div class="modal-content">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <p>Are you sure you want to delete <strong><?= htmlspecialchars($row['name']) ?></strong>?</p>
                    <a href="?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger">Yes, Delete</a>
                  </div>
                </div>
              </div>
            </div>

          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" class="text-center">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
  var messageModal = document.getElementById('messageModal');
  messageModal.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    var userId = button.getAttribute('data-userid');
    var userName = button.getAttribute('data-username');
    document.getElementById('to_user_id').value = userId;
    document.getElementById('to_user_name').value = userName;
    document.getElementById('messageFeedback').innerText = '';
  });

  document.getElementById('sendMessageForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    fetch('send_message.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(data => {
      document.getElementById('messageFeedback').innerText = data;
    })
    .catch(() => {
      document.getElementById('messageFeedback').innerText = '❌ Failed to send message.';
    });
  });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
