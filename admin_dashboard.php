<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get Stats
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalPro = $conn->query("SELECT COUNT(*) as count FROM users WHERE plan = 'Pro'")->fetch_assoc()['count'];

$currentMonth = date('Y-m');
$monthlyPayments = $conn->query("SELECT COUNT(*) as count FROM users WHERE plan = 'Pro' AND last_payment_date LIKE '$currentMonth%'")->fetch_assoc()['count'];

// QR stats (last 7 days)
$qrStats = [];
$labels = [];
$today = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $day = clone $today;
    $day->modify("-$i days");
    $dateStr = $day->format('Y-m-d');
    $labels[] = $day->format('M d');

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM qrcodes WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $dateStr);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $qrStats[] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
      background: #0d6efd;
      color: white;
      padding: 20px;
    }
    .sidebar a {
      color: #ffffff;
      text-decoration: none;
      display: block;
      padding: 8px 12px;
      margin-bottom: 10px;
      border-radius: 5px;
    }
    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .card-box {
      border-radius: 10px;
      padding: 20px;
      color: #fff;
    }
    .bg-total { background: #0d6efd; }
    .bg-pro { background: #198754; }
    .bg-month { background: #ffc107; color: #000; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 sidebar">
      <h4>🛠️ Admin Panel</h4>
      <hr class="border-white" />
      <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
      <a href="admin_payments.php"><i class="fas fa-money-check-alt"></i> View Payments</a>
      <a href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 p-4">
      <h3 class="mb-4">📊 Admin Dashboard</h3>

      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card-box bg-total">
            <h5><i class="fas fa-users"></i> Total Users</h5>
            <h3><?= $totalUsers ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-box bg-pro">
            <h5><i class="fas fa-crown"></i> Pro Users</h5>
            <h3><?= $totalPro ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-box bg-month">
            <h5><i class="fas fa-calendar-alt"></i> Payments This Month</h5>
            <h3><?= $monthlyPayments ?></h3>
          </div>
        </div>
      </div>

      <!-- QR Generation Chart -->
      <div class="card p-3 shadow-sm">
        <h5>📈 QR Codes Generated (Last 7 Days)</h5>
        <canvas id="qrChart" height="100"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  const getContext = document.getElementById('qrChart').getContext('2d');
  let qrchart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'QR Codes',
        data: <?= json_encode($qrStats) ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  });
</script>

</body>
</html>
<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get Stats
$totalUsers = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$totalPro = $conn->query("SELECT COUNT(*) as count FROM users WHERE plan = 'Pro'")->fetch_assoc()['count'];

$currentMonth = date('Y-m');
$monthlyPayments = $conn->query("SELECT COUNT(*) as count FROM users WHERE plan = 'Pro' AND last_payment_date LIKE '$currentMonth%'")->fetch_assoc()['count'];

// QR stats (last 7 days)
$qrStats = [];
$labels = [];
$today = new DateTime();
for ($i = 6; $i >= 0; $i--) {
    $day = clone $today;
    $day->modify("-$i days");
    $dateStr = $day->format('Y-m-d');
    $labels[] = $day->format('M d');

    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM qrcodes WHERE DATE(created_at) = ?");
    $stmt->bind_param("s", $dateStr);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    $qrStats[] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - Gaatech QR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      min-height: 100vh;
      background: #0d6efd;
      color: white;
      padding: 20px;
    }
    .sidebar a {
      color: #ffffff;
      text-decoration: none;
      display: block;
      padding: 8px 12px;
      margin-bottom: 10px;
      border-radius: 5px;
    }
    .sidebar a:hover {
      background: rgba(255, 255, 255, 0.2);
    }
    .card-box {
      border-radius: 10px;
      padding: 20px;
      color: #fff;
    }
    .bg-total { background: #0d6efd; }
    .bg-pro { background: #198754; }
    .bg-month { background: #ffc107; color: #000; }
  </style>
</head>
<body>

<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 sidebar">
      <h4>🛠️ Admin Panel</h4>
      <hr class="border-white" />
      <a href="admin_dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
      <a href="admin_manage_users.php"><i class="fas fa-users-cog"></i> Manage Users</a>
      <a href="admin_payments.php"><i class="fas fa-money-check-alt"></i> View Payments</a>
      <a href="admin_messages.php"><i class="fas fa-envelope"></i> Messages</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Main Content -->
    <div class="col-md-9 p-4">
      <h3 class="mb-4">📊 Admin Dashboard</h3>

      <div class="row g-4 mb-4">
        <div class="col-md-4">
          <div class="card-box bg-total">
            <h5><i class="fas fa-users"></i> Total Users</h5>
            <h3><?= $totalUsers ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-box bg-pro">
            <h5><i class="fas fa-crown"></i> Pro Users</h5>
            <h3><?= $totalPro ?></h3>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card-box bg-month">
            <h5><i class="fas fa-calendar-alt"></i> Payments This Month</h5>
            <h3><?= $monthlyPayments ?></h3>
          </div>
        </div>
      </div>

      <!-- QR Generation Chart -->
      <div class="card p-3 shadow-sm">
        <h5>📈 QR Codes Generated (Last 7 Days)</h5>
        <canvas id="qrChart" height="100"></canvas>
      </div>
    </div>
  </div>
</div>

<script>
  const ctx = document.getElementById('qrChart').getContext('2d');
  const qrChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?= json_encode($labels) ?>,
      datasets: [{
        label: 'QR Codes',
        data: <?= json_encode($qrStats) ?>,
        borderColor: '#0d6efd',
        backgroundColor: 'rgba(13,110,253,0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  });
</script>

</body>
</html>
